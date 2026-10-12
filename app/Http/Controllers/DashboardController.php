<?php

namespace App\Http\Controllers;

use App\Models\AuditorOpdAssignment;
use App\Models\Opd;
use App\Models\Sp2d;
use App\Models\Sp2dBudgetCode;
use App\Models\Spj;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $isAdminInspektorat = $user->hasRole('admin') || $user->hasRole('inspektur');
        $isIrban = $user->isIrbanLike();

        $assignedOpdIds = [];
        if ($isIrban) {
            $assignedOpdIds = AuditorOpdAssignment::where('user_id', $user->irbanScopeUserId())->pluck('opd_id')->toArray();
        }

        // Batasan wilayah kerja: admin & inspektur melihat seluruh OPD,
        // irban hanya OPD yang menjadi wilayah kerjanya, opd_admin/kepala_opd hanya OPD-nya sendiri.
        if ($isAdminInspektorat) {
            $opdScope = null; // tanpa batasan
        } elseif ($isIrban) {
            $opdScope = $assignedOpdIds;
        } else {
            $opdScope = $user->opd_id ? [$user->opd_id] : [];
        }

        $totalOpd = $isAdminInspektorat
            ? Opd::count()
            : Opd::whereIn('id', $opdScope)->count();

        $sp2dQuery = Sp2d::query();
        $spjQuery = Spj::query();
        if (! $isAdminInspektorat) {
            $sp2dQuery->whereIn('opd_id', $opdScope);
            $spjQuery->whereHas('sp2d', fn ($q) => $q->whereIn('opd_id', $opdScope));
        }

        $totalSp2d = (clone $sp2dQuery)->count();
        $totalSpjUploaded = (clone $spjQuery)->count();

        // SP2D yang belum diupload SPJ-nya (satu SP2D = satu SPJ)
        $sp2dWithSpjIds = (clone $spjQuery)->pluck('sp2d_id')->all();
        $sp2dBelumUpload = (clone $sp2dQuery)->whereNotIn('id', $sp2dWithSpjIds ?: [0])->count();

        // SPJ yang sudah mendapat catatan reviu (temuan)
        $spjWithFindings = (clone $spjQuery)->whereHas('findings')->count();

        // Akun belanja (per SP2D) dan capaian kelengkapan bukti belanja
        $scopedSp2dIds = (clone $sp2dQuery)->pluck('id')->all();

        $totalAkunBelanja = Sp2dBudgetCode::whereIn('sp2d_id', $scopedSp2dIds ?: [0])->count();

        // Bukti belanja per akun belanja (kasus normal: satu bukti untuk satu kode belanja)
        $budgetCodeIdsWithBukti = DB::table('spj_documents')
            ->join('spj', 'spj.id', '=', 'spj_documents.spj_id')
            ->where('spj_documents.document_type', 'bukti_belanja')
            ->whereNotNull('spj_documents.sp2d_budget_code_id')
            ->whereIn('spj.sp2d_id', $scopedSp2dIds ?: [0])
            ->distinct()
            ->pluck('spj_documents.sp2d_budget_code_id')
            ->all();

        // Bukti belanja gabungan (khusus SP2D/LS-GAJI: satu bukti mewakili seluruh akun belanja SP2D tsb)
        $sp2dIdsWithGabunganBukti = DB::table('spj_documents')
            ->join('spj', 'spj.id', '=', 'spj_documents.spj_id')
            ->where('spj_documents.document_type', 'bukti_belanja')
            ->whereNull('spj_documents.sp2d_budget_code_id')
            ->whereIn('spj.sp2d_id', $scopedSp2dIds ?: [0])
            ->distinct()
            ->pluck('spj.sp2d_id')
            ->all();

        $akunBelanjaDenganBukti = Sp2dBudgetCode::whereIn('sp2d_id', $scopedSp2dIds ?: [0])
            ->where(function ($q) use ($budgetCodeIdsWithBukti, $sp2dIdsWithGabunganBukti) {
                $q->whereIn('id', $budgetCodeIdsWithBukti ?: [0])
                    ->orWhereIn('sp2d_id', $sp2dIdsWithGabunganBukti ?: [0]);
            })
            ->count();

        $persentaseBukti = $totalAkunBelanja > 0
            ? round(($akunBelanjaDenganBukti / $totalAkunBelanja) * 100, 1)
            : 0;

        return view('dashboard', compact(
            'totalOpd',
            'totalSp2d',
            'totalSpjUploaded',
            'sp2dBelumUpload',
            'spjWithFindings',
            'totalAkunBelanja',
            'akunBelanjaDenganBukti',
            'persentaseBukti'
        ));
    }
}
