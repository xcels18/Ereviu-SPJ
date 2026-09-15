<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Ringkasan Kelengkapan Bukti SPJ per OPD</title>
    <style>
        @page { size: a4 landscape; margin: 15px 20px; }
        body { font-family: Arial, sans-serif; font-size: 11px; color: #1e293b; }
        .kop-table { width: 100%; border-collapse: collapse; border-bottom: 3px double #1e293b; padding-bottom: 8px; margin-bottom: 14px; }
        .kop-table td { padding-bottom: 8px; vertical-align: middle; }
        .kop-table h1 { font-size: 15px; margin: 0; text-transform: uppercase; }
        .kop-table h2 { font-size: 13px; margin: 2px 0 0; text-transform: uppercase; }
        .judul { text-align: center; margin-bottom: 14px; }
        .judul h3 { font-size: 13px; text-decoration: underline; margin: 0; text-transform: uppercase; }
        .data-umum { width: 100%; margin-bottom: 14px; font-size: 11px; }
        .data-umum td { padding: 2px 4px; vertical-align: top; }
        .data-umum td.label { width: 140px; }
        .data-umum td.sep { width: 12px; }
        table.main { width: 100%; border-collapse: collapse; margin-top: 6px; }
        table.main th, table.main td { border: 1px solid #999; padding: 5px 6px; text-align: left; font-size: 10px; vertical-align: top; }
        table.main th { background-color: #f1f5f9; text-align: center; }
        table.main tfoot td { font-weight: bold; background-color: #f8fafc; }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer-note { margin-top: 16px; font-size: 10px; color: #64748b; }
    </style>
</head>
<body>
    <table class="kop-table">
        <tr>
            <td style="width: 65px;">
                @if ($logo)
                    <img src="{{ $logo }}" style="width: 55px; height: auto;">
                @endif
            </td>
            <td style="text-align: center;">
                <h1>Pemerintah Kabupaten Puncak Jaya</h1>
                <h2>Inspektorat Daerah Kabupaten Puncak Jaya</h2>
            </td>
            <td style="width: 65px;"></td>
        </tr>
    </table>

    <div class="judul">
        <h3>Laporan Ringkasan Kelengkapan Bukti SPJ per OPD</h3>
    </div>

    <table class="data-umum">
        <tr>
            <td class="label">Periode</td><td class="sep">:</td><td>{{ $meta['periode'] }}</td>
            <td class="label">Tanggal Cetak</td><td class="sep">:</td><td>{{ date('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">Jumlah OPD</td><td class="sep">:</td><td>{{ $opdReports->count() }}</td>
            <td class="label"></td><td class="sep"></td><td></td>
        </tr>
    </table>

    <table class="main">
        <thead>
            <tr>
                <th style="width: 26px;">No</th>
                <th>OPD</th>
                <th class="text-center">SP2D</th>
                <th class="text-center">SPJ Diupload</th>
                <th class="text-center">Selesai Direviu</th>
                <th class="text-center">Total Akun</th>
                <th class="text-center">Lengkap</th>
                <th class="text-center">Tidak Lengkap</th>
                <th class="text-center">Belum Ada Bukti</th>
                <th class="text-center">% Lengkap</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($opdReports as $i => $row)
                <tr>
                    <td class="text-center">{{ $i + 1 }}</td>
                    <td>{{ $row->opd->nama_opd }}</td>
                    <td class="text-center">{{ $row->total_sp2d }}</td>
                    <td class="text-center">{{ $row->total_sp2d - $row->sp2d_belum_upload }}</td>
                    <td class="text-center">{{ $row->spj_reviewed }}</td>
                    <td class="text-center">{{ $row->total_akun }}</td>
                    <td class="text-center">{{ $row->akun_lengkap }}</td>
                    <td class="text-center">{{ $row->akun_tidak_lengkap }}</td>
                    <td class="text-center">{{ $row->akun_belum_ada_bukti }}</td>
                    <td class="text-center">{{ $row->persentase_lengkap }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center">Tidak ada data OPD untuk periode yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
        @if ($opdReports->count() > 0)
            <tfoot>
                <tr>
                    <td colspan="2">Total</td>
                    <td class="text-center">{{ $summary->total_sp2d }}</td>
                    <td class="text-center">{{ $summary->total_sp2d - $opdReports->sum('sp2d_belum_upload') }}</td>
                    <td class="text-center">{{ $summary->spj_reviewed }}</td>
                    <td class="text-center">{{ $summary->total_akun }}</td>
                    <td class="text-center">{{ $summary->akun_lengkap }}</td>
                    <td class="text-center">{{ $summary->akun_tidak_lengkap }}</td>
                    <td class="text-center">{{ $summary->akun_belum_ada_bukti }}</td>
                    <td class="text-center">{{ $summary->total_akun > 0 ? round(($summary->akun_lengkap / $summary->total_akun) * 100, 1) : 0 }}%</td>
                </tr>
            </tfoot>
        @endif
    </table>

    <p class="footer-note">Dicetak oleh {{ $meta['dicetak_oleh'] }} pada {{ date('d/m/Y H:i') }}</p>
</body>
</html>
