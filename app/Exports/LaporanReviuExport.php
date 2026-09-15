<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class LaporanReviuExport implements FromView, ShouldAutoSize, WithColumnWidths
{
    protected array $groups;
    protected array $meta;

    public function __construct(array $groups, array $meta)
    {
        $this->groups = $groups;
        $this->meta = $meta;
    }

    public function view(): View
    {
        return view('reports.cetak-excel', [
            'groups' => $this->groups,
            'meta' => $this->meta,
        ]);
    }

    /**
     * ShouldAutoSize tidak menghitung lebar kolom dengan benar untuk sel yang
     * digabung (rowspan No/No SP2D/Tanggal SP2D/Nilai SP2D), sehingga kolom
     * tersebut perlu lebar tetap agar isinya tidak terpotong saat dibuka di Excel.
     */
    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 28,
            'C' => 16,
            'D' => 18,
        ];
    }
}
