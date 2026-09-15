<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanBulananExport implements FromView, ShouldAutoSize
{
    protected $opdReports;
    protected array $monthlyMatrix;
    protected array $bulanLabel;
    protected array $meta;

    public function __construct($opdReports, array $monthlyMatrix, array $bulanLabel, array $meta)
    {
        $this->opdReports = $opdReports;
        $this->monthlyMatrix = $monthlyMatrix;
        $this->bulanLabel = $bulanLabel;
        $this->meta = $meta;
    }

    public function view(): View
    {
        return view('reports.bulanan-excel', [
            'opdReports' => $this->opdReports,
            'monthlyMatrix' => $this->monthlyMatrix,
            'bulanLabel' => $this->bulanLabel,
            'meta' => $this->meta,
        ]);
    }
}
