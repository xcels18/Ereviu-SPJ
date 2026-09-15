<?php

namespace App\Http\Controllers\Spj;

use App\Http\Controllers\Controller;
use App\Models\AuditorOpdAssignment;
use App\Models\Opd;
use App\Models\Schedule;
use App\Models\Sp2d;
use App\Models\Sp2dBudgetCode;
use App\Models\Spj;
use App\Models\SpjDocument;
use App\Models\SpjFinding;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class SpjController extends Controller
{
    public function upload(Request $request)
    {
        $user = auth()->user();
        $opdId = $user->opd_id;
        $isAdminInspektorat = $user->hasRole('admin') || $user->hasRole('inspektur');
        $isIrban = $user->isIrbanLike();

        $assignedOpdIds = [];
        if ($isIrban) {
            $assignedOpdIds = AuditorOpdAssignment::where('user_id', $user->irbanScopeUserId())->pluck('opd_id')->toArray();
        }

        // Menu ini merangkap proses upload SPJ sekaligus penulisan catatan reviu
        $showOpdFilter = $isAdminInspektorat || $isIrban;

        // Tahap 1: bagi admin/inspektur/irban, tampilkan ringkasan per OPD terlebih dahulu
        // (Nama OPD, Total Anggaran, Persentase kelengkapan bukti, tombol Detail)
        if ($showOpdFilter && ! $request->filled('opd_id')) {
            return $this->renderOpdSummary($request, $isAdminInspektorat, $assignedOpdIds);
        }

        // Tahap 2: daftar SP2D lengkap dengan tombol Upload & Reviu untuk satu OPD
        return $this->renderSp2dList($request, $user, $isAdminInspektorat, $isIrban, $assignedOpdIds, $opdId, $showOpdFilter);
    }

    protected function renderOpdSummary(Request $request, bool $isAdminInspektorat, array $assignedOpdIds)
    {
        $opdQuery = $isAdminInspektorat
            ? Opd::query()
            : Opd::whereIn('id', $assignedOpdIds);

        if ($request->filled('search')) {
            $opdQuery->where('nama_opd', 'like', '%'.$request->input('search').'%');
        }

        $opds = $opdQuery->orderBy('nama_opd')->get();

        $opdSummaries = $opds->map(function ($opd) {
            $sp2dIds = Sp2d::where('opd_id', $opd->id)->pluck('id');

            $totalAnggaran = (clone $sp2dIds)->isEmpty() ? 0 : Sp2d::whereIn('id', $sp2dIds)->sum('jumlah_total');
            $totalSp2d = $sp2dIds->count();

            [$totalAkun, $akunDenganBukti, $persentase] = $this->hitungKelengkapanBukti($sp2dIds->all());

            return (object) [
                'opd' => $opd,
                'total_sp2d' => $totalSp2d,
                'total_anggaran' => $totalAnggaran,
                'total_akun' => $totalAkun,
                'akun_dengan_bukti' => $akunDenganBukti,
                'persentase' => $persentase,
            ];
        });

        $totalAnggaranSemua = $opdSummaries->sum('total_anggaran');
        $totalSp2dSemua = $opdSummaries->sum('total_sp2d');
        $rataPersentase = $opdSummaries->count() > 0 ? round($opdSummaries->avg('persentase'), 1) : 0;

        return view('spj.upload', [
            'mode' => 'opd_list',
            'opdSummaries' => $opdSummaries,
            'totalAnggaranSemua' => $totalAnggaranSemua,
            'totalSp2dSemua' => $totalSp2dSemua,
            'rataPersentase' => $rataPersentase,
        ]);
    }

    protected function hitungKelengkapanBukti(array $sp2dIds): array
    {
        $totalAkun = Sp2dBudgetCode::whereIn('sp2d_id', $sp2dIds ?: [0])->count();

        $budgetCodeIdsWithBukti = DB::table('spj_documents')
            ->join('spj', 'spj.id', '=', 'spj_documents.spj_id')
            ->where('spj_documents.document_type', 'bukti_belanja')
            ->whereNotNull('spj_documents.sp2d_budget_code_id')
            ->whereIn('spj.sp2d_id', $sp2dIds ?: [0])
            ->distinct()
            ->pluck('spj_documents.sp2d_budget_code_id')
            ->all();

        $sp2dIdsWithGabunganBukti = DB::table('spj_documents')
            ->join('spj', 'spj.id', '=', 'spj_documents.spj_id')
            ->where('spj_documents.document_type', 'bukti_belanja')
            ->whereNull('spj_documents.sp2d_budget_code_id')
            ->whereIn('spj.sp2d_id', $sp2dIds ?: [0])
            ->distinct()
            ->pluck('spj.sp2d_id')
            ->all();

        // Akun belanja yang dinilai "belum dibelanjakan" oleh pemeriksa dianggap
        // tuntas walau tanpa bukti upload (memang tidak ada yang perlu diunggah).
        $budgetCodeIdsBelumDibelanjakan = DB::table('spj_findings')
            ->join('spj', 'spj.id', '=', 'spj_findings.spj_id')
            ->where('spj_findings.status_bukti', 'belum_dibelanjakan')
            ->whereNotNull('spj_findings.sp2d_budget_code_id')
            ->whereIn('spj.sp2d_id', $sp2dIds ?: [0])
            ->distinct()
            ->pluck('spj_findings.sp2d_budget_code_id')
            ->all();

        $sp2dIdsWithGabunganBelumDibelanjakan = DB::table('spj_findings')
            ->join('spj', 'spj.id', '=', 'spj_findings.spj_id')
            ->where('spj_findings.status_bukti', 'belum_dibelanjakan')
            ->whereNull('spj_findings.sp2d_budget_code_id')
            ->whereIn('spj.sp2d_id', $sp2dIds ?: [0])
            ->distinct()
            ->pluck('spj.sp2d_id')
            ->all();

        $akunDenganBukti = Sp2dBudgetCode::whereIn('sp2d_id', $sp2dIds ?: [0])
            ->where(function ($q) use ($budgetCodeIdsWithBukti, $sp2dIdsWithGabunganBukti, $budgetCodeIdsBelumDibelanjakan, $sp2dIdsWithGabunganBelumDibelanjakan) {
                $q->whereIn('id', $budgetCodeIdsWithBukti ?: [0])
                    ->orWhereIn('sp2d_id', $sp2dIdsWithGabunganBukti ?: [0])
                    ->orWhereIn('id', $budgetCodeIdsBelumDibelanjakan ?: [0])
                    ->orWhereIn('sp2d_id', $sp2dIdsWithGabunganBelumDibelanjakan ?: [0]);
            })
            ->count();

        $persentase = $totalAkun > 0 ? round(($akunDenganBukti / $totalAkun) * 100, 1) : 0;

        return [$totalAkun, $akunDenganBukti, $persentase];
    }

    protected function renderSp2dList(Request $request, $user, bool $isAdminInspektorat, bool $isIrban, array $assignedOpdIds, ?int $opdId, bool $showOpdFilter)
    {
        // Check if there's an active upload schedule
        $uploadSchedule = Schedule::where('type', 'upload_period')
            ->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        // Get SP2Ds (satu baris per SP2D), dibatasi ke OPD milik user (opd_admin/kepala_opd)
        // atau ke wilayah kerja user (irban), kecuali admin/inspektur yang dapat melihat & memfilter seluruh dinas
        $sp2dQuery = Sp2d::with(['opd', 'budgetCodes', 'spj.documents', 'spj.findings']);

        $selectedOpd = null;

        if ($isAdminInspektorat) {
            if ($request->filled('opd_id')) {
                $sp2dQuery->where('opd_id', $request->input('opd_id'));
                $selectedOpd = Opd::find($request->input('opd_id'));
            }
        } elseif ($isIrban) {
            $sp2dQuery->whereIn('opd_id', $assignedOpdIds);
            if ($request->filled('opd_id') && in_array((int) $request->input('opd_id'), $assignedOpdIds, true)) {
                $sp2dQuery->where('opd_id', $request->input('opd_id'));
                $selectedOpd = Opd::find($request->input('opd_id'));
            }
        } else {
            $sp2dQuery->where('opd_id', $opdId);
            $selectedOpd = Opd::find($opdId);
        }

        if ($request->filled('search')) {
            $sp2dQuery->where('nomor_sp2d', 'like', '%'.$request->input('search').'%');
        }

        if ($request->filled('tahun')) {
            $sp2dQuery->whereYear('tanggal', $request->input('tahun'));
        }

        if ($request->filled('bulan')) {
            $sp2dQuery->whereMonth('tanggal', $request->input('bulan'));
        }

        if ($request->filled('status')) {
            $statusFilter = $request->input('status');

            if ($statusFilter === 'belum') {
                $sp2dQuery->whereDoesntHave('spj');
            } elseif (in_array($statusFilter, ['draft', 'submitted', 'in_review', 'reviewed'], true)) {
                $sp2dQuery->whereHas('spj', function ($q) use ($statusFilter) {
                    $q->where('status', $statusFilter);
                });
            }
        }

        $sp2dQuery->orderBy('tanggal', 'desc');

        // Statistik dihitung dari seluruh data yang cocok filter (bukan hanya halaman aktif)
        $statsSp2ds = (clone $sp2dQuery)->get();
        $totalSp2dCount = $statsSp2ds->count();
        $reviewedCount = $statsSp2ds->filter(fn ($s) => optional($s->spj->first())->status === 'reviewed')->count();
        $prosesCount = $statsSp2ds->filter(fn ($s) => in_array(optional($s->spj->first())->status, ['submitted', 'in_review'], true))->count();
        $belumCount = $statsSp2ds->filter(fn ($s) => ! $s->spj->first())->count();

        $perPageOptions = [10, 20, 50, 100];
        $perPageInput = (string) $request->input('per_page', '20');

        if ($perPageInput === 'all') {
            $perPage = 'all';
            $sp2ds = $sp2dQuery->get();
            $sp2dsPaginator = null;
        } else {
            $perPage = in_array((int) $perPageInput, $perPageOptions, true) ? (int) $perPageInput : 20;
            $sp2dsPaginator = $sp2dQuery->paginate($perPage)->withQueryString();
            $sp2ds = $sp2dsPaginator->getCollection();
        }

        $availableYears = Sp2d::pluck('tanggal')
            ->map(fn ($tanggal) => $tanggal->format('Y'))
            ->unique()
            ->sortDesc()
            ->values();

        $totalAnggaran = $statsSp2ds->sum('jumlah_total');
        [$totalAkun, $akunDenganBukti, $persentaseKelengkapan] = $this->hitungKelengkapanBukti($statsSp2ds->pluck('id')->all());

        return view('spj.upload', [
            'mode' => 'sp2d_list',
            'sp2ds' => $sp2ds,
            'sp2dsPaginator' => $sp2dsPaginator,
            'perPage' => $perPage,
            'totalSp2dCount' => $totalSp2dCount,
            'reviewedCount' => $reviewedCount,
            'prosesCount' => $prosesCount,
            'belumCount' => $belumCount,
            'totalAnggaran' => $totalAnggaran,
            'totalAkun' => $totalAkun,
            'akunDenganBukti' => $akunDenganBukti,
            'persentaseKelengkapan' => $persentaseKelengkapan,
            'uploadSchedule' => $uploadSchedule,
            'availableYears' => $availableYears,
            'showOpdFilter' => $showOpdFilter,
            'selectedOpd' => $selectedOpd,
        ]);
    }

    public function store(Request $request)
    {
        if (! $request->user()->can('review_spj')) {
            foreach (['catatan_reviu', 'catatan_reviu_gabungan', 'status_bukti', 'status_bukti_gabungan'] as $field) {
                abort_if($request->filled($field), 403);
            }
        }

        $validated = $request->validate([
            'sp2d_id' => 'required|exists:sp2d,id',
            'sp2d_scan' => 'required|array|min:1',
            'sp2d_scan.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png',
            'dokumen_lainnya' => 'nullable|array',
            'dokumen_lainnya.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png',
            'bukti_belanja' => 'nullable|array',
            'bukti_belanja.*' => 'array',
            'bukti_belanja.*.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png',
            'bukti_belanja_gabungan' => 'nullable|array',
            'bukti_belanja_gabungan.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png',
            'catatan_reviu' => 'nullable|array',
            'catatan_reviu.*' => 'nullable|string|max:2000',
            'catatan_reviu_gabungan' => 'nullable|string|max:2000',
            'status_bukti' => 'nullable|array',
            'status_bukti.*' => 'nullable|in:lengkap,tidak_lengkap,belum_dibelanjakan',
            'status_bukti_gabungan' => 'nullable|in:lengkap,tidak_lengkap,belum_dibelanjakan',
        ]);
        $user = auth()->user();
        $uploadSchedule = Schedule::where('type', 'upload_period')
            ->where('is_active', true)
            ->whereDate('start_date', '<=', now())
            ->whereDate('end_date', '>=', now())
            ->first();

        if (! $uploadSchedule && ! $user->can('manage_sp2d') && ! $user->isIrbanLike() && ! $user->hasRole('inspektur')) {
            return back()->with('error', 'Periode upload tidak aktif.');
        }

        $writtenPaths = [];
        try {
            return DB::transaction(function () use ($request, $user, $validated, &$writtenPaths) {
                $sp2dQuery = Sp2d::where('id', $validated['sp2d_id'])->with('budgetCodes');

                if ($user->hasRole('admin') || $user->hasRole('inspektur')) {
                    // Tidak dibatasi wilayah kerja
                } elseif ($user->isIrbanLike()) {
                    $assignedOpdIds = AuditorOpdAssignment::where('user_id', $user->irbanScopeUserId())->pluck('opd_id')->toArray();
                    $sp2dQuery->whereIn('opd_id', $assignedOpdIds);
                } else {
                    $sp2dQuery->where('opd_id', $user->opd_id);
                }

                $sp2d = $sp2dQuery->first();

                if (! $sp2d) {
                    return back()->with('error', 'SP2D tidak valid.');
                }

                // Create SPJ (satu record mewakili keseluruhan SP2D)
                $spj = DB::transaction(function () use ($sp2d) {
                    Sp2d::whereKey($sp2d->id)->lockForUpdate()->firstOrFail();
                    if (Spj::where('sp2d_id', $sp2d->id)->exists()) {
                        throw ValidationException::withMessages(['sp2d_id' => 'SPJ sudah tersedia. Gunakan Tambah Dokumen pada SPJ yang ada.']);
                    }

                    return Spj::create([
                        'sp2d_id' => $sp2d->id,
                        'opd_id' => $sp2d->opd_id,
                        'sp2d_budget_code_id' => null,
                        'nomor_spj' => $sp2d->nomor_sp2d,
                        'tanggal' => now()->format('Y-m-d'),
                        'jumlah' => $sp2d->jumlah_total,
                        'status' => 'submitted',
                    ]);
                });

                // Simpan file scan SP2D
                foreach ($request->file('sp2d_scan', []) as $file) {
                    $path = $file->store("spj/{$spj->id}/sp2d_scan", 'local');
                    if (! $path) {
                        throw new \RuntimeException('Gagal menyimpan berkas.');
                    }
                    $writtenPaths[] = $path;
                    SpjDocument::create([
                        'spj_id' => $spj->id,
                        'sp2d_budget_code_id' => null,
                        'document_type' => 'sp2d_scan',
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => $user->id,
                    ]);
                }

                // Simpan bukti belanja per kode belanja
                $budgetCodeIds = $sp2d->budgetCodes->pluck('id')->all();

                // SP2D dengan LS-GAJI/UP diupload sebagai satu bukti belanja gabungan
                // & status/catatan reviu gabungan. SP2D/TU tetap per akun belanja untuk semuanya.
                $isBuktiGabungan = $this->isBuktiBelanjaGabungan($sp2d->nomor_sp2d);
                $isStatusCatatanGabungan = $this->isStatusCatatanGabungan($sp2d->nomor_sp2d);

                foreach ($request->file('bukti_belanja', []) as $budgetCodeId => $files) {
                    if (! in_array((int) $budgetCodeId, $budgetCodeIds, true)) {
                        continue;
                    }

                    foreach ($files as $file) {
                        $path = $file->store("spj/{$spj->id}/bukti_belanja/{$budgetCodeId}", 'local');
                        if (! $path) {
                            throw new \RuntimeException('Gagal menyimpan berkas.');
                        }
                        $writtenPaths[] = $path;
                        SpjDocument::create([
                            'spj_id' => $spj->id,
                            'sp2d_budget_code_id' => $budgetCodeId,
                            'document_type' => 'bukti_belanja',
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $path,
                            'file_type' => $file->getClientMimeType(),
                            'size' => $file->getSize(),
                            'uploaded_by' => $user->id,
                        ]);
                    }
                }

                // Simpan bukti belanja gabungan (untuk SP2D LS-GAJI, satu upload untuk seluruh akun belanja)
                foreach ($request->file('bukti_belanja_gabungan', []) as $file) {
                    $path = $file->store("spj/{$spj->id}/bukti_belanja/gabungan", 'local');
                    if (! $path) {
                        throw new \RuntimeException('Gagal menyimpan berkas.');
                    }
                    $writtenPaths[] = $path;
                    SpjDocument::create([
                        'spj_id' => $spj->id,
                        'sp2d_budget_code_id' => null,
                        'document_type' => 'bukti_belanja',
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => $user->id,
                    ]);
                }

                // Simpan status kelengkapan bukti & catatan reviu (dapat diisi awal saat upload, atau dilengkapi kemudian oleh pemeriksa saat Reviu)
                if ($isStatusCatatanGabungan) {
                    // SP2D/LS-GAJI & UP: status & catatan reviu cukup sekali untuk seluruh akun belanja
                    $catatanGabungan = trim((string) $request->input('catatan_reviu_gabungan'));
                    $statusGabungan = $request->input('status_bukti_gabungan');
                    if (! in_array($statusGabungan, ['lengkap', 'tidak_lengkap', 'belum_dibelanjakan'], true)) {
                        $statusGabungan = null;
                    }

                    if ($catatanGabungan !== '' || $statusGabungan !== null) {
                        SpjFinding::create([
                            'spj_id' => $spj->id,
                            'sp2d_budget_code_id' => null,
                            'finding_type' => 'note',
                            'status_bukti' => $statusGabungan,
                            'description' => $catatanGabungan,
                            'created_by' => $user->id,
                        ]);
                    }
                } else {
                    $catatanReviu = $request->input('catatan_reviu', []);
                    $statusBukti = $request->input('status_bukti', []);

                    foreach ($budgetCodeIds as $budgetCodeId) {
                        $catatan = trim((string) ($catatanReviu[$budgetCodeId] ?? ''));
                        $status = $statusBukti[$budgetCodeId] ?? null;
                        if (! in_array($status, ['lengkap', 'tidak_lengkap', 'belum_dibelanjakan'], true)) {
                            $status = null;
                        }

                        if ($catatan === '' && $status === null) {
                            continue;
                        }

                        SpjFinding::create([
                            'spj_id' => $spj->id,
                            'sp2d_budget_code_id' => $budgetCodeId,
                            'finding_type' => 'note',
                            'status_bukti' => $status,
                            'description' => $catatan,
                            'created_by' => $user->id,
                        ]);
                    }
                }

                // Simpan dokumen lainnya (opsional, tidak terikat ke kode belanja tertentu)
                foreach ($request->file('dokumen_lainnya', []) as $file) {
                    $path = $file->store("spj/{$spj->id}/lainnya", 'local');
                    if (! $path) {
                        throw new \RuntimeException('Gagal menyimpan berkas.');
                    }
                    $writtenPaths[] = $path;
                    SpjDocument::create([
                        'spj_id' => $spj->id,
                        'sp2d_budget_code_id' => null,
                        'document_type' => 'lainnya',
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => $user->id,
                    ]);
                }

                ActivityLogger::log('upload', 'spj', "Upload SPJ untuk SP2D {$sp2d->nomor_sp2d} (OPD: {$sp2d->opd->nama_opd})", $spj);

                return redirect()->route('spj.upload', ['opd_id' => $sp2d->opd_id])->with('success', 'SPJ berhasil diupload.');
            });
        } catch (\Throwable $e) {
            foreach ($writtenPaths as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
    }

    /**
     * Unduh dokumen SPJ secara terautentikasi. Hanya dapat diakses oleh
     * admin/inspektur (tanpa batas wilayah), irban (wilayah yang ditugaskan),
     * atau pengguna OPD yang bersangkutan (bukan OPD lain).
     */
    public function downloadDocument(SpjDocument $document)
    {
        $user = auth()->user();
        $spj = $document->spj()->with('opd')->firstOrFail();

        $authorized = $user->hasRole('admin') || $user->hasRole('inspektur');

        if (! $authorized && $user->isIrbanLike()) {
            $authorized = AuditorOpdAssignment::where('user_id', $user->irbanScopeUserId())
                ->where('opd_id', $spj->opd_id)
                ->exists();
        }

        if (! $authorized && ! $user->isIrbanLike()) {
            $authorized = $user->opd_id !== null && $user->opd_id === $spj->opd_id;
        }

        if (! $authorized) {
            abort(403);
        }

        $disk = Storage::disk('local')->exists($document->file_path) ? 'local' : 'public';

        if (! Storage::disk($disk)->exists($document->file_path)) {
            abort(404);
        }

        return Storage::disk($disk)->response($document->file_path, $document->file_name);
    }

    /**
     * Hapus satu dokumen SPJ yang sudah terupload, untuk memperbaiki kesalahan
     * upload (salah file / salah akun belanja). Hanya dapat dilakukan oleh
     * pemilik OPD atau admin/irban, dan selama SPJ belum selesai direviu. Jika
     * file yang dihapus adalah dokumen terakhir, record SPJ ikut dihapus agar
     * SP2D kembali ke status "Belum ada SPJ".
     */
    public function destroyDocument(Request $request, SpjDocument $document)
    {
        return DB::transaction(function () use ($request, $document) {
            $spj = $document->spj()->firstOrFail();
            Sp2d::whereKey($spj->sp2d_id)->lockForUpdate()->firstOrFail();
            $spj = Spj::whereKey($spj->id)->lockForUpdate()->firstOrFail();
            $document = SpjDocument::whereKey($document->id)->lockForUpdate()->firstOrFail();
            $this->authorizeManageSpj($spj);
            if ($spj->status === 'reviewed') {
                throw ValidationException::withMessages(['spj' => 'Buka kembali reviu sebelum menghapus dokumen.']);
            }
            $disk = Storage::disk('local')->exists($document->file_path) ? 'local' : 'public';
            DB::table('pending_file_deletions')->insert(['disk' => $disk, 'path' => $document->file_path, 'created_at' => now(), 'updated_at' => now()]);
            $name = $document->file_name;
            $document->delete();
            ActivityLogger::log('delete', 'spj', "Menghapus dokumen {$name}; pembersihan berkas dijadwalkan.", $spj);
            $deleted = ! $spj->documents()->exists() && ! DB::table('spj_review_history')->where('spj_id', $spj->id)->exists();
            if ($deleted) {
                $spj->delete();
            }
            $message = 'Dokumen dihapus dari daftar. Berkas akan dibersihkan oleh scheduler.';

            return $request->wantsJson() ? response()->json(['message' => $message, 'spj_deleted' => $deleted]) : back()->with('success', $message);
        });
    }

    /**
     * Tambahkan dokumen baru ke SPJ yang sudah ada, untuk melengkapi/mengganti
     * file yang salah upload sebelumnya. Aturan wewenang sama dengan
     * destroyDocument(): pemilik OPD atau admin/irban, selama belum direviu.
     */
    public function addDocuments(Request $request, Spj $spj)
    {
        $this->authorizeManageSpj($spj);

        if ($spj->status === 'reviewed') {
            $message = 'SPJ yang sudah selesai direviu tidak dapat diubah. Hubungi admin untuk membuka kembali reviu.';

            return $request->wantsJson()
                ? response()->json(['message' => $message], 422)
                : back()->with('error', $message);
        }

        $request->validate([
            'sp2d_scan' => 'nullable|array',
            'sp2d_scan.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png',
            'dokumen_lainnya' => 'nullable|array',
            'dokumen_lainnya.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png',
            'bukti_belanja' => 'nullable|array',
            'bukti_belanja.*' => 'array',
            'bukti_belanja.*.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png',
            'bukti_belanja_gabungan' => 'nullable|array',
            'bukti_belanja_gabungan.*' => 'file|max:10240|mimes:pdf,jpg,jpeg,png',
        ]);

        $writtenPaths = [];
        try {
            return DB::transaction(function () use ($request, $spj, &$writtenPaths) {
                Sp2d::whereKey($spj->sp2d_id)->lockForUpdate()->firstOrFail();
                $spj = Spj::whereKey($spj->id)->lockForUpdate()->firstOrFail();
                if ($spj->status === 'reviewed') {
                    throw ValidationException::withMessages(['spj' => 'SPJ telah selesai direviu. Muat ulang halaman.']);
                }
                $user = auth()->user();
                $sp2d = $spj->sp2d()->with('budgetCodes')->first();
                $budgetCodeIds = $sp2d ? $sp2d->budgetCodes->pluck('id')->all() : [];

                foreach ($request->file('sp2d_scan', []) as $file) {
                    $path = $file->store("spj/{$spj->id}/sp2d_scan", 'local');
                    if (! $path) {
                        throw new \RuntimeException('Gagal menyimpan berkas.');
                    }
                    $writtenPaths[] = $path;
                    SpjDocument::create([
                        'spj_id' => $spj->id,
                        'sp2d_budget_code_id' => null,
                        'document_type' => 'sp2d_scan',
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => $user->id,
                    ]);
                }

                foreach ($request->file('bukti_belanja', []) as $budgetCodeId => $files) {
                    if (! in_array((int) $budgetCodeId, $budgetCodeIds, true)) {
                        continue;
                    }

                    foreach ($files as $file) {
                        $path = $file->store("spj/{$spj->id}/bukti_belanja/{$budgetCodeId}", 'local');
                        if (! $path) {
                            throw new \RuntimeException('Gagal menyimpan berkas.');
                        }
                        $writtenPaths[] = $path;
                        SpjDocument::create([
                            'spj_id' => $spj->id,
                            'sp2d_budget_code_id' => $budgetCodeId,
                            'document_type' => 'bukti_belanja',
                            'file_name' => $file->getClientOriginalName(),
                            'file_path' => $path,
                            'file_type' => $file->getClientMimeType(),
                            'size' => $file->getSize(),
                            'uploaded_by' => $user->id,
                        ]);
                    }
                }

                foreach ($request->file('bukti_belanja_gabungan', []) as $file) {
                    $path = $file->store("spj/{$spj->id}/bukti_belanja/gabungan", 'local');
                    if (! $path) {
                        throw new \RuntimeException('Gagal menyimpan berkas.');
                    }
                    $writtenPaths[] = $path;
                    SpjDocument::create([
                        'spj_id' => $spj->id,
                        'sp2d_budget_code_id' => null,
                        'document_type' => 'bukti_belanja',
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => $user->id,
                    ]);
                }

                foreach ($request->file('dokumen_lainnya', []) as $file) {
                    $path = $file->store("spj/{$spj->id}/lainnya", 'local');
                    if (! $path) {
                        throw new \RuntimeException('Gagal menyimpan berkas.');
                    }
                    $writtenPaths[] = $path;
                    SpjDocument::create([
                        'spj_id' => $spj->id,
                        'sp2d_budget_code_id' => null,
                        'document_type' => 'lainnya',
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'size' => $file->getSize(),
                        'uploaded_by' => $user->id,
                    ]);
                }

                $message = 'File tambahan berhasil diupload.';
                ActivityLogger::log('update', 'spj', "Menambahkan dokumen SPJ #{$spj->nomor_spj}", $spj);

                return $request->wantsJson()
                    ? response()->json(['message' => $message])
                    : back()->with('success', $message);
            });
        } catch (\Throwable $e) {
            foreach ($writtenPaths as $path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
    }

    /**
     * Pastikan user berwenang mengelola (menambah/menghapus) dokumen SPJ ini:
     * admin/inspektur (semua wilayah), irban (khusus OPD yang ditugaskan),
     * atau OPD pemilik SPJ itu sendiri.
     */
    private function authorizeManageSpj(Spj $spj): void
    {
        $user = auth()->user();
        $authorized = $user->hasRole('admin') || $user->hasRole('inspektur');

        if (! $authorized && $user->isIrbanLike()) {
            $authorized = AuditorOpdAssignment::where('user_id', $user->irbanScopeUserId())
                ->where('opd_id', $spj->opd_id)
                ->exists();
        }

        if (! $authorized && ! $user->isIrbanLike()) {
            $authorized = $user->opd_id !== null && $user->opd_id === $spj->opd_id;
        }

        if (! $authorized) {
            abort(403);
        }
    }

    public function reviewStore(Request $request, Spj $spj)
    {
        $user = auth()->user();

        if (! $user->can('review_spj')) {
            abort(403);
        }

        $this->authorizeManageSpj($spj);

        $validated = $request->validate([
            'status' => 'required|in:in_review,reviewed',
            'review_version' => 'required|integer|min:0',
            'review_reason' => 'nullable|string|max:2000',
            'mode_gabungan' => 'nullable|in:0,1',
            'catatan_reviu' => 'nullable|array',
            'catatan_reviu.*' => 'nullable|string|max:2000',
            'catatan_reviu_gabungan' => 'nullable|string|max:2000',
            'status_bukti' => 'nullable|array',
            'status_bukti.*' => 'nullable|in:lengkap,tidak_lengkap,belum_dibelanjakan',
            'status_bukti_gabungan' => 'nullable|in:lengkap,tidak_lengkap,belum_dibelanjakan',
        ]);

        DB::transaction(function () use ($spj, $request, $validated, $user) {
            Sp2d::whereKey($spj->sp2d_id)->lockForUpdate()->firstOrFail();
            $spj = Spj::whereKey($spj->id)->lockForUpdate()->firstOrFail();
            if ((int) $spj->review_version !== (int) $validated['review_version']) {
                throw ValidationException::withMessages(['review_version' => 'Penilaian telah berubah. Muat ulang halaman sebelum menyimpan.']);
            }
            if ($spj->status === 'reviewed' && ! $request->filled('review_reason')) {
                throw ValidationException::withMessages(['review_reason' => 'Isi alasan perubahan hasil reviu yang sudah selesai.']);
            }
            $sp2d = $spj->sp2d()->with('budgetCodes')->first();
            $budgetCodeIds = $sp2d ? $sp2d->budgetCodes->pluck('id')->all() : [];

            // Mode gabungan (1 bukti/status/catatan untuk seluruh akun belanja) dapat
            // dipilih manual oleh pemeriksa lewat toggle di modal Reviu. Jika toggle
            // tidak dikirim (mis. request lama), fallback ke deteksi pola nomor SP2D.
            $isStatusCatatanGabungan = $request->filled('mode_gabungan')
                ? $request->input('mode_gabungan') === '1'
                : ($sp2d && $this->isStatusCatatanGabungan($sp2d->nomor_sp2d));

            if ($validated['status'] === 'reviewed') {
                $allowed = ['lengkap', 'tidak_lengkap', 'belum_dibelanjakan'];
                $errors = [];
                if ($isStatusCatatanGabungan || empty($budgetCodeIds)) {
                    if (! in_array($request->input('status_bukti_gabungan'), $allowed, true)) {
                        $errors['status_bukti_gabungan'] = 'Tentukan penilaian bukti gabungan sebelum menyelesaikan reviu.';
                    }
                    $isStatusCatatanGabungan = true;
                } else {
                    foreach ($sp2d->budgetCodes as $code) {
                        if (! in_array($request->input("status_bukti.{$code->id}"), $allowed, true)) {
                            $errors["status_bukti.{$code->id}"] = "Akun {$code->kode_belanja} belum dinilai.";
                        }
                    }
                }
                if ($errors) {
                    throw ValidationException::withMessages($errors);
                }
            }

            DB::table('spj_review_history')->insert([
                'spj_id' => $spj->id, 'version' => $spj->review_version,
                'snapshot' => json_encode(['spj' => $spj->getAttributes(), 'findings' => $spj->findings()->get()->toArray()], JSON_THROW_ON_ERROR),
                'actor_id' => $user->id, 'reason' => $request->input('review_reason'),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            // Keep previous assessments in history; only the active scope remains in the live table.
            $obsolete = SpjFinding::where('spj_id', $spj->id);
            $isStatusCatatanGabungan ? $obsolete->whereNotNull('sp2d_budget_code_id') : $obsolete->whereNull('sp2d_budget_code_id');
            $obsolete->delete();

            if ($isStatusCatatanGabungan) {
                $catatan = trim((string) $request->input('catatan_reviu_gabungan'));
                $status = $request->input('status_bukti_gabungan');
                if (! in_array($status, ['lengkap', 'tidak_lengkap', 'belum_dibelanjakan'], true)) {
                    $status = null;
                }

                if ($catatan === '' && $status === null) {
                    SpjFinding::where('spj_id', $spj->id)->whereNull('sp2d_budget_code_id')->delete();
                } else {
                    SpjFinding::updateOrCreate(
                        ['spj_id' => $spj->id, 'sp2d_budget_code_id' => null],
                        ['finding_type' => 'note', 'status_bukti' => $status, 'description' => $catatan, 'created_by' => $user->id]
                    );
                }
            } else {
                $catatanReviu = $request->input('catatan_reviu', []);
                $statusBukti = $request->input('status_bukti', []);

                foreach ($budgetCodeIds as $budgetCodeId) {
                    $catatan = trim((string) ($catatanReviu[$budgetCodeId] ?? ''));
                    $status = $statusBukti[$budgetCodeId] ?? null;
                    if (! in_array($status, ['lengkap', 'tidak_lengkap', 'belum_dibelanjakan'], true)) {
                        $status = null;
                    }

                    if ($catatan === '' && $status === null) {
                        SpjFinding::where('spj_id', $spj->id)->where('sp2d_budget_code_id', $budgetCodeId)->delete();

                        continue;
                    }

                    SpjFinding::updateOrCreate(
                        ['spj_id' => $spj->id, 'sp2d_budget_code_id' => $budgetCodeId],
                        ['finding_type' => 'note', 'status_bukti' => $status, 'description' => $catatan, 'created_by' => $user->id]
                    );
                }
            }

            $spj->update([
                'review_version' => $spj->review_version + 1,
                'review_mode' => $isStatusCatatanGabungan ? 'combined' : 'account',
                'status' => $validated['status'],
                'reviewed_by' => $user->id,
                'reviewed_at' => $validated['status'] === 'reviewed' ? now() : null,
            ]);

            ActivityLogger::log('review', 'spj', "Mereviu SPJ #{$spj->nomor_spj} dengan status: {$validated['status']}", $spj);

        });

        return back()->with('success', 'Reviu berhasil disimpan.');
    }

    /**
     * SP2D dengan jenis LS-GAJI atau UP diupload sebagai satu bukti belanja
     * gabungan untuk seluruh akun belanja (keduanya umumnya tidak memiliki
     * rincian bukti per akun belanja di data sumbernya). SP2D/TU tetap
     * memakai bukti belanja per akun belanja seperti biasa.
     */
    private function isBuktiBelanjaGabungan(string $nomorSp2d): bool
    {
        return str_contains($nomorSp2d, 'SP2D/LS-GAJI')
            || str_contains($nomorSp2d, 'SP2D/UP');
    }

    /**
     * SP2D dengan jenis LS-GAJI atau UP menggunakan satu status kelengkapan &
     * satu catatan reviu untuk seluruh akun belanja. SP2D/TU tetap memakai
     * status & catatan reviu per akun belanja seperti biasa.
     */
    private function isStatusCatatanGabungan(string $nomorSp2d): bool
    {
        return str_contains($nomorSp2d, 'SP2D/LS-GAJI')
            || str_contains($nomorSp2d, 'SP2D/UP');
    }
}
