<?php

namespace App\Http\Controllers\Report;

use App\Exports\LaporanBulananExport;
use App\Exports\LaporanReviuExport;
use App\Exports\LaporanRingkasanOpdExport;
use App\Exports\SpjExport;
use App\Http\Controllers\Controller;
use App\Models\AuditorOpdAssignment;
use App\Models\Opd;
use App\Models\Sp2d;
use App\Models\Sp2dBudgetCode;
use App\Models\Spj;
use App\Models\SpjFinding;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;

class ReportController extends Controller
{
    /**
     * Scoping wilayah kerja laporan:
     * - admin & inspektur: null (tidak dibatasi, dapat melihat seluruh OPD)
     * - irban: array id OPD yang menjadi wilayah kerjanya (auditor_opd_assignments)
     * - user OPD lainnya (opd_admin/kepala_opd): array berisi OPD miliknya sendiri
     */
    protected function scopedOpdIds(): ?array
    {
        $user = auth()->user();

        if ($user->hasRole('admin') || $user->hasRole('inspektur')) {
            return null;
        }

        if ($user->isIrbanLike()) {
            return AuditorOpdAssignment::where('user_id', $user->irbanScopeUserId())->pluck('opd_id')->toArray();
        }

        return $user->opd_id ? [$user->opd_id] : [];
    }

