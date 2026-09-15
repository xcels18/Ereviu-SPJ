<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class LaporanRingkasanOpdExport implements FromView, ShouldAutoSize
{
    protected $opdReports;
    protected $summary;
    protected array $meta;

    public function __construct($opdReports, $summary, array $meta)
    {
        $this->opdReports = $opdReports;
        $this->summary = $summary;
        $this->meta = $meta;
    }

    public function view(): View
    {
        return view('reports.ringkasan-opd-excel', [
            'opdReports' => $this->opdReports,
            'summary' => $this->summary,
            'meta' => $this->meta,
        ]);
    }
}
