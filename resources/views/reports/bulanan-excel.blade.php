<table style="border-collapse: collapse; font-family: Calibri, Arial, sans-serif;">
    @php $colspan = count($bulanLabel) + 2; @endphp
    <tr>
        <td colspan="{{ $colspan }}" style="text-align:center; font-size:16px; font-weight:bold;">PEMERINTAH KABUPATEN PUNCAK JAYA</td>
    </tr>
    <tr>
        <td colspan="{{ $colspan }}" style="text-align:center; font-size:14px; font-weight:bold;">INSPEKTORAT DAERAH KABUPATEN PUNCAK JAYA</td>
    </tr>
    <tr>
        <td colspan="{{ $colspan }}" style="text-align:center; font-size:13px; font-weight:bold; text-decoration:underline;">LAPORAN KELENGKAPAN BUKTI SPJ PER BULAN</td>
    </tr>
    <tr><td colspan="{{ $colspan }}"></td></tr>
    <tr>
        <td style="font-weight:bold; width:140px;">Periode</td>
        <td style="width:15px;">:</td>
        <td colspan="{{ $colspan - 2 }}">{{ $meta['periode'] }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Tanggal Cetak</td>
        <td>:</td>
        <td colspan="{{ $colspan - 2 }}">{{ date('d/m/Y H:i') }}</td>
    </tr>
    <tr><td colspan="{{ $colspan }}"></td></tr>
    <tr>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">No</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">Dinas</th>
        @foreach ($bulanLabel as $label)
            <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">{{ $label }}</th>
        @endforeach
    </tr>
    @php
        $statusText = [
            'lengkap' => 'Lengkap',
            'tidak_lengkap' => 'Tidak Lengkap',
            'belum_upload' => 'Belum Upload Bukti SPJ',
        ];
        $statusColors = [
            'lengkap' => ['#e8f8f0', '#0f7a4d'],
            'tidak_lengkap' => ['#fdecec', '#b91c1c'],
            'belum_upload' => ['#f1f5f9', '#64748b'],
        ];
    @endphp
    @forelse ($opdReports as $i => $row)
        @php $rowBg = $i % 2 === 0 ? '#ffffff' : '#f8fafc'; @endphp
        <tr>
            <td style="border:1px solid #cbd5e1; text-align:center; background-color: {{ $rowBg }};">{{ $i + 1 }}</td>
            <td style="border:1px solid #cbd5e1; background-color: {{ $rowBg }};">{{ $row->opd->nama_opd }}</td>
            @foreach ($bulanLabel as $num => $label)
                @php
                    $cell = $monthlyMatrix[$row->opd->id][$num] ?? null;
                    [$statusBg, $statusColor] = $cell ? ($statusColors[$cell->status] ?? ['#f1f5f9', '#334155']) : [$rowBg, '#94a3b8'];
                @endphp
                <td style="border:1px solid #cbd5e1; text-align:center; font-weight:bold; background-color: {{ $statusBg }}; color: {{ $statusColor }};">
                    {{ $cell ? $statusText[$cell->status] : '-' }}
                </td>
            @endforeach
        </tr>
    @empty
        <tr>
            <td colspan="{{ $colspan }}" style="border:1px solid #cbd5e1; text-align:center; padding:14px;">Tidak ada data OPD untuk periode yang dipilih.</td>
        </tr>
    @endforelse
    <tr><td colspan="{{ $colspan }}"></td></tr>
    <tr>
        <td colspan="{{ $colspan }}" style="font-size:10px; color:#64748b;">Dicetak oleh {{ $meta['dicetak_oleh'] }} pada {{ date('d/m/Y H:i') }}</td>
    </tr>
</table>
