<?php

namespace App\Http\Controllers\Sp2d;

use App\Exports\Sp2dTemplateExport;
use App\Http\Controllers\Controller;
use App\Models\Opd;
use App\Models\Sp2d;
use App\Models\Sp2dBudgetCode;
use App\Models\Spj;
use App\Models\SpjDocument;
use App\Models\SpjFinding;
use App\Services\ActivityLogger;
use App\Services\Sp2dImportValidator;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class Sp2dController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string', 'max:200'],
            'opd_id' => ['nullable', 'integer', 'min:1'],
            'tahun' => ['nullable', 'integer', 'between:1900,2200'],
        ]);
        $sp2dQuery = Sp2d::with('opd');

        if (request('opd_id')) {
            $sp2dQuery->where('opd_id', request('opd_id'));
        }

        if (request('tahun')) {
            $sp2dQuery->where('tahun', request('tahun'));
        }

        if ($request->filled('search')) {
            $sp2dQuery->where('nomor_sp2d', 'like', '%'.trim($request->input('search')).'%');
        }

        $totalCount = (clone $sp2dQuery)->count();
        $totalOpd = (clone $sp2dQuery)->distinct()->count('opd_id');
        $totalNilai = (clone $sp2dQuery)->sum('jumlah_total');

        $sp2ds = $sp2dQuery->orderBy('tanggal', 'desc')->orderByDesc('id')->paginate(15);
        $opds = Opd::orderBy('nama_opd')->get();
        $availableYears = Sp2d::whereNotNull('tahun')->distinct()->orderByDesc('tahun')->pluck('tahun');

        return view('sp2d.index', compact('sp2ds', 'opds', 'totalCount', 'totalNilai', 'availableYears', 'totalOpd'));
    }

    public function create()
    {
        $opds = Opd::orderBy('nama_opd')->get();

        return view('sp2d.create', compact('opds'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'opd_id' => 'required|exists:opds,id',
            'nomor_sp2d' => 'required|string|max:100|unique:sp2d,nomor_sp2d',
            'tanggal' => 'required|date',
            'tahun' => 'required|integer|min:2020|max:2100',
            'triwulan' => 'required|integer|min:1|max:4',
            'jumlah_total' => 'required|numeric|min:0',
        ]);

        $validated['created_by'] = auth()->id();
        $sp2d = Sp2d::create($validated);
        ActivityLogger::log('create', 'sp2d', "Menambahkan SP2D: {$sp2d->nomor_sp2d}", $sp2d);

        // Handle budget codes if provided
        if ($request->has('budget_codes')) {
            foreach ($request->budget_codes as $code) {
                if (! empty($code['kode_belanja'])) {
                    Sp2dBudgetCode::create([
                        'sp2d_id' => $sp2d->id,
                        'kode_belanja' => $code['kode_belanja'],
                        'uraian' => $code['uraian'] ?? '',
                        'jumlah' => $code['jumlah'] ?? 0,
                    ]);
                }
            }
        }

        return redirect()->route('sp2d.show', $sp2d)->with('success', 'SP2D berhasil ditambahkan.');
    }

    public function show(Sp2d $sp2d)
    {
        $sp2d->load(['opd', 'budgetCodes', 'creator']);

        return view('sp2d.show', compact('sp2d'));
    }

    public function edit(Sp2d $sp2d)
    {
        $opds = Opd::orderBy('nama_opd')->get();
        $sp2d->load('budgetCodes');

        return view('sp2d.edit', compact('sp2d', 'opds'));
    }

    public function update(Request $request, Sp2d $sp2d)
    {
        $validated = $request->validate([
            'opd_id' => 'required|exists:opds,id',
            'nomor_sp2d' => 'required|string|max:100|unique:sp2d,nomor_sp2d,'.$sp2d->id,
            'tanggal' => 'required|date',
            'tahun' => 'required|integer|min:2020|max:2100',
            'triwulan' => 'required|integer|min:1|max:4',
            'jumlah_total' => 'required|numeric|min:0',
        ]);

        $rows = $request->validate([
            'budget_codes' => 'sometimes|array|max:2000',
            'budget_codes.*.id' => 'nullable|integer|distinct',
            'budget_codes.*.kode_belanja' => 'required|string|max:50',
            'budget_codes.*.uraian' => 'nullable|string|max:255',
            'budget_codes.*.jumlah' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($sp2d, $validated, $rows, $request) {
            $sp2d = Sp2d::whereKey($sp2d->id)->lockForUpdate()->firstOrFail();
            $hasSpj = $sp2d->spj()->exists();
            if ($hasSpj && (int) $validated['opd_id'] !== $sp2d->opd_id) {
                throw ValidationException::withMessages(['opd_id' => 'OPD tidak dapat dipindahkan karena SP2D sudah memiliki SPJ.']);
            }
            if ($hasSpj && ((string) $validated['nomor_sp2d'] !== $sp2d->nomor_sp2d || number_format((float) $validated['jumlah_total'], 2, '.', '') !== number_format((float) $sp2d->jumlah_total, 2, '.', ''))) {
                throw ValidationException::withMessages(['jumlah_total' => 'Nomor dan total SP2D yang sudah memiliki SPJ memerlukan koreksi resmi agar arsip tetap konsisten.']);
            }
            if ($sp2d->spj()->where('status', 'reviewed')->exists()) {
                throw ValidationException::withMessages(['sp2d' => 'SP2D sudah selesai direviu. Buka kembali reviu sebelum mengubah data.']);
            }
            if (array_key_exists('budget_codes', $rows) || $request->boolean('sync_budget_codes')) {
                $existing = $sp2d->budgetCodes()->lockForUpdate()->get()->keyBy('id');
                $kept = [];
                foreach ($rows['budget_codes'] ?? [] as $row) {
                    $id = $row['id'] ?? null;
                    unset($row['id']);
                    if ($id) {
                        $code = $existing->get($id);
                        if (! $code) {
                            throw ValidationException::withMessages(['budget_codes' => 'Akun belanja tidak terdaftar pada SP2D ini. Muat ulang halaman.']);
                        }
                        $kept[] = $code->id;
                        $code->fill($row);
                        if ($hasSpj && $code->isDirty(['kode_belanja', 'jumlah'])) {
                            throw ValidationException::withMessages(['budget_codes' => 'Kode dan nilai akun yang sudah memiliki SPJ tidak dapat diubah.']);
                        }
                        $code->save();
                    } else {
                        if ($hasSpj) {
                            throw ValidationException::withMessages(['budget_codes' => 'Akun baru tidak dapat ditambahkan setelah SPJ dibuat.']);
                        }
                        $sp2d->budgetCodes()->create($row);
                    }
                }
                foreach ($existing->except($kept) as $code) {
                    if ($hasSpj || SpjDocument::where('sp2d_budget_code_id', $code->id)->exists()
                        || SpjFinding::where('sp2d_budget_code_id', $code->id)->exists()
                        || Spj::where('sp2d_budget_code_id', $code->id)->exists()) {
                        throw ValidationException::withMessages(['budget_codes' => 'Akun yang sudah digunakan tidak dapat dihapus. Bukti dan hasil reviu harus tetap terhubung.']);
                    }
                    $code->delete();
                }
            }
            $sp2d->update($validated);
            ActivityLogger::log('update', 'sp2d', "Memperbarui SP2D: {$sp2d->nomor_sp2d}", $sp2d);
        });

        return redirect()->route('sp2d.show', $sp2d)->with('success', 'SP2D berhasil diperbarui.');
    }

    public function destroy(Sp2d $sp2d)
    {
        DB::transaction(function () use ($sp2d) {
            $sp2d = Sp2d::whereKey($sp2d->id)->lockForUpdate()->firstOrFail();
            if ($sp2d->spj()->exists()) {
                throw ValidationException::withMessages(['sp2d' => 'SP2D yang sudah memiliki SPJ tidak dapat dihapus.']);
            }
            ActivityLogger::log('delete', 'sp2d', "Menghapus SP2D: {$sp2d->nomor_sp2d}", $sp2d);
            $sp2d->delete();
        });

        return redirect()->route('sp2d.index')->with('success', 'SP2D berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:20480|mimes:xlsx,xls,csv',
        ]);

        try {
            $rows = Excel::toArray([], $request->file('file'))[0] ?? [];

            if (empty($rows)) {
                return back()->with('error', 'File kosong atau format tidak dikenali.');
            }

            $header = array_map(fn ($h) => strtoupper(trim((string) $h)), array_shift($rows));

            $requiredColumns = ['KDUNIT', 'NMUNIT', 'KDPER', 'NMPER', 'NOSP2D', 'TGL_SP2D', 'NILAI_SP2D'];
            $col = [];
            foreach ($requiredColumns as $name) {
                $idx = array_search($name, $header, true);
                if ($idx === false) {
                    return back()->with('error', "Format file tidak sesuai (format SP2D BKU), kolom $name tidak ditemukan.");
                }
                $col[$name] = $idx;
            }

            $parser = new Sp2dImportValidator;
            $groups = $parser->groups($rows, $col);

            DB::beginTransaction();

            $count = 0;
            $skipped = 0;

            foreach ($groups as $nomor => $data) {
                if (Sp2d::where('nomor_sp2d', $nomor)->exists()) {
                    $skipped++;

                    continue;
                }

                $kdunit = $data['kdunit'];
                $nmunit = $data['nmunit'];

                // Cari OPD berdasarkan kode unit (identitas stabil dari template)
                $opd = $kdunit !== '' ? Opd::where('kode_unit', $kdunit)->first() : null;

                if (! $opd) {
                    $matches = Opd::whereRaw('LOWER(nama_opd) = ?', [mb_strtolower($nmunit)])->get();
                    if ($matches->count() > 1 || ($matches->count() === 1 && $matches->first()->kode_unit && $matches->first()->kode_unit !== $kdunit)) {
                        throw ValidationException::withMessages(['file' => "Baris {$data['line']}: identitas OPD bertentangan dengan data master. Tidak ada data yang disimpan."]);
                    }
                    $opd = $matches->first();
                    if ($opd && ! $opd->kode_unit) {
                        $opd->update(['kode_unit' => $kdunit]);
                    }
                }
                if ($opd && mb_strtolower(trim($opd->nama_opd)) !== mb_strtolower($nmunit)) {
                    throw ValidationException::withMessages(['file' => "Baris {$data['line']}: nama OPD tidak sesuai kode unit pada data master. Koreksi file atau master OPD terlebih dahulu."]);
                }
                if (! $opd) {
                    $opd = Opd::create(['nama_opd' => $nmunit, 'kode_unit' => $kdunit]);
                }

                if (! $opd) {
                    $skipped++;

                    continue;
                }

                $tanggalExcel = $data['tanggal_excel'];
                $tanggal = is_numeric($tanggalExcel)
                    ? Date::excelToDateTimeObject($tanggalExcel)
                    : Carbon::parse($tanggalExcel);

                $jumlahTotal = $parser->decimal($data['total_cents']);

                $sp2d = Sp2d::create([
                    'opd_id' => $opd->id,
                    'nomor_sp2d' => $nomor,
                    'tanggal' => $tanggal->format('Y-m-d'),
                    'tahun' => (int) $tanggal->format('Y'),
                    'triwulan' => (int) ceil($tanggal->format('n') / 3),
                    'jumlah_total' => $jumlahTotal,
                    'created_by' => auth()->id(),
                ]);

                foreach ($data['details'] as $detail) {
                    if ($detail['kode_belanja'] === '') {
                        continue;
                    }

                    Sp2dBudgetCode::create([
                        'sp2d_id' => $sp2d->id,
                        'kode_belanja' => $detail['kode_belanja'],
                        'uraian' => $detail['uraian'],
                        'jumlah' => $detail['jumlah'],
                    ]);
                }

                $count++;
            }

            ActivityLogger::log('import', 'sp2d', "Import SP2D: {$count} berhasil, {$skipped} dilewati");
            DB::commit();

            $message = "Berhasil mengimport $count SP2D.";
            if ($skipped > 0) {
                $message .= " $skipped dilewati (nomor SP2D sudah ada atau OPD tidak ditemukan).";
            }

            return back()->with('success', $message);
        } catch (ValidationException $e) {
            if (DB::transactionLevel() > 0) {
                DB::rollBack();
            }
            throw $e;
        } catch (\Exception $e) {
            DB::rollBack();
            report($e);

            return back()->with('error', 'Gagal import: terjadi kesalahan saat memproses file. Periksa kembali format file dan coba lagi.');
        }
    }

    public function budgetCodes(Sp2d $sp2d)
    {
        return response()->json($sp2d->budgetCodes);
    }

    public function downloadTemplate()
    {
        return Excel::download(new Sp2dTemplateExport, 'template-sp2d.xlsx');
    }
}
