<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SpjExport implements FromCollection, WithHeadings
{
    protected $spjs;

    public function __construct($spjs)
    {
        $this->spjs = $spjs;
    }

    public function collection()
    {
        return $this->spjs->map(function ($spj) {
            return [
                $spj->nomor_spj,
                $spj->opd->nama_opd,
                $spj->budgetCode->kode_belanja,
                $spj->budgetCode->uraian,
                $spj->tanggal->format('d/m/Y'),
                'Rp ' . number_format($spj->jumlah, 0, ',', '.'),
                ucfirst($spj->status),
                $spj->reviewer->name ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return ['Nomor SPJ', 'OPD', 'Kode Belanja', 'Uraian', 'Tanggal', 'Jumlah', 'Status', 'Reviewed By'];
    }
}