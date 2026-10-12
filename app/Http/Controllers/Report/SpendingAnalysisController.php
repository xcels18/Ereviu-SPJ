<?php

namespace App\Http\Controllers\Report;

use App\Models\Opd;
use App\Models\Sp2d;
use App\Models\Spj;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SpendingAnalysisController extends ReportController
{
    public function index(Request $request)
    {
        $data = $request->validate([
            'tahun' => 'nullable|integer|between:2000,2100',
            'opd_id' => 'nullable|integer|min:1',
            'triwulan' => 'nullable|integer|between:1,4',
            'belanja' => 'nullable|string|max:150',
            'kode' => 'nullable|string|max:50',
            'exclude_codes' => 'sometimes|array|max:5',
            'exclude_codes.*' => 'string|max:50',
            'bulan' => 'nullable|integer|between:1,12',
            'tanggal_dari' => 'nullable|date_format:Y-m-d',
            'tanggal_sampai' => 'nullable|date_format:Y-m-d'.($request->filled('tanggal_dari') ? '|after_or_equal:tanggal_dari' : ''),
            'nomor_sp2d' => 'nullable|string|max:100',
            'nilai_min' => 'nullable|numeric|min:0',
            'nilai_max' => 'nullable|numeric|min:0'.($request->filled('nilai_min') ? '|gte:nilai_min' : ''),
        ]);
        $scope = $this->scopedOpdIds();
        $opds = Opd::when($scope !== null, fn ($q) => $q->whereIn('id', $scope))->orderBy('nama_opd')->get(['id', 'nama_opd']);
        $selected = $data['opd_id'] ?? null;
        abort_if($selected && ! $opds->contains('id', $selected), 403);
        $latest = Sp2d::whereIn('opd_id', $opds->pluck('id'))->max('tanggal');
        $year = (int) ($data['tahun'] ?? ($latest ? substr($latest, 0, 4) : now()->year));
        $quarter = $data['triwulan'] ?? null;
        $term = trim($data['belanja'] ?? '');
        $code = $data['kode'] ?? null;
        $start = Carbon::create($year, $quarter ? ($quarter - 1) * 3 + 1 : 1, 1)->startOfDay();
        $end = $quarter ? $start->copy()->addMonths(3) : $start->copy()->addYear();
        // Aggregate detail amounts, never repeat the SP2D header total for matching accounts.
        $query = DB::table('sp2d_budget_codes as b')->join('budget_accounts as a', 'a.id', '=', 'b.budget_account_id')->join('sp2d as s', 's.id', '=', 'b.sp2d_id')
            ->whereIn('s.opd_id', $opds->pluck('id'))->where('s.tanggal', '>=', $start->toDateString())->where('s.tanggal', '<', $end->toDateString())
            ->when($selected, fn ($q) => $q->where('s.opd_id', $selected))
            ->when($code, fn ($q) => $q->where('a.code', $code));
        if (! empty($data['bulan'])) {
            $query->whereMonth('s.tanggal', $data['bulan']);
        }
        if (! empty($data['tanggal_dari'])) {
            $query->where('s.tanggal', '>=', $data['tanggal_dari']);
        }
        if (! empty($data['tanggal_sampai'])) {
            $query->where('s.tanggal', '<=', $data['tanggal_sampai']);
        }
        if (isset($data['nilai_min'])) {
            $query->where('b.jumlah', '>=', $data['nilai_min']);
        }
        if (isset($data['nilai_max'])) {
            $query->where('b.jumlah', '<=', $data['nilai_max']);
        }
        if (! empty($data['nomor_sp2d'])) {
            $number = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $data['nomor_sp2d']);
            $query->whereRaw("s.nomor_sp2d LIKE ? ESCAPE '!'", ['%'.$number.'%']);
        }
        $spendingNames = (clone $query)->whereNotNull('b.uraian')->whereRaw("TRIM(b.uraian) <> ''")
            ->selectRaw('DISTINCT TRIM(b.uraian) as name')->orderBy('name')->pluck('name');
        foreach (preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY) as $word) {
            $literal = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], mb_strtolower($word));
            $query->whereRaw("LOWER(b.uraian) LIKE ? ESCAPE '!'", ['%'.$literal.'%']);
        }
        if ($request->boolean('detail')) {
            if (! empty($data['exclude_codes'])) {
                $query->whereNotIn('a.code', $data['exclude_codes']);
            }
            $detailAmount = (clone $query)->sum('b.jumlah');
            $rows = (clone $query)->selectRaw('s.id, s.opd_id, s.nomor_sp2d, s.tanggal, SUM(b.jumlah) as amount')
                ->groupBy('s.id', 's.opd_id', 's.nomor_sp2d', 's.tanggal')->orderByDesc('s.tanggal')->orderByDesc('s.id')->paginate(15);
            $spjs = Spj::whereIn('sp2d_id', collect($rows->items())->pluck('id'))->get()->keyBy('sp2d_id');
            $names = $opds->pluck('nama_opd', 'id');
            $canOpen = $request->user()->can('upload_spj') || $request->user()->can('review_spj');

            return response()->json(['amount' => $detailAmount, 'rows' => collect($rows->items())->map(fn ($row) => [
                'id' => $row->id, 'number' => $row->nomor_sp2d, 'opd' => $names[$row->opd_id], 'date' => $row->tanggal,
                'amount' => $row->amount, 'status' => $spjs->get($row->id)?->status,
                'url' => $canOpen ? route('spj.upload', ['opd_id' => $row->opd_id, 'search' => $row->nomor_sp2d]) : route('reports.sp2d.detail', $row->id),
                'action' => $canOpen ? ($spjs->has($row->id) ? 'Buka SPJ' : 'Buka halaman upload') : 'Lihat rincian laporan',
            ])->values(), 'page' => $rows->currentPage(), 'last_page' => $rows->lastPage(), 'total' => $rows->total()]);
        }
        $totals = (clone $query)->selectRaw('COUNT(*) as entries, COUNT(DISTINCT s.id) as documents, COUNT(DISTINCT s.opd_id) as agencies, COALESCE(SUM(b.jumlah),0) as amount')->first();
        $accountQuery = (clone $query)->selectRaw("a.code as kode_belanja, MIN(NULLIF(TRIM(b.uraian), '')) as nama_belanja, SUM(b.jumlah) as amount, COUNT(*) as entries")
            ->groupBy('a.code')->orderByDesc('amount')->orderBy('a.code');
        $accounts = (clone $accountQuery)->limit(10)->get();
        $allAccounts = $accountQuery->paginate(20, ['*'], 'accounts_page')->withQueryString();
        $ranking = (clone $query)->selectRaw('s.opd_id, SUM(b.jumlah) as amount, COUNT(DISTINCT s.id) as documents')->groupBy('s.opd_id')->orderByDesc('amount')->get();
        $names = $opds->pluck('nama_opd', 'id');
        $monthSql = DB::connection()->getDriverName() === 'sqlite' ? "CAST(strftime('%m', s.tanggal) AS INTEGER)" : 'MONTH(s.tanggal)';
        $monthly = (clone $query)->selectRaw("$monthSql as month, SUM(b.jumlah) as amount")->groupByRaw($monthSql)->get()->keyBy('month');
        $months = collect(range((int) $start->month, $quarter ? (int) $start->month + 2 : 12))->map(fn ($month) => ['month' => $month, 'amount' => (float) ($monthly->get($month)->amount ?? 0)]);
        $inspektorat = $opds->first(fn ($opd) => str_contains(mb_strtolower($opd->nama_opd), 'inspektorat'));

        return view('reports.spending-analysis', compact('opds', 'selected', 'year', 'quarter', 'term', 'code', 'totals', 'accounts', 'allAccounts', 'ranking', 'names', 'months', 'inspektorat', 'spendingNames'));
    }
}
