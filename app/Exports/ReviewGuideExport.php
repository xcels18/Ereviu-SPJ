<?php

namespace App\Exports;

use App\Models\ReviewGuide;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReviewGuideExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return ReviewGuide::orderBy('kode_rekening')->get()->map(function ($guide) {
            return [
                'kode_rekening' => $guide->kode_rekening,
                'nama_belanja' => $guide->nama_belanja,
                'dokumen_wajib' => implode(', ', $guide->required_documents ?? []),
                'catatan' => $guide->notes ?? '',
                'is_active' => $guide->is_active ? 'Aktif' : 'Nonaktif',
            ];
        });
    }

    public function headings(): array
    {
        return ['Kode Rekening', 'Nama Belanja', 'Dokumen Wajib', 'Catatan', 'Status'];
    }
}