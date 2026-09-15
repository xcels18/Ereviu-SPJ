<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Kelengkapan Bukti SPJ per Bulan</title>
    <style>
        @page { size: a4 landscape; margin: 15px 20px; }
        body { font-family: Arial, sans-serif; font-size: 10px; color: #1e293b; }
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
        table.main th, table.main td { border: 1px solid #999; padding: 4px 3px; text-align: center; font-size: 8.5px; vertical-align: middle; }
        table.main th { background-color: #f1f5f9; }
        table.main td.opd-name { text-align: left; font-size: 9px; }
        .status-lengkap { color: #0f7a4d; font-weight: bold; }
        .status-tidak-lengkap { color: #b91c1c; font-weight: bold; }
        .status-belum-upload { color: #64748b; }
        .legend { margin-top: 10px; font-size: 9px; color: #475569; }
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
        <h3>Laporan Kelengkapan Bukti SPJ per Bulan</h3>
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

    @php
        $statusText = [
            'lengkap' => 'Lengkap',
            'tidak_lengkap' => 'Tidak Lengkap',
            'belum_upload' => 'Belum Upload',
        ];
        $statusClass = [
            'lengkap' => 'status-lengkap',
            'tidak_lengkap' => 'status-tidak-lengkap',
            'belum_upload' => 'status-belum-upload',
        ];
    @endphp

    <table class="main">
        <thead>
            <tr>
                <th style="width: 22px;">No</th>
                <th style="text-align: left; width: 140px;">Dinas</th>
                @foreach ($bulanLabel as $label)
                    <th>{{ substr($label, 0, 3) }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($opdReports as $i => $row)
                <tr>
                    <td>{{ $i + 1 }}</td>
                    <td class="opd-name">{{ $row->opd->nama_opd }}</td>
                    @foreach ($bulanLabel as $num => $label)
                        @php $cell = $monthlyMatrix[$row->opd->id][$num] ?? null; @endphp
                        <td class="{{ $cell ? $statusClass[$cell->status] : '' }}">
                            {{ $cell ? $statusText[$cell->status] : '-' }}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($bulanLabel) + 2 }}">Tidak ada data OPD untuk periode yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="legend">
        <strong>Lengkap</strong>: seluruh SP2D pada bulan tsb sudah lengkap buktinya &middot;
        <strong>Tidak Lengkap</strong>: sebagian SP2D sudah/belum diupload buktinya &middot;
        <strong>Belum Upload</strong>: seluruh SP2D pada bulan tsb belum diupload sama sekali &middot;
        <strong>-</strong>: tidak ada SP2D pada bulan tsb
    </p>

    <p class="footer-note">Dicetak oleh {{ $meta['dicetak_oleh'] }} pada {{ date('d/m/Y H:i') }}</p>
</body>
</html>
