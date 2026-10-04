<table style="border-collapse: collapse; font-family: Calibri, Arial, sans-serif;">
    <tr>
        <td colspan="8" style="text-align:center; font-size:16px; font-weight:bold;">PEMERINTAH KABUPATEN PUNCAK JAYA</td>
    </tr>
    <tr>
        <td colspan="8" style="text-align:center; font-size:14px; font-weight:bold;">INSPEKTORAT DAERAH KABUPATEN PUNCAK JAYA</td>
    </tr>
    <tr>
        <td colspan="8" style="text-align:center; font-size:13px; font-weight:bold; text-decoration:underline;">LAPORAN HASIL REVIU SURAT PERTANGGUNGJAWABAN (SPJ)</td>
    </tr>
    <tr><td colspan="8"></td></tr>
    <tr>
        <td colspan="2" style="font-weight:bold;">Nama OPD :</td>
        <td colspan="6">{{ $meta['opd']->nama_opd }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold;">Periode :</td>
        <td colspan="6">{{ $meta['triwulan_label'] }}{{ $meta['tahun'] ? ' Tahun ' . $meta['tahun'] : ' (Semua Tahun)' }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold;">Jumlah Akun Belanja :</td>
        <td colspan="6">{{ $meta['total_akun'] }}</td>
    </tr>
    <tr>
        <td colspan="2" style="font-weight:bold;">Tanggal Cetak :</td>
        <td colspan="6">{{ date('d/m/Y H:i') }}</td>
    </tr>
    <tr><td colspan="8"></td></tr>
    <tr>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">No</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">No SP2D</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">Tanggal SP2D</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">Nilai SP2D</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">Akun Belanja</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">Nilai Akun Belanja</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">Status</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">Catatan Reviu</th>
    </tr>
    @forelse ($groups as $groupIndex => $group)
        @php
            $rowBg = $groupIndex % 2 === 0 ? '#ffffff' : '#f1f5f9';
        @endphp
        @foreach ($group->items as $i => $item)
            @php
                $statusColors = config('report-statuses')[$item->status] ?? config('report-statuses.Belum Ada Bukti');
                $statusBg = $statusColors['background'];
                $statusColor = $statusColors['text'];
            @endphp
            <tr>
                @if ($i === 0)
                    <td style="border:1px solid #cbd5e1; text-align:center; vertical-align:middle; background-color: {{ $rowBg }};" rowspan="{{ count($group->items) }}">{{ $group->no }}</td>
                    <td style="border:1px solid #cbd5e1; font-weight:bold; vertical-align:middle; background-color: {{ $rowBg }};" rowspan="{{ count($group->items) }}">{{ $group->nomor_sp2d }}</td>
                    <td style="border:1px solid #cbd5e1; text-align:center; vertical-align:middle; background-color: {{ $rowBg }};" rowspan="{{ count($group->items) }}">{{ $group->tanggal_sp2d->format('d/m/Y') }}</td>
                    <td style="border:1px solid #cbd5e1; text-align:right; vertical-align:middle; background-color: {{ $rowBg }};" rowspan="{{ count($group->items) }}">Rp {{ number_format($group->nilai_sp2d, 0, ',', '.') }}</td>
                @endif
                <td style="border:1px solid #cbd5e1; background-color: {{ $rowBg }};">{{ $item->akun_belanja }}</td>
                <td style="border:1px solid #cbd5e1; text-align:right; background-color: {{ $rowBg }};">Rp {{ number_format($item->nilai_akun, 0, ',', '.') }}</td>
                <td style="border:1px solid #cbd5e1; text-align:center; font-weight:bold; background-color: {{ $statusBg }}; color: {{ $statusColor }};">{{ $item->status }}</td>
                <td style="border:1px solid #cbd5e1; background-color: {{ $rowBg }};">{{ $item->catatan_reviu ?: '-' }}</td>
            </tr>
        @endforeach
    @empty
        <tr>
            <td colspan="8" style="border:1px solid #cbd5e1; text-align:center; padding:14px;">Tidak ada data SP2D untuk periode yang dipilih.</td>
        </tr>
    @endforelse
    <tr><td colspan="8"></td></tr>
    <tr>
        <td colspan="8" style="font-size:10px; color:#64748b;">Dicetak oleh {{ $meta['dicetak_oleh'] }} pada {{ date('d/m/Y H:i') }}</td>
    </tr>
</table>
