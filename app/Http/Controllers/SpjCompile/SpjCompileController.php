<?php

namespace App\Http\Controllers\SpjCompile;

use App\Http\Controllers\Controller;
use App\Jobs\BuildSpjExport;
use App\Models\Opd;
use App\Models\Sp2d;
use App\Models\Sp2dBudgetCode;
use App\Models\SpjExport;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class SpjCompileController extends Controller
{
    public function index()
    {
        $opds = Opd::orderBy('nama_opd')->get();

        return view('spj-compile.index', ['opds' => $opds]);
    }

    /**
     * Daftar SP2D milik satu OPD yang sudah memiliki SPJ terupload (untuk step 2).
     */
    public function getSp2dList(Opd $opd)
    {
        $sp2ds = Sp2d::where('opd_id', $opd->id)
            ->whereHas('spj')
            ->withCount('budgetCodes')
            ->orderBy('nomor_sp2d')
            ->paginate(50)
            ->through(function ($sp2d) {
                return [
                    'id' => $sp2d->id,
                    'nomor_sp2d' => $sp2d->nomor_sp2d,
                    'tanggal' => optional($sp2d->tanggal)->format('d-m-Y'),
                    'jumlah_total' => (float) $sp2d->jumlah_total,
                    'total_akun' => $sp2d->budget_codes_count,
                ];
            });

        return response()->json(['sp2ds' => $sp2ds->items(), 'next_page' => $sp2ds->hasMorePages() ? $sp2ds->currentPage() + 1 : null, 'total' => $sp2ds->total()]);
    }

    /**
     * Daftar akun belanja (yang sudah memiliki bukti belanja) untuk tiap SP2D
     * terpilih (untuk step 3).
     */
    public function getBudgetCodes(Request $request)
    {
        $validated = $request->validate([
            'sp2d_ids' => 'required|array|min:1|max:50',
            'sp2d_ids.*' => 'integer|exists:sp2d,id',
        ]);

        $sp2ds = Sp2d::whereIn('id', $validated['sp2d_ids'])
            ->with(['budgetCodes', 'spj.documents'])
            ->orderBy('nomor_sp2d')
            ->get();

        $result = $sp2ds->map(function ($sp2d) {
            $spj = $sp2d->spj->first();
            $documents = $spj ? $spj->documents : collect();
            $hasScan = $documents->where('document_type', 'sp2d_scan')->isNotEmpty();
            $hasGabungan = $documents->where('document_type', 'bukti_belanja')
                ->whereNull('sp2d_budget_code_id')->isNotEmpty();

            $akun = $sp2d->budgetCodes->map(function ($code) use ($documents, $hasGabungan) {
                $hasPerKode = $documents->where('document_type', 'bukti_belanja')
                    ->where('sp2d_budget_code_id', $code->id)->isNotEmpty();

                return [
                    'id' => $code->id,
                    'kode_belanja' => $code->kode_belanja,
                    'uraian' => $code->uraian,
                    'jumlah' => (float) $code->jumlah,
                    'has_bukti' => $hasPerKode || $hasGabungan,
                ];
            })->filter(fn ($item) => $item['has_bukti'])->values();

            // SP2D tanpa akun belanja terdaftar, namun sudah punya bukti belanja
            // gabungan terupload (mis. data lama tanpa rincian akun). Tampilkan
            // sebagai satu item virtual (id: 0) agar tetap bisa dipilih & dikompilasi.
            if ($sp2d->budgetCodes->isEmpty() && $hasGabungan) {
                $akun = collect([[
                    'id' => 0,
                    'kode_belanja' => '-',
                    'uraian' => 'Bukti Belanja (tanpa rincian akun)',
                    'jumlah' => (float) $sp2d->jumlah_total,
                    'has_bukti' => true,
                ]]);
            }

            return [
                'sp2d_id' => $sp2d->id,
                'nomor_sp2d' => $sp2d->nomor_sp2d,
                'has_scan' => $hasScan,
                'akun' => $akun,
            ];
        })->values();

        return response()->json(['data' => $result]);
    }

    /**
     * Kompilasi file SP2D + bukti akun belanja terpilih menjadi PDF gabungan
     * per SP2D, susun dalam folder Nama Dinas/Nomor SP2D, lalu unduh sebagai ZIP.
     */
    public function generate(Request $request)
    {
        $data = $request->validate([
            'opd_id' => ['required', 'integer', 'exists:opds,id'],
            'all' => ['required', 'boolean'],
            'mode' => ['required', Rule::in(['original', 'merged'])],
            'compression' => ['required', Rule::in(['fast', 'balanced'])],
            'sp2d_ids' => ['required_if:all,false', 'array', 'max:5000'],
            'sp2d_ids.*' => ['integer', 'distinct'],
            'budget_codes' => ['sometimes', 'array', 'max:5000'],
            'budget_codes.*' => ['array', 'min:1', 'max:2000'],
            'budget_codes.*.*' => ['integer', 'min:0'],
        ]);
        $ids = $data['sp2d_ids'] ?? [];
        if (! $data['all']) {
            abort_if(! $ids || Sp2d::where('opd_id', $data['opd_id'])->whereIn('id', $ids)->count() !== count($ids), 422, 'Pilihan SP2D tidak sesuai OPD.');
        }
        foreach (($data['budget_codes'] ?? []) as $sp2dId => $codes) {
            abort_unless(in_array((int) $sp2dId, $ids, true), 422, 'Pilihan akun tidak sesuai SP2D.');
            $realIds = array_values(array_unique(array_filter($codes, fn ($id) => $id !== 0)));
            abort_if(Sp2dBudgetCode::where('sp2d_id', $sp2dId)->whereIn('id', $realIds)->count() !== count($realIds), 422, 'Akun belanja tidak valid.');
            abort_if(in_array(0, $codes, true) && Sp2dBudgetCode::where('sp2d_id', $sp2dId)->exists(), 422, 'Pilihan bukti gabungan tidak valid.');
        }
        $export = Cache::lock('spj-export-admission', 30)->block(5, function () use ($request, $data, $ids) {
            return DB::transaction(function () use ($request, $data, $ids) {
                $active = SpjExport::whereIn('status', ['queued', 'preparing', 'archiving', 'cancelling']);
                abort_if((clone $active)->count() >= config('spj_compile.max_active', 5), 429, 'Antrean penuh. Coba lagi setelah paket lain selesai.');
                abort_if((clone $active)->where('opd_id', $data['opd_id'])->count() >= config('spj_compile.max_active_per_opd', 2), 429, 'OPD ini masih memiliki paket aktif. Tunggu hingga selesai.');
                abort_if(disk_free_space(storage_path()) < config('spj_compile.min_free_bytes'), 507, 'Ruang penyimpanan tidak mencukupi.');
                // Serialize simultaneous requests by the same actor, including double clicks.
                User::whereKey($request->user()->id)->lockForUpdate()->firstOrFail();
                abort_if(SpjExport::where('user_id', $request->user()->id)->whereIn('status', ['queued', 'preparing', 'archiving', 'cancelling'])->exists(), 409, 'Masih ada paket yang diproses. Tunggu atau batalkan terlebih dahulu.');
                $export = SpjExport::create([
                    'user_id' => $request->user()->id, 'opd_id' => $data['opd_id'],
                    'opd_name' => Opd::findOrFail($data['opd_id'])->nama_opd,
                    'mode' => $data['mode'], 'compression' => $data['compression'],
                    'selection' => ['all' => (bool) $data['all'], 'sp2d_ids' => $ids, 'budget_codes' => $data['budget_codes'] ?? []],
                ]);
                BuildSpjExport::dispatch($export->id);
                ActivityLogger::log('compile', 'spj_compile', 'Mengantrekan paket SPJ '.$export->id, $export);

                return $export;
            });

        });

        return response()->json(['id' => $export->id], 202);
    }

    public function history(Request $request)
    {
        return response()->json(['exports' => SpjExport::where('user_id', $request->user()->id)->latest()->limit(20)->get()->map(fn ($export) => $this->payload($export))]);
    }

    private function payload(SpjExport $export): array
    {
        return [
            'id' => $export->id, 'opd_name' => $export->opd_name, 'status' => $export->expires_at?->isPast() ? 'expired' : $export->status,
            'mode' => $export->mode, 'total_files' => $export->total_files, 'processed_files' => $export->processed_files,
            'total_bytes' => $export->total_bytes, 'processed_bytes' => $export->processed_bytes,
            'error' => $export->error, 'created_at' => $export->created_at->toIso8601String(),
            'expires_at' => $export->expires_at?->toIso8601String(),
            'parts' => collect($export->parts ?? [])->map(fn ($part, $index) => array_merge($part, [
                'url' => $export->status === 'completed' && $export->expires_at?->isFuture()
                    ? route('spj-compile.download', [$export, $index]) : null,
            ]))->values()->all(),
        ];
    }

    public function cancel(Request $request, SpjExport $export)
    {
        abort_unless($export->user_id === $request->user()->id, 403);
        SpjExport::whereKey($export->id)->where('status', 'queued')->update(['status' => 'cancelled', 'expires_at' => now()->addHour()]);
        SpjExport::whereKey($export->id)->whereIn('status', ['preparing', 'archiving'])->update(['status' => 'cancelling']);

        return response()->json(['status' => $export->fresh()->status]);
    }

    public function download(Request $request, SpjExport $export, int $part)
    {
        abort_unless($export->user_id === $request->user()->id, 403);
        abort_unless($export->status === 'completed' && $export->expires_at?->isFuture(), 410);
        $info = $export->parts[$part] ?? null;
        abort_unless($info, 404);
        $path = $export->directory().'/'.basename($info['name']);
        abort_unless(is_file($path), 404);

        // BinaryFileResponse supports Content-Length and Range; never load a browser Blob.
        return response()->download($path, 'SPJ-'.$export->id.'-'.$info['name'], [
            'Content-Type' => 'application/zip', 'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
