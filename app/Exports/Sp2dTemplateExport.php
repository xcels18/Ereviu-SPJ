<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class Sp2dTemplateExport implements FromArray, WithHeadings
{
    public function array(): array
    {
        return [
            [
                'SEKRETARIAT DPRD',
                '4.02.0.00.0.00.01.0000',
                'SEKRETARIAT DPRD',
                'PROGRAM PENUNJANG URUSAN PEMERINTAHAN DAERAH KABUPATEN/KOTA',
                'Layanan Keuangan dan Kesejahteraan DPRD',
                'Penyelenggaraan Administrasi Keuangan DPRD',
                '5.1.01.04.008.00001.',
                'Belanja Tunjangan Komunikasi Intensif Pimpinan dan Anggota DPRD',
                '00059/SP2D/LS/4.02.0.00.0.00.01.0000/2026',
                '2026-01-02',
                'Pengajuan SP2D LS Atas Tunjangan Komunikasi Insentif Pimpinan dan Anggota DPRD Bulan Januari Tahun 2026',
                558600000,
                '00001-B01',
                '2026-01-02',
                'Pengajuan SP2D LS Atas Tunjangan Komunikasi Insentif Pimpinan dan Anggota DPRD Bulan Januari Tahun 2026',
                558600000,
                '', // SUMBER_DANA: optional; do not assume the source of funds.
            ],
        ];
    }

    public function headings(): array
    {
        return [
            'NAMA_URUSAN',
            'KDUNIT',
            'NMUNIT',
            'NAMA_PROGRAM',
            'NAMA_KEGIATAN',
            'NAMA_SUB_KEGIATAN',
            'KDPER',
            'NMPER',
            'NOSP2D',
            'TGL_SP2D',
            'URAIAN',
            'NILAI_SP2D',
            'NO_BKU',
            'TGL_BKU',
            'URAIAN',
            'NILAI_BKU',
            'SUMBER_DANA',
        ];
    }
}
