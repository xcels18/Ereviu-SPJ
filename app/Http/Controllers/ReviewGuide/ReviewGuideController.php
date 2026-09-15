<?php

namespace App\Http\Controllers\ReviewGuide;

use App\Http\Controllers\Controller;
use App\Imports\ReviewGuideImport;
use App\Models\ReviewGuide;
use App\Services\ActivityLogger;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ReviewGuideController extends Controller
{
    public function index()
    {
        $guides = ReviewGuide::orderBy('kode_rekening')->paginate(15);
        return view('review-guides.index', compact('guides'));
    }

    public function create()
    {
        return view('review-guides.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_rekening' => 'required|string|max:50|unique:review_guides,kode_rekening',
            'nama_belanja' => 'required|string|max:255',
            'required_documents' => 'required|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['required_documents'] = array_filter(array_map('trim', explode(',', $validated['required_documents'])));
        $validated['required_documents'] = json_encode($validated['required_documents']);
        $validated['created_by'] = auth()->id();

        $reviewGuide = ReviewGuide::create($validated);
        ActivityLogger::log('create', 'review_guide', "Menambahkan panduan reviu: {$reviewGuide->kode_rekening}", $reviewGuide);

        return redirect()->route('review-guides.index')->with('success', 'Panduan reviu berhasil ditambahkan.');
    }

    public function edit(ReviewGuide $reviewGuide)
    {
        return view('review-guides.edit', compact('reviewGuide'));
    }

    public function update(Request $request, ReviewGuide $reviewGuide)
    {
        $validated = $request->validate([
            'kode_rekening' => 'required|string|max:50|unique:review_guides,kode_rekening,' . $reviewGuide->id,
            'nama_belanja' => 'required|string|max:255',
            'required_documents' => 'required|string',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['required_documents'] = array_filter(array_map('trim', explode(',', $validated['required_documents'])));
        $validated['required_documents'] = json_encode($validated['required_documents']);

        $reviewGuide->update($validated);
        ActivityLogger::log('update', 'review_guide', "Memperbarui panduan reviu: {$reviewGuide->kode_rekening}", $reviewGuide);

        return redirect()->route('review-guides.index')->with('success', 'Panduan reviu berhasil diperbarui.');
    }

    public function destroy(ReviewGuide $reviewGuide)
    {
        ActivityLogger::log('delete', 'review_guide', "Menghapus panduan reviu: {$reviewGuide->kode_rekening}", $reviewGuide);
        $reviewGuide->delete();

        return redirect()->route('review-guides.index')->with('success', 'Panduan reviu berhasil dihapus.');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            Excel::import(new ReviewGuideImport, $request->file('file'));
            ActivityLogger::log('import', 'review_guide', 'Import panduan reviu dari file Excel');
            return back()->with('success', 'Panduan berhasil diimport.');
        } catch (\Exception $e) {
            report($e);
            return back()->with('error', 'Gagal import: terjadi kesalahan saat memproses file. Periksa kembali format file dan coba lagi.');
        }
    }

    public function export()
    {
        return Excel::download(new \App\Exports\ReviewGuideExport, 'panduan-reviu-' . date('Ymd') . '.xlsx');
    }
}