<?php

namespace App\Http\Controllers\AuditTrail;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class AuditTrailController extends Controller
{
    /**
     * Modul yang tersedia untuk filter, dipetakan ke label yang lebih mudah
     * dibaca di halaman audit trail.
     */
    private array $moduleLabels = [
        'auth' => 'Autentikasi',
        'user' => 'Kelola User',
        'opd' => 'OPD',
        'sp2d' => 'SP2D',
        'spj' => 'SPJ',
        'review_guide' => 'Panduan Reviu',
        'spj_compile' => 'Compile SPJ',
    ];

    private array $actionLabels = [
        'login' => 'Login',
        'logout' => 'Logout',
        'login_failed' => 'Login Gagal',
        'create' => 'Tambah',
        'update' => 'Ubah',
        'delete' => 'Hapus',
        'upload' => 'Upload',
        'review' => 'Reviu',
        'import' => 'Import',
        'compile' => 'Compile',
    ];

    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'module' => ['nullable', Rule::in(array_keys($this->moduleLabels))],
            'action' => ['nullable', Rule::in(array_keys($this->actionLabels))],
            'user_id' => ['nullable', 'integer', 'min:1'],
            'date_from' => ['nullable', 'date_format:Y-m-d'],
            'date_to' => ['nullable', 'date_format:Y-m-d', ...($request->filled('date_from') ? ['after_or_equal:date_from'] : [])],
            'per_page' => ['nullable', Rule::in([15, 25, 50])],
            'sort' => ['nullable', Rule::in(['newest', 'oldest'])],
        ]);

        $search = trim((string) $request->query('search', ''));
        $moduleFilter = (string) $request->query('module', '');
        $actionFilter = (string) $request->query('action', '');
        $userFilter = (string) $request->query('user_id', '');
        $dateFrom = (string) $request->query('date_from', '');
        $dateTo = (string) $request->query('date_to', '');

        $query = ActivityLog::query();
        $perPage = (int) ($request->input('per_page') ?: 15);
        $sort = $request->input('sort') ?: 'newest';
        $timezone = config('app.timezone', 'UTC');

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('user_name', 'like', "%{$search}%")
                    ->orWhere('user_email', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%");
            });
        }

        if ($moduleFilter !== '' && array_key_exists($moduleFilter, $this->moduleLabels)) {
            $query->where('module', $moduleFilter);
        }

        if ($actionFilter !== '' && array_key_exists($actionFilter, $this->actionLabels)) {
            $query->where('action', $actionFilter);
        }

        if ($userFilter !== '') {
            $query->where('user_id', $userFilter);
        }

        if ($dateFrom !== '') {
            $query->where('created_at', '>=', Carbon::parse($dateFrom, $timezone)->startOfDay());
        }

        if ($dateTo !== '') {
            $query->where('created_at', '<', Carbon::parse($dateTo, $timezone)->addDay()->startOfDay());
        }

        // Summaries cover the complete filtered result, not only the current page.
        $summary = (clone $query)->selectRaw(
            "COUNT(*) as total, COUNT(DISTINCT user_id) as users,
            COALESCE(SUM(CASE WHEN action = 'login_failed' THEN 1 ELSE 0 END), 0) as failed,
            COALESCE(SUM(CASE WHEN action IN ('create', 'update', 'delete', 'upload', 'review', 'import', 'compile') THEN 1 ELSE 0 END), 0) as changes"
        )->first();
        $direction = $sort === 'oldest' ? 'asc' : 'desc';
        $logs = $query->orderBy('created_at', $direction)->orderBy('id', $direction)
            ->paginate($perPage)->withQueryString();

        $users = User::orderBy('name')->get(['id', 'name', 'email']);

        return view('audit-trail.index', [
            'logs' => $logs,
            'summary' => $summary,
            'perPage' => $perPage,
            'sort' => $sort,
            'timezone' => $timezone,
            'users' => $users,
            'moduleLabels' => $this->moduleLabels,
            'actionLabels' => $this->actionLabels,
            'search' => $search,
            'moduleFilter' => $moduleFilter,
            'actionFilter' => $actionFilter,
            'userFilter' => $userFilter,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
        ]);
    }
}
