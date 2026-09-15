<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Temuan</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        h1 { margin-bottom: 0; }
        .date { color: #666; }
        .badge { padding: 2px 8px; border-radius: 4px; font-size: 10px; }
        .minor { background: #fef3cd; color: #856404; }
        .major { background: #f8d7da; color: #721c24; }
        .note { background: #d1ecf1; color: #0c5460; }
        .kop-table { width: 100%; border-collapse: collapse; border-bottom: 3px double #1e293b; padding-bottom: 8px; margin-bottom: 14px; }
        .kop-table td { padding-bottom: 8px; vertical-align: middle; border: none; }
        .kop-table h2 { font-size: 14px; margin: 0; text-transform: uppercase; }
        .kop-table h3 { font-size: 12px; margin: 2px 0 0; text-transform: uppercase; font-weight: normal; }
    </style>
</head>
<body>
    <table class="kop-table">
        <tr>
            <td style="width: 60px;">
                @if ($logo)
                    <img src="{{ $logo }}" style="width: 50px; height: auto;">
                @endif
            </td>
            <td style="text-align: center;">
                <h2>Pemerintah Kabupaten Puncak Jaya</h2>
                <h3>Inspektorat Daerah Kabupaten Puncak Jaya</h3>
            </td>
            <td style="width: 60px;"></td>
        </tr>
    </table>

    <h1>Laporan Temuan Review SPJ</h1>
    <p class="date">Dicetak: {{ date('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>SPJ</th>
                <th>OPD</th>
                <th>Jenis</th>
                <th>Deskripsi</th>
                <th>Auditor</th>
                <th>Tanggal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($findings as $finding)
            <tr>
                <td>{{ $finding->spj->nomor_spj }}</td>
                <td>{{ $finding->spj->opd->nama_opd }}</td>
                <td><span class="badge {{ $finding->finding_type }}">{{ strtoupper($finding->finding_type) }}</span></td>
                <td>{{ $finding->description }}</td>
                <td>{{ $finding->creator->name }}</td>
                <td>{{ $finding->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>