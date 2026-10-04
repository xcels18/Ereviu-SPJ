<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Hasil Reviu SPJ</title>
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
        table.main tr.group-start td { border-top: 2px solid #1e293b; }
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
        <h3>Laporan Hasil Reviu Surat Pertanggungjawaban (SPJ)</h3>
    </div>

    <table class="data-umum">
        <tr>
            <td class="label">Nama OPD</td><td class="sep">:</td><td>{{ $meta['opd']->nama_opd }}</td>
            <td class="label">Periode</td><td class="sep">:</td><td>{{ $meta['triwulan_label'] }}{{ $meta['tahun'] ? ' Tahun ' . $meta['tahun'] : ' (Semua Tahun)' }}</td>
        </tr>
        <tr>
            <td class="label">Jumlah Akun Belanja</td><td class="sep">:</td><td>{{ $meta['total_akun'] }}</td>
            <td class="label">Tanggal Cetak</td><td class="sep">:</td><td>{{ date('d/m/Y H:i') }}</td>
        </tr>
    </table>

    <table class="main">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>No SP2D</th>
                <th>Tanggal SP2D</th>
                <th>Nilai SP2D</th>
                <th>Akun Belanja</th>
                <th>Nilai Akun Belanja</th>
                <th style="width: 70px;">Status</th>
                <th>Catatan Reviu</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($groups as $group)
                @foreach ($group->items as $i => $item)
                    <tr @if ($i === 0) class="group-start" @endif>
                        <td class="text-center">{{ $i === 0 ? $group->no : '' }}</td>
                        <td>{{ $i === 0 ? $group->nomor_sp2d : '' }}</td>
                        <td>{{ $i === 0 ? $group->tanggal_sp2d->format('d/m/Y') : '' }}</td>
                        <td class="text-right">{{ $i === 0 ? 'Rp ' . number_format($group->nilai_sp2d, 0, ',', '.') : '' }}</td>
                        <td>{{ $item->akun_belanja }}</td>
                        <td class="text-right">Rp {{ number_format($item->nilai_akun, 0, ',', '.') }}</td>
                        @php
                            $statusColors = config('report-statuses')[$item->status] ?? config('report-statuses.Belum Ada Bukti');
                        @endphp
                        <td style="text-align:center; font-weight:bold; background-color: {{ $statusColors['background'] }}; color: {{ $statusColors['text'] }};">{{ $item->status }}</td>
                        <td>{{ $item->catatan_reviu ?: '-' }}</td>
                    </tr>
                @endforeach
            @empty
                <tr>
                    <td colspan="8" class="text-center">Tidak ada data SP2D untuk periode yang dipilih.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p class="footer-note">Dicetak oleh {{ $meta['dicetak_oleh'] }} pada {{ date('d/m/Y H:i') }}</p>
</body>
</html>