    public function index(Request $request)
    {
        $assignedOpdIds = $this->scopedOpdIds();

        $opdQuery = $assignedOpdIds === null
            ? Opd::query()
            : Opd::whereIn('id', $assignedOpdIds ?: [0]);

        if ($request->filled('search')) {
            $opdQuery->where('nama_opd', 'like', '%'.$request->input('search').'%');
        }

        $opds = $opdQuery->orderBy('nama_opd')->get();

        $view = $request->input('view') === 'bulanan' ? 'bulanan' : 'ringkasan';
        $tahun = $request->input('tahun');
        // Pada tampilan per bulan, seluruh bulan (Januari-Desember) ditampilkan
        // sekaligus sebagai kolom, sehingga filter bulan tunggal diabaikan.
        $bulan = $view === 'bulanan' ? null : $request->input('bulan');

        $opdReports = $opds->map(function ($opd) use ($tahun, $bulan) {
            $sp2dQuery = Sp2d::where('opd_id', $opd->id);

            if ($tahun) {
                $sp2dQuery->whereYear('tanggal', $tahun);
            }

            if ($bulan) {
                $sp2dQuery->whereMonth('tanggal', $bulan);
            }

            $sp2dIds = $sp2dQuery->pluck('id')->all();

            $totalSp2d = count($sp2dIds);
            $totalAnggaran = $sp2dIds ? Sp2d::whereIn('id', $sp2dIds)->sum('jumlah_total') : 0;

            [$totalAkun, $lengkap, $tidakLengkap, $belumAdaBukti, $belumDinilai] = $this->hitungStatusAkunBelanja($sp2dIds);

            $totalSpj = $sp2dIds ? Spj::whereIn('sp2d_id', $sp2dIds)->count() : 0;
            $spjReviewed = $sp2dIds ? Spj::whereIn('sp2d_id', $sp2dIds)->where('status', 'reviewed')->count() : 0;
            $spjProses = $sp2dIds ? Spj::whereIn('sp2d_id', $sp2dIds)->whereIn('status', ['submitted', 'in_review'])->count() : 0;
            $sp2dBelumUpload = max($totalSp2d - $totalSpj, 0);

            $persentaseLengkap = $totalAkun > 0 ? round(($lengkap / $totalAkun) * 100, 1) : 0;

            return (object) [
                'opd' => $opd,
                'total_sp2d' => $totalSp2d,
                'total_anggaran' => $totalAnggaran,
                'sp2d_belum_upload' => $sp2dBelumUpload,
                'spj_proses' => $spjProses,
                'spj_reviewed' => $spjReviewed,
                'total_akun' => $totalAkun,
                'akun_lengkap' => $lengkap,
                'akun_tidak_lengkap' => $tidakLengkap,
                'akun_belum_dinilai' => $belumDinilai,
                'akun_belum_ada_bukti' => $belumAdaBukti,
                'persentase_lengkap' => $persentaseLengkap,
            ];
        })->sortByDesc('total_sp2d')->values();

        $summary = (object) [
            'total_opd' => $opdReports->count(),
            'total_sp2d' => $opdReports->sum('total_sp2d'),
            'total_anggaran' => $opdReports->sum('total_anggaran'),
            'spj_proses' => $opdReports->sum('spj_proses'),
            'spj_reviewed' => $opdReports->sum('spj_reviewed'),
            'total_akun' => $opdReports->sum('total_akun'),
            'akun_lengkap' => $opdReports->sum('akun_lengkap'),
            'akun_tidak_lengkap' => $opdReports->sum('akun_tidak_lengkap'),
            'akun_belum_dinilai' => $opdReports->sum('akun_belum_dinilai'),
            'akun_belum_ada_bukti' => $opdReports->sum('akun_belum_ada_bukti'),
        ];

        $availableYears = Sp2d::pluck('tanggal')
            ->map(fn ($tanggal) => $tanggal->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        $monthlyMatrix = $view === 'bulanan' ? $this->buildMonthlyMatrix($opds, $tahun) : [];

        if ($request->filled('export')) {
            return $this->exportLaporanRingkasan($request, $view, $opdReports, $summary, $monthlyMatrix, $tahun, $bulan);
        }

        return view('reports.index', [
            'opdReports' => $opdReports,
            'summary' => $summary,
            'availableYears' => $availableYears,
            'view' => $view,
            'monthlyMatrix' => $monthlyMatrix,
        ]);
    }

    /**
     * Nama bulan (Januari-Desember) untuk keperluan label kolom/laporan.
     */
    protected function bulanLabel(): array
    {
        return [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
    }

    /**
     * Cetak laporan cepat (PDF/Excel) dari halaman Laporan, mengikuti
     * tampilan yang sedang aktif (Ringkasan OPD atau Per Bulan) beserta
     * filter yang sedang diterapkan (tahun/bulan/pencarian).
     */
    protected function exportLaporanRingkasan(
        Request $request,
        string $view,
        $opdReports,
        $summary,
        array $monthlyMatrix,
        ?string $tahun,
        ?string $bulan
    ) {
        $bulanLabel = $this->bulanLabel();

        $meta = [
            'tahun' => $tahun,
            'periode' => $tahun ? 'Tahun '.$tahun : 'Semua Tahun',
            'bulan_label' => $bulan ? ($bulanLabel[(int) $bulan] ?? null) : null,
            'dicetak_oleh' => auth()->user()->name,
        ];

        $export = $request->input('export');
        $isBulanan = $view === 'bulanan';
        $slug = $isBulanan ? 'laporan-kelengkapan-bukti-per-bulan' : 'laporan-ringkasan-opd';

        if ($export === 'pdf') {
            $viewName = $isBulanan ? 'reports.bulanan-pdf' : 'reports.ringkasan-opd-pdf';
            $data = $isBulanan
                ? ['opdReports' => $opdReports, 'monthlyMatrix' => $monthlyMatrix, 'bulanLabel' => $bulanLabel, 'meta' => $meta, 'logo' => $this->logoBase64()]
                : ['opdReports' => $opdReports, 'summary' => $summary, 'meta' => $meta, 'logo' => $this->logoBase64()];

            $pdf = Pdf::loadView($viewName, $data)->setPaper('a4', 'landscape');

            return $pdf->stream($slug.'-'.date('Ymd').'.pdf');
        }

        if ($export === 'excel') {
            $exportClass = $isBulanan
                ? new LaporanBulananExport($opdReports, $monthlyMatrix, $bulanLabel, $meta)
                : new LaporanRingkasanOpdExport($opdReports, $summary, $meta);

            return Excel::download($exportClass, $slug.'-'.date('Ymd').'.xlsx');
        }

        abort(400, 'Format cetak tidak dikenali.');
    }

    /**
     * Susun matriks status kelengkapan bukti per OPD x Bulan (Januari-Desember)
     * untuk tampilan "Per Bulan" pada halaman Laporan. Status per sel:
     * - lengkap: seluruh akun belanja pada SP2D bulan tsb sudah lengkap
     * - tidak_lengkap: sudah ada SPJ diupload namun ada akun yang tidak lengkap/belum dinilai
     * - belum_upload: belum ada SPJ yang diupload sama sekali pada bulan tsb
     * - (tidak ada entri): tidak ada SP2D pada bulan tsb untuk OPD tersebut
     *
     * @param  Collection  $opds
     * @return array<int, array<int, object>> opd_id => [bulan => status object]
     */
    protected function buildMonthlyMatrix($opds, ?string $tahun): array
    {
        $opdIds = $opds->pluck('id')->all();

        if (empty($opdIds)) {
            return [];
        }

        $sp2dQuery = Sp2d::whereIn('opd_id', $opdIds)->select('id', 'opd_id', 'tanggal');

        if ($tahun) {
            $sp2dQuery->whereYear('tanggal', $tahun);
        }

        $sp2ds = $sp2dQuery->get();

        if ($sp2ds->isEmpty()) {
            return [];
        }

        $sp2dIds = $sp2ds->pluck('id')->all();

        // Status kelengkapan per akun belanja, diagregasi ke level SP2D.
        $sp2dStatus = [];

        Sp2dBudgetCode::whereIn('sp2d_id', $sp2dIds)
            ->select('id', 'sp2d_id')
            ->orderBy('id')
            ->chunk(1000, function ($codes) use (&$sp2dStatus, $sp2dIds) {
                static $budgetCodeIdsWithBukti = null;
                static $sp2dIdsWithGabunganBukti = null;
                static $findingsPerKode = null;
                static $findingsGabungan = null;

                if ($budgetCodeIdsWithBukti === null) {
                    $budgetCodeIdsWithBukti = DB::table('spj_documents')
                        ->join('spj', 'spj.id', '=', 'spj_documents.spj_id')
                        ->where('spj_documents.document_type', 'bukti_belanja')
                        ->whereNotNull('spj_documents.sp2d_budget_code_id')
                        ->whereIn('spj.sp2d_id', $sp2dIds)
                        ->distinct()
                        ->pluck('spj_documents.sp2d_budget_code_id')
                        ->flip();

                    $sp2dIdsWithGabunganBukti = DB::table('spj_documents')
                        ->join('spj', 'spj.id', '=', 'spj_documents.spj_id')
                        ->where('spj_documents.document_type', 'bukti_belanja')
                        ->whereNull('spj_documents.sp2d_budget_code_id')
                        ->whereIn('spj.sp2d_id', $sp2dIds)
                        ->distinct()
                        ->pluck('spj.sp2d_id')
                        ->flip();

                    $findingsPerKode = DB::table('spj_findings')
                        ->join('spj', 'spj.id', '=', 'spj_findings.spj_id')
                        ->whereNotNull('spj_findings.sp2d_budget_code_id')
                        ->whereIn('spj.sp2d_id', $sp2dIds)
                        ->pluck('spj_findings.status_bukti', 'spj_findings.sp2d_budget_code_id');

                    $findingsGabungan = DB::table('spj_findings')
                        ->join('spj', 'spj.id', '=', 'spj_findings.spj_id')
                        ->whereNull('spj_findings.sp2d_budget_code_id')
                        ->whereIn('spj.sp2d_id', $sp2dIds)
                        ->pluck('spj_findings.status_bukti', 'spj.sp2d_id');
                }

                foreach ($codes as $code) {
                    if (! isset($sp2dStatus[$code->sp2d_id])) {
                        $sp2dStatus[$code->sp2d_id] = ['total' => 0, 'lengkap' => 0, 'belum_ada_bukti' => 0];
                    }

                    $sp2dStatus[$code->sp2d_id]['total']++;

                    $status = $findingsPerKode->get($code->id) ?? $findingsGabungan->get($code->sp2d_id);

                    // Dana belum dibelanjakan dianggap tuntas, tidak memerlukan bukti upload.
                    if ($status === 'belum_dibelanjakan') {
                        $sp2dStatus[$code->sp2d_id]['lengkap']++;

                        continue;
                    }

                    $hasBukti = $budgetCodeIdsWithBukti->has($code->id) || $sp2dIdsWithGabunganBukti->has($code->sp2d_id);

                    if (! $hasBukti) {
                        $sp2dStatus[$code->sp2d_id]['belum_ada_bukti']++;

                        continue;
                    }

                    if ($status === 'lengkap') {
                        $sp2dStatus[$code->sp2d_id]['lengkap']++;
                    }
                }
            });

        // Tentukan status tiap SP2D secara individual terlebih dahulu, baru
        // gabungkan ke level OPD+bulan:
        // - "lengkap"      : SEMUA SP2D pada bulan tsb sudah lengkap buktinya
        // - "belum_upload" : SEMUA SP2D pada bulan tsb belum diupload sama sekali
        // - "tidak_lengkap": campuran (ada yang sudah upload/lengkap, ada yang
        //   belum, atau ada yang uploadnya belum lengkap)
        $sp2dLevelByMonth = [];

        foreach ($sp2ds as $sp2d) {
            if (! $sp2d->tanggal) {
                continue;
            }

            $month = (int) $sp2d->tanggal->format('n');
            $status = $sp2dStatus[$sp2d->id] ?? ['total' => 0, 'lengkap' => 0, 'belum_ada_bukti' => 0];

            if ($status['total'] === 0 || $status['belum_ada_bukti'] === $status['total']) {
                $sp2dLevelStatus = 'belum_upload';
            } elseif ($status['lengkap'] === $status['total']) {
                $sp2dLevelStatus = 'lengkap';
            } else {
                $sp2dLevelStatus = 'tidak_lengkap';
            }

            $sp2dLevelByMonth[$sp2d->opd_id][$month][] = $sp2dLevelStatus;
        }

        $matrix = [];

        foreach ($sp2dLevelByMonth as $opdId => $months) {
            foreach ($months as $month => $statuses) {
                $unique = array_unique($statuses);

                if (count($unique) === 1) {
                    $monthStatus = $unique[0];
                } else {
                    $monthStatus = 'tidak_lengkap';
                }

                $matrix[$opdId][$month] = (object) ['status' => $monthStatus];
            }
        }

        return $matrix;
    }

    /**
     * Hitung status kelengkapan bukti akun belanja untuk sekumpulan sp2d_id:
     * - lengkap: ada bukti & pemeriksa menandai "lengkap"
     * - tidak_lengkap: ada bukti & pemeriksa menandai "tidak lengkap"
     * - belum_dinilai: sudah ada bukti tapi pemeriksa belum menentukan status
     * - belum_ada_bukti: belum ada file bukti belanja yang diupload sama sekali
     */
    protected function hitungStatusAkunBelanja(array $sp2dIds): array
    {
        if (empty($sp2dIds)) {
            return [0, 0, 0, 0, 0];
        }

        $totalAkun = Sp2dBudgetCode::whereIn('sp2d_id', $sp2dIds)->count();

        if ($totalAkun === 0) {
            return [0, 0, 0, 0, 0];
        }

        $budgetCodeIdsWithBukti = DB::table('spj_documents')
            ->join('spj', 'spj.id', '=', 'spj_documents.spj_id')
            ->where('spj_documents.document_type', 'bukti_belanja')
            ->whereNotNull('spj_documents.sp2d_budget_code_id')
            ->whereIn('spj.sp2d_id', $sp2dIds)
            ->distinct()
            ->pluck('spj_documents.sp2d_budget_code_id')
            ->flip();

        $sp2dIdsWithGabunganBukti = DB::table('spj_documents')
            ->join('spj', 'spj.id', '=', 'spj_documents.spj_id')
            ->where('spj_documents.document_type', 'bukti_belanja')
            ->whereNull('spj_documents.sp2d_budget_code_id')
            ->whereIn('spj.sp2d_id', $sp2dIds)
            ->distinct()
            ->pluck('spj.sp2d_id')
            ->flip();

        $findingsPerKode = DB::table('spj_findings')
            ->join('spj', 'spj.id', '=', 'spj_findings.spj_id')
            ->whereNotNull('spj_findings.sp2d_budget_code_id')
            ->whereIn('spj.sp2d_id', $sp2dIds)
            ->pluck('spj_findings.status_bukti', 'spj_findings.sp2d_budget_code_id');

        $findingsGabungan = DB::table('spj_findings')
            ->join('spj', 'spj.id', '=', 'spj_findings.spj_id')
            ->whereNull('spj_findings.sp2d_budget_code_id')
            ->whereIn('spj.sp2d_id', $sp2dIds)
            ->pluck('spj_findings.status_bukti', 'spj.sp2d_id');

        $lengkap = 0;
        $tidakLengkap = 0;
        $belumAdaBukti = 0;
        $belumDinilai = 0;

        Sp2dBudgetCode::whereIn('sp2d_id', $sp2dIds)
            ->select('id', 'sp2d_id')
            ->orderBy('id')
            ->chunk(1000, function ($codes) use (
                &$lengkap, &$tidakLengkap, &$belumAdaBukti, &$belumDinilai,
                $budgetCodeIdsWithBukti, $sp2dIdsWithGabunganBukti, $findingsPerKode, $findingsGabungan
            ) {
                foreach ($codes as $code) {
                    $status = $findingsPerKode->get($code->id) ?? $findingsGabungan->get($code->sp2d_id);

                    // Dana belum dibelanjakan dianggap tuntas (tidak ada bukti yang
                    // perlu diunggah), disatukan ke hitungan "lengkap".
                    if ($status === 'belum_dibelanjakan') {
                        $lengkap++;

                        continue;
                    }

                    $hasBukti = $budgetCodeIdsWithBukti->has($code->id) || $sp2dIdsWithGabunganBukti->has($code->sp2d_id);

                    if (! $hasBukti) {
                        $belumAdaBukti++;

                        continue;
                    }

                    if ($status === 'lengkap') {
                        $lengkap++;
                    } elseif ($status === 'tidak_lengkap') {
                        $tidakLengkap++;
                    } else {
                        $belumDinilai++;
                    }
                }
            });

        return [$totalAkun, $lengkap, $tidakLengkap, $belumAdaBukti, $belumDinilai];
    }

    /**
     * Daftar SP2D milik satu OPD untuk keperluan pemantauan reviu (dibuka dari
     * tombol "Detail" pada halaman Laporan). Menampilkan status SPJ & ringkasan
     * kelengkapan bukti per akun belanja untuk setiap SP2D.
     */
    public function sp2dList(Request $request, Opd $opd)
    {
        $assignedOpdIds = $this->scopedOpdIds();

        if ($assignedOpdIds !== null && ! in_array($opd->id, $assignedOpdIds, true)) {
            abort(403);
        }

        $sp2dQuery = Sp2d::where('opd_id', $opd->id)->with(['budgetCodes', 'spj.documents', 'spj.findings']);

        if ($request->filled('search')) {
            $sp2dQuery->where('nomor_sp2d', 'like', '%'.$request->input('search').'%');
        }

        if ($request->filled('tahun')) {
            $sp2dQuery->whereYear('tanggal', $request->input('tahun'));
        }

        if ($request->filled('bulan')) {
            $sp2dQuery->whereMonth('tanggal', $request->input('bulan'));
        }

        // Ringkasan kelengkapan bukti dihitung dari seluruh SP2D yang sesuai
        // filter (bukan hanya SP2D pada halaman aktif) agar kartu ringkasan
        // selalu mencerminkan keseluruhan data yang sedang ditampilkan.
        $summary = (object) [
            'sp2d_count' => 0, 'sp2d_jumlah' => 0,
            'total' => 0, 'total_jumlah' => 0,
            'lengkap' => 0, 'lengkap_jumlah' => 0,
            'tidak_lengkap' => 0, 'tidak_lengkap_jumlah' => 0,
            'belum_ada_bukti' => 0, 'belum_ada_bukti_jumlah' => 0,
            'belum_dinilai' => 0, 'belum_dinilai_jumlah' => 0,
            'belum_dibelanjakan' => 0, 'belum_dibelanjakan_jumlah' => 0,
        ];

        foreach ((clone $sp2dQuery)->get() as $sp2dItem) {
            $spjItem = $sp2dItem->spj->first();
            [
                $lengkap, $tidakLengkap, $belumAdaBukti, $belumDinilai, $belumDibelanjakan,
                $lengkapJumlah, $tidakLengkapJumlah, $belumAdaBuktiJumlah, $belumDinilaiJumlah, $belumDibelanjakanJumlah,
            ] = $this->hitungRincianAkunPerSp2d($sp2dItem, $spjItem);

            $summary->sp2d_count++;
            $summary->sp2d_jumlah += $sp2dItem->jumlah_total;
            $summary->total += $sp2dItem->budgetCodes->count();
            $summary->total_jumlah += $sp2dItem->budgetCodes->sum('jumlah');
            $summary->lengkap += $lengkap;
            $summary->lengkap_jumlah += $lengkapJumlah;
            $summary->tidak_lengkap += $tidakLengkap;
            $summary->tidak_lengkap_jumlah += $tidakLengkapJumlah;
            $summary->belum_ada_bukti += $belumAdaBukti;
            $summary->belum_ada_bukti_jumlah += $belumAdaBuktiJumlah;
            $summary->belum_dinilai += $belumDinilai;
            $summary->belum_dinilai_jumlah += $belumDinilaiJumlah;
            $summary->belum_dibelanjakan += $belumDibelanjakan;
            $summary->belum_dibelanjakan_jumlah += $belumDibelanjakanJumlah;
        }

        $sp2dsPaginator = $sp2dQuery->orderBy('tanggal', 'desc')->paginate(20)->withQueryString();

        $rows = $sp2dsPaginator->getCollection()->map(function ($sp2d) {
            $spj = $sp2d->spj->first();
            [$lengkap, $tidakLengkap, $belumAdaBukti, $belumDinilai, $belumDibelanjakan] = $this->hitungRincianAkunPerSp2d($sp2d, $spj);
            $totalAkun = $sp2d->budgetCodes->count();
            $tuntas = $lengkap + $belumDibelanjakan;

            return (object) [
                'sp2d' => $sp2d,
                'spj' => $spj,
                'total_akun' => $totalAkun,
                'lengkap' => $lengkap,
                'tidak_lengkap' => $tidakLengkap,
                'belum_ada_bukti' => $belumAdaBukti,
                'belum_dinilai' => $belumDinilai,
                'belum_dibelanjakan' => $belumDibelanjakan,
                'persentase_lengkap' => $totalAkun > 0 ? round(($tuntas / $totalAkun) * 100, 1) : 0,
            ];
        });

        $availableYears = Sp2d::where('opd_id', $opd->id)->pluck('tanggal')
            ->map(fn ($tanggal) => $tanggal->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        return view('reports.sp2d-list', [
            'opd' => $opd,
            'rows' => $rows,
            'sp2dsPaginator' => $sp2dsPaginator,
            'availableYears' => $availableYears,
            'summary' => $summary,
        ]);
    }

    /**
     * Detail satu SP2D: rincian akun belanja mana saja yang belum ada bukti,
     * bukti tidak lengkap, dan bukti lengkap, agar pemeriksa dapat memantau
     * perkembangan reviu SPJ tanpa perlu masuk ke alur upload/reviu.
     */
    public function sp2dDetail(Sp2d $sp2d)
    {
        $assignedOpdIds = $this->scopedOpdIds();

        if ($assignedOpdIds !== null && ! in_array($sp2d->opd_id, $assignedOpdIds, true)) {
            abort(403);
        }

        $sp2d->load(['opd', 'budgetCodes', 'spj.documents', 'spj.findings']);

        $spj = $sp2d->spj->first();
        $documents = $spj ? $spj->documents : collect();
        $findings = $spj ? $spj->findings : collect();

        $hasGabunganBukti = $documents->where('document_type', 'bukti_belanja')->whereNull('sp2d_budget_code_id')->isNotEmpty();
        $findingGabungan = $findings->firstWhere('sp2d_budget_code_id', null);
        $buktiGabungan = $documents->where('document_type', 'bukti_belanja')->whereNull('sp2d_budget_code_id')->values();

        $akunList = $sp2d->budgetCodes->map(function ($code) use ($documents, $findings, $hasGabunganBukti, $findingGabungan, $buktiGabungan) {
            $buktiKode = $documents->where('document_type', 'bukti_belanja')->where('sp2d_budget_code_id', $code->id)->values();
            $hasBukti = $buktiKode->isNotEmpty() || $hasGabunganBukti;

            $finding = $findings->firstWhere('sp2d_budget_code_id', $code->id) ?? $findingGabungan;
            $statusBukti = $finding->status_bukti ?? null;

            if ($statusBukti === 'belum_dibelanjakan') {
                $status = 'belum_dibelanjakan';
            } elseif (! $hasBukti) {
                $status = 'belum_ada_bukti';
            } elseif ($statusBukti === 'lengkap') {
                $status = 'lengkap';
            } elseif ($statusBukti === 'tidak_lengkap') {
                $status = 'tidak_lengkap';
            } else {
                $status = 'belum_dinilai';
            }

            return (object) [
                'kode_belanja' => $code->kode_belanja,
                'uraian' => $code->uraian,
                'jumlah' => $code->jumlah,
                'status' => $status,
                'catatan' => $finding->description ?? '',
                'bukti' => $buktiKode->isNotEmpty() ? $buktiKode : $buktiGabungan,
            ];
        });

        $summary = (object) [
            'total' => $akunList->count(),
            'total_jumlah' => $akunList->sum('jumlah'),
            'lengkap' => $akunList->where('status', 'lengkap')->count(),
            'lengkap_jumlah' => $akunList->where('status', 'lengkap')->sum('jumlah'),
            'tidak_lengkap' => $akunList->where('status', 'tidak_lengkap')->count(),
            'tidak_lengkap_jumlah' => $akunList->where('status', 'tidak_lengkap')->sum('jumlah'),
            'belum_ada_bukti' => $akunList->where('status', 'belum_ada_bukti')->count(),
            'belum_ada_bukti_jumlah' => $akunList->where('status', 'belum_ada_bukti')->sum('jumlah'),
            'belum_dinilai' => $akunList->where('status', 'belum_dinilai')->count(),
            'belum_dinilai_jumlah' => $akunList->where('status', 'belum_dinilai')->sum('jumlah'),
            'belum_dibelanjakan' => $akunList->where('status', 'belum_dibelanjakan')->count(),
            'belum_dibelanjakan_jumlah' => $akunList->where('status', 'belum_dibelanjakan')->sum('jumlah'),
        ];

        return view('reports.sp2d-detail', [
            'sp2d' => $sp2d,
            'spj' => $spj,
            'akunList' => $akunList,
            'summary' => $summary,
        ]);
    }

    /**
     * Hitung rincian status kelengkapan bukti per akun belanja untuk satu SP2D
     * (dipakai pada daftar SP2D di halaman pemantauan reviu).
     */
    protected function hitungRincianAkunPerSp2d(Sp2d $sp2d, ?Spj $spj): array
    {
        $documents = $spj ? $spj->documents : collect();
        $findings = $spj ? $spj->findings : collect();

        $hasGabunganBukti = $documents->where('document_type', 'bukti_belanja')->whereNull('sp2d_budget_code_id')->isNotEmpty();
        $findingGabungan = $findings->firstWhere('sp2d_budget_code_id', null);

        $lengkap = 0;
        $tidakLengkap = 0;
        $belumAdaBukti = 0;
        $belumDinilai = 0;
        $belumDibelanjakan = 0;
        $lengkapJumlah = 0;
        $tidakLengkapJumlah = 0;
        $belumAdaBuktiJumlah = 0;
        $belumDinilaiJumlah = 0;
        $belumDibelanjakanJumlah = 0;

        foreach ($sp2d->budgetCodes as $code) {
            $finding = $findings->firstWhere('sp2d_budget_code_id', $code->id) ?? $findingGabungan;
            $status = $finding->status_bukti ?? null;

            // Dana belum dibelanjakan: tidak perlu bukti, dihitung terpisah dari
            // "belum ada bukti" agar tidak terlihat seolah reviu terhambat.
            if ($status === 'belum_dibelanjakan') {
                $belumDibelanjakan++;
                $belumDibelanjakanJumlah += $code->jumlah;

                continue;
            }

            $hasBuktiKode = $documents->where('document_type', 'bukti_belanja')->where('sp2d_budget_code_id', $code->id)->isNotEmpty();
            $hasBukti = $hasBuktiKode || $hasGabunganBukti;

            if (! $hasBukti) {
                $belumAdaBukti++;
                $belumAdaBuktiJumlah += $code->jumlah;

                continue;
            }

            if ($status === 'lengkap') {
                $lengkap++;
                $lengkapJumlah += $code->jumlah;
            } elseif ($status === 'tidak_lengkap') {
                $tidakLengkap++;
                $tidakLengkapJumlah += $code->jumlah;
            } else {
                $belumDinilai++;
                $belumDinilaiJumlah += $code->jumlah;
            }
        }

        return [
            $lengkap, $tidakLengkap, $belumAdaBukti, $belumDinilai, $belumDibelanjakan,
            $lengkapJumlah, $tidakLengkapJumlah, $belumAdaBuktiJumlah, $belumDinilaiJumlah, $belumDibelanjakanJumlah,
        ];
    }

    public function spjReport(Request $request)
    {
        $assignedOpdIds = $this->scopedOpdIds();

        $query = Spj::with(['opd', 'sp2d', 'budgetCode', 'reviewer']);

        if ($assignedOpdIds !== null) {
            $query->whereIn('opd_id', $assignedOpdIds ?: [0]);
        }

        if ($request->filled('opd_id') && ($assignedOpdIds === null || in_array((int) $request->opd_id, $assignedOpdIds, true))) {
            $query->where('opd_id', $request->opd_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->tahun) {
            $query->whereHas('sp2d', fn ($q) => $q->where('tahun', $request->tahun));
        }

        if ($request->filled('export')) {
            abort_unless($request->user()->can('export_reports'), 403);
            abort_if((clone $query)->count() > 2000, 422, 'Ekspor dibatasi 2.000 baris. Persempit filter periode/OPD.');
        }
        $spjs = $request->filled('export') ? $query->orderByDesc('created_at')->get() : $query->orderByDesc('created_at')->paginate(50)->withQueryString();

        $opdQuery = $assignedOpdIds === null ? Opd::query() : Opd::whereIn('id', $assignedOpdIds ?: [0]);
        $opds = $opdQuery->orderBy('nama_opd')->get();

        if ($request->export == 'pdf') {
            $logo = $this->logoBase64();
            $pdf = Pdf::loadView('reports.spj-pdf', compact('spjs', 'logo'));

            return $pdf->download('laporan-spj-'.date('Ymd').'.pdf');
        }

        if ($request->export == 'excel') {
            return Excel::download(new SpjExport($spjs), 'laporan-spj-'.date('Ymd').'.xlsx');
        }

        return view('reports.spj', compact('spjs', 'opds'));
    }

    public function findingsReport(Request $request)
    {
        $assignedOpdIds = $this->scopedOpdIds();

        $query = SpjFinding::with(['spj.opd', 'creator']);

        if ($assignedOpdIds !== null) {
            $query->whereHas('spj', fn ($q) => $q->whereIn('opd_id', $assignedOpdIds ?: [0]));
        }

        if ($request->filled('opd_id') && ($assignedOpdIds === null || in_array((int) $request->opd_id, $assignedOpdIds, true))) {
            $query->whereHas('spj', fn ($q) => $q->where('opd_id', $request->opd_id));
        }

        if ($request->finding_type) {
            $query->where('finding_type', $request->finding_type);
        }

        if ($request->filled('export')) {
            abort_unless($request->user()->can('export_reports'), 403);
            abort_if((clone $query)->count() > 2000, 422, 'Ekspor dibatasi 2.000 baris. Persempit filter periode/OPD.');
        }
        $findings = $request->filled('export') ? $query->orderByDesc('created_at')->get() : $query->orderByDesc('created_at')->paginate(50)->withQueryString();

        $opdQuery = $assignedOpdIds === null ? Opd::query() : Opd::whereIn('id', $assignedOpdIds ?: [0]);
        $opds = $opdQuery->orderBy('nama_opd')->get();

        if ($request->export == 'pdf') {
            $logo = $this->logoBase64();
            $pdf = Pdf::loadView('reports.findings-pdf', compact('findings', 'logo'));

            return $pdf->download('laporan-temuan-'.date('Ymd').'.pdf');
        }

        return view('reports.findings', compact('findings', 'opds'));
    }

    public function cetakLaporan(Request $request)
    {
        $assignedOpdIds = $this->scopedOpdIds();

        $opdQuery = $assignedOpdIds === null ? Opd::query() : Opd::whereIn('id', $assignedOpdIds ?: [0]);
        $opds = $opdQuery->orderBy('nama_opd')->get();

        $availableYears = Sp2d::pluck('tanggal')
            ->map(fn ($tanggal) => $tanggal->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        $opdId = $request->input('opd_id');
        $tahun = $request->input('tahun');
        $triwulan = (string) $request->input('triwulan', 'all');

        $selectedOpd = null;
        $groups = [];

        if ($opdId && ($assignedOpdIds === null || in_array((int) $opdId, $assignedOpdIds, true))) {
            $selectedOpd = $opds->firstWhere('id', (int) $opdId) ?? Opd::find($opdId);

            if ($selectedOpd) {
                $groups = $this->buildLaporanReviuGroups((int) $opdId, $tahun, $triwulan);
            }
        }

        $totalAkun = collect($groups)->sum(fn ($g) => count($g->items));

        $meta = [
            'opd' => $selectedOpd,
            'tahun' => $tahun,
            'triwulan' => $triwulan,
            'triwulan_label' => $triwulan === 'all' ? 'Seluruh Triwulan' : 'Triwulan '.$triwulan,
            'dicetak_oleh' => auth()->user()->name,
            'total_akun' => $totalAkun,
        ];

        if ($selectedOpd && $groups && $request->input('export') === 'pdf') {
            $pdf = Pdf::loadView('reports.cetak-pdf', ['groups' => $groups, 'meta' => $meta, 'logo' => $this->logoBase64()])
                ->setPaper('a4', 'landscape');

            // Buka dulu di tab baru (preview) alih-alih langsung memaksa download.
            return $pdf->stream('laporan-reviu-'.Str::slug($selectedOpd->nama_opd).'-'.date('Ymd').'.pdf');
        }

        if ($selectedOpd && $groups && $request->input('export') === 'excel') {
            return Excel::download(
                new LaporanReviuExport($groups, $meta),
                'laporan-reviu-'.Str::slug($selectedOpd->nama_opd).'-'.date('Ymd').'.xlsx'
            );
        }

        return view('reports.cetak', [
            'opds' => $opds,
            'availableYears' => $availableYears,
            'selectedOpd' => $selectedOpd,
            'groups' => $groups,
            'meta' => $meta,
        ]);
    }

    /**
     * Susun laporan cetak dalam bentuk pivot per SP2D (satu grup = satu SP2D)
     * agar data No SP2D/Tanggal/Nilai SP2D tidak berulang untuk tiap akun belanja.
     * Setiap grup berisi daftar item akun belanja: Akun Belanja, Nilai Akun Belanja, Status, Catatan Reviu.
     */
    protected function buildLaporanReviuGroups(int $opdId, ?string $tahun, string $triwulan): array
    {
        $sp2dQuery = Sp2d::where('opd_id', $opdId)->with(['budgetCodes', 'spj.documents', 'spj.findings']);

        if ($tahun) {
            $sp2dQuery->whereYear('tanggal', $tahun);
        }

        if ($triwulan !== 'all') {
            $sp2dQuery->where('triwulan', (int) $triwulan);
        }

        abort_if((clone $sp2dQuery)->count() > 2000, 422, 'Cetak dibatasi 2.000 SP2D. Persempit periode dan OPD.');
        $sp2ds = $sp2dQuery->orderBy('tanggal')->get();

        $groups = [];
        $no = 1;

        foreach ($sp2ds as $sp2d) {
            $spj = $sp2d->spj->first();
            $documents = $spj ? $spj->documents : collect();
            $findings = $spj ? $spj->findings : collect();

            $buktiPerKode = $documents->where('document_type', 'bukti_belanja')->whereNotNull('sp2d_budget_code_id')->pluck('sp2d_budget_code_id')->flip();
            $hasGabunganBukti = $documents->where('document_type', 'bukti_belanja')->whereNull('sp2d_budget_code_id')->isNotEmpty();

            $findingPerKode = $findings->whereNotNull('sp2d_budget_code_id')->keyBy('sp2d_budget_code_id');
            $findingGabungan = $findings->whereNull('sp2d_budget_code_id')->first();

            $items = [];

            foreach ($sp2d->budgetCodes as $code) {
                $hasBukti = $buktiPerKode->has($code->id) || $hasGabunganBukti;

                $finding = $findingPerKode->get($code->id) ?? $findingGabungan;
                $statusBukti = $finding->status_bukti ?? null;
                $catatan = $finding->description ?? '';

                if ($statusBukti === 'belum_dibelanjakan') {
                    $status = 'Belum Dibelanjakan';
                } elseif (! $hasBukti) {
                    $status = 'Belum Ada Bukti';
                    $catatan = '';
                } elseif ($statusBukti === 'lengkap') {
                    $status = 'Lengkap';
                } elseif ($statusBukti === 'tidak_lengkap') {
                    $status = 'Tidak Lengkap';
                } else {
                    $status = 'Belum Dinilai';
                }

                $items[] = (object) [
                    'akun_belanja' => trim($code->kode_belanja.' - '.$code->uraian),
                    'nilai_akun' => $code->jumlah,
                    'status' => $status,
                    'catatan_reviu' => $catatan,
                ];
            }

            if (empty($items)) {
                continue;
            }

            $groups[] = (object) [
                'no' => $no++,
                'nomor_sp2d' => $sp2d->nomor_sp2d,
                'tanggal_sp2d' => $sp2d->tanggal,
                'nilai_sp2d' => $sp2d->jumlah_total,
                'items' => $items,
            ];
        }

        return $groups;
    }

    /**
     * Logo Kabupaten Puncak Jaya sebagai data URI base64 untuk kop surat pada laporan PDF.
     */
    private function logoBase64(): string
    {
        static $logo = null;

        if ($logo === null) {
            $path = public_path('images/logo-puncak-jaya.png');
            $logo = is_file($path)
                ? 'data:image/png;base64,'.base64_encode(file_get_contents($path))
                : '';
        }

        return $logo;
    }
}
