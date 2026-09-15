<table style="border-collapse: collapse; font-family: Calibri, Arial, sans-serif;">
    <tr>
        <td colspan="10" style="text-align:center; font-size:16px; font-weight:bold;">PEMERINTAH KABUPATEN PUNCAK JAYA</td>
    </tr>
    <tr>
        <td colspan="10" style="text-align:center; font-size:14px; font-weight:bold;">INSPEKTORAT DAERAH KABUPATEN PUNCAK JAYA</td>
    </tr>
    <tr>
        <td colspan="10" style="text-align:center; font-size:13px; font-weight:bold; text-decoration:underline;">LAPORAN RINGKASAN KELENGKAPAN BUKTI SPJ PER OPD</td>
    </tr>
    <tr><td colspan="10"></td></tr>
    <tr>
        <td style="font-weight:bold; width:140px;">Periode</td>
        <td style="width:15px;">:</td>
        <td colspan="8">{{ $meta['periode'] }}</td>
    </tr>
    <tr>
        <td style="font-weight:bold;">Tanggal Cetak</td>
        <td>:</td>
        <td colspan="8">{{ date('d/m/Y H:i') }}</td>
    </tr>
    <tr><td colspan="10"></td></tr>
    <tr>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">No</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">OPD</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">SP2D</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">SPJ Diupload</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">Selesai Direviu</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">Total Akun</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">Lengkap</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">Tidak Lengkap</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">Belum Ada Bukti</th>
        <th style="background-color:#1e3a5f; color:#ffffff; font-weight:bold; border:1px solid #0f2338; text-align:center; padding:6px;">% Lengkap</th>
    </tr>
    @forelse ($opdReports as $i => $row)
        @php $rowBg = $i % 2 === 0 ? '#ffffff' : '#f1f5f9'; @endphp
        <tr>
            <td style="border:1px solid #cbd5e1; text-align:center; background-color: {{ $rowBg }};">{{ $i + 1 }}</td>
            <td style="border:1px solid #cbd5e1; background-color: {{ $rowBg }};">{{ $row->opd->nama_opd }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; background-color: {{ $rowBg }};">{{ $row->total_sp2d }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; background-color: {{ $rowBg }};">{{ $row->total_sp2d - $row->sp2d_belum_upload }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; background-color: {{ $rowBg }};">{{ $row->spj_reviewed }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; background-color: {{ $rowBg }};">{{ $row->total_akun }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; background-color: {{ $rowBg }};">{{ $row->akun_lengkap }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; background-color: {{ $rowBg }};">{{ $row->akun_tidak_lengkap }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; background-color: {{ $rowBg }};">{{ $row->akun_belum_ada_bukti }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; background-color: {{ $rowBg }};">{{ $row->persentase_lengkap }}%</td>
        </tr>
    @empty
        <tr>
            <td colspan="10" style="border:1px solid #cbd5e1; text-align:center; padding:14px;">Tidak ada data OPD untuk periode yang dipilih.</td>
        </tr>
    @endforelse
    @if ($opdReports->count() > 0)
        <tr>
            <td colspan="2" style="border:1px solid #cbd5e1; font-weight:bold; background-color:#f8fafc;">Total</td>
            <td style="border:1px solid #cbd5e1; text-align:center; font-weight:bold; background-color:#f8fafc;">{{ $summary->total_sp2d }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; font-weight:bold; background-color:#f8fafc;">{{ $summary->total_sp2d - $opdReports->sum('sp2d_belum_upload') }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; font-weight:bold; background-color:#f8fafc;">{{ $summary->spj_reviewed }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; font-weight:bold; background-color:#f8fafc;">{{ $summary->total_akun }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; font-weight:bold; background-color:#f8fafc;">{{ $summary->akun_lengkap }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; font-weight:bold; background-color:#f8fafc;">{{ $summary->akun_tidak_lengkap }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; font-weight:bold; background-color:#f8fafc;">{{ $summary->akun_belum_ada_bukti }}</td>
            <td style="border:1px solid #cbd5e1; text-align:center; font-weight:bold; background-color:#f8fafc;">{{ $summary->total_akun > 0 ? round(($summary->akun_lengkap / $summary->total_akun) * 100, 1) : 0 }}%</td>
        </tr>
    @endif
    <tr><td colspan="10"></td></tr>
    <tr>
        <td colspan="10" style="font-size:10px; color:#64748b;">Dicetak oleh {{ $meta['dicetak_oleh'] }} pada {{ date('d/m/Y H:i') }}</td>
    </tr>
</table>
