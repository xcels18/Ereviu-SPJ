<?php

namespace App\Http\Controllers\Opd;

use App\Http\Controllers\Controller;
use App\Models\Opd;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OpdController extends Controller
{
    public function index(Request $request)
    {
        $request->validate(['search' => 'nullable|string|max:200']);
        $query = Opd::query();
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($query) use ($search) {
                $query->where('nama_opd', 'like', '%'.$search.'%')
                    ->orWhere('singkatan', 'like', '%'.$search.'%')
                    ->orWhere('kode_unit', 'like', '%'.$search.'%');
            });
        }
        $totalOpd = Opd::count();
        $withSp2d = Opd::has('sp2d')->count();
        $opds = $query->withCount('sp2d')->orderBy('nama_opd')->orderBy('id')->paginate(15)->withQueryString();

        return view('opd.index', compact('opds', 'totalOpd', 'withSp2d'));
    }

    public function create()
    {
        return view('opd.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_opd' => 'required|string|max:255',
            'singkatan' => 'nullable|string|max:50',
        ]);

        $opd = Opd::create($validated);
        ActivityLogger::log('create', 'opd', "Menambahkan OPD: {$opd->nama_opd}", $opd);

        return redirect()->route('opd.index')->with('success', 'OPD berhasil ditambahkan.');
    }

    public function show(Opd $opd)
    {
        $sp2ds = $opd->sp2d()->orderByDesc('tanggal')->orderByDesc('id')->paginate(25);

        return view('opd.show', compact('opd', 'sp2ds'));
    }

    public function edit(Opd $opd)
    {
        return view('opd.edit', compact('opd'));
    }

    public function update(Request $request, Opd $opd)
    {
        $validated = $request->validate([
            'nama_opd' => 'required|string|max:255',
            'singkatan' => 'nullable|string|max:50',
        ]);

        $opd->update($validated);
        ActivityLogger::log('update', 'opd', "Memperbarui OPD: {$opd->nama_opd}", $opd);

        return redirect()->route('opd.index')->with('success', 'OPD berhasil diperbarui.');
    }

    public function destroy(Opd $opd)
    {
        DB::transaction(function () use ($opd) {
            ActivityLogger::log('delete', 'opd', "Menghapus OPD: {$opd->nama_opd}", $opd);
            $opd->delete();
        });

        return redirect()->route('opd.index')->with('success', 'OPD berhasil dihapus.');
    }
}
