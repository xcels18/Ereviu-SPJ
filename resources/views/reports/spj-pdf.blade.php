<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan SPJ</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        h1 { margin-bottom: 0; }
        .date { color: #666; }
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

    <h1>Laporan SPJ</h1>
    <p class="date">Dicetak: {{ date('d/m/Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>No SPJ</th>
                <th>OPD</th>
                <th>Kode Belanja</th>
                <th>Uraian</th>
                <th>Tanggal</th>
                <th>Jumlah</th>
                <th>Status</th>
                <th>Reviewed By</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($spjs as $spj)
            <tr>
                <td>{{ $spj->nomor_spj }}</td>
                <td>{{ $spj->opd->nama_opd }}</td>
                <td>{{ $spj->budgetCode->kode_belanja ?? 'Gabungan' }}</td>
                <td>{{ $spj->budgetCode->uraian ?? 'Seluruh Akun Belanja' }}</td>
                <td>{{ $spj->tanggal->format('d/m/Y') }}</td>
                <td>Rp {{ number_format($spj->jumlah, 0, ',', '.') }}</td>
                <td>{{ ucfirst($spj->status) }}</td>
                <td>{{ $spj->reviewer->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>