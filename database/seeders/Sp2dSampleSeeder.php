<?php

namespace Database\Seeders;

use App\Models\Opd;
use App\Models\Sp2d;
use App\Models\Sp2dBudgetCode;
use Illuminate\Database\Seeder;

class Sp2dSampleSeeder extends Seeder
{
    public function run(): void
    {
        $sp2dData = [
            // Dinas Pendidikan
            [
                'opd_id' => 1,
                'nomor_sp2d' => 'SP2D-001/2024',
                'tanggal' => '2024-01-15',
                'tahun' => 2024,
                'triwulan' => 1,
                'jumlah_total' => 50000000,
                'budget_codes' => [
                    ['kode' => '5.1.02.01.01.0001', 'uraian' => 'Makan Minum Rapat', 'jumlah' => 5000000],
                    ['kode' => '5.1.02.04.01.0001', 'uraian' => 'Pengadaan ATK', 'jumlah' => 15000000],
                    ['kode' => '5.1.02.10.01.0001', 'uraian' => 'Honor Narasumber', 'jumlah' => 30000000],
                ]
            ],
            [
                'opd_id' => 1,
                'nomor_sp2d' => 'SP2D-002/2024',
                'tanggal' => '2024-02-20',
                'tahun' => 2024,
                'triwulan' => 1,
                'jumlah_total' => 75000000,
                'budget_codes' => [
                    ['kode' => '5.1.02.01.01.0001', 'uraian' => 'Konsumsi Kegiatan Seminar', 'jumlah' => 10000000],
                    ['kode' => '5.1.02.06.01.0001', 'uraian' => 'Perbaikan Kendaraan Dinas', 'jumlah' => 65000000],
                ]
            ],
            // Dinas Kesehatan
            [
                'opd_id' => 2,
                'nomor_sp2d' => 'SP2D-003/2024',
                'tanggal' => '2024-01-25',
                'tahun' => 2024,
                'triwulan' => 1,
                'jumlah_total' => 120000000,
                'budget_codes' => [
                    ['kode' => '5.1.02.01.01.0001', 'uraian' => 'Makan Minum Operasional', 'jumlah' => 20000000],
                    ['kode' => '5.1.02.03.02.0001', 'uraian' => 'Listrik Gedung Puskesmas', 'jumlah' => 50000000],
                    ['kode' => '5.1.02.05.01.0001', 'uraian' => 'Telephone', 'jumlah' => 10000000],
                    ['kode' => '5.1.02.10.01.0001', 'uraian' => 'Honor Pembina Kesehatan', 'jumlah' => 40000000],
                ]
            ],
            // Dinas PU
            [
                'opd_id' => 3,
                'nomor_sp2d' => 'SP2D-005/2024',
                'tanggal' => '2024-02-05',
                'tahun' => 2024,
                'triwulan' => 1,
                'jumlah_total' => 250000000,
                'budget_codes' => [
                    ['kode' => '5.1.02.08.01.0001', 'uraian' => 'Pembangunan Jalan Desa', 'jumlah' => 200000000],
                    ['kode' => '5.1.02.01.01.0001', 'uraian' => 'Makan Minum Tim Survey', 'jumlah' => 5000000],
                    ['kode' => '5.1.02.06.01.0001', 'uraian' => 'Perbaikan Alat Berat', 'jumlah' => 45000000],
                ]
            ],
        ];

        foreach ($sp2dData as $data) {
            $budgetCodes = $data['budget_codes'];
            unset($data['budget_codes']);
            $data['created_by'] = 1;

            $sp2d = Sp2d::create($data);

            foreach ($budgetCodes as $code) {
                Sp2dBudgetCode::create([
                    'sp2d_id' => $sp2d->id,
                    'kode_belanja' => $code['kode'],
                    'uraian' => $code['uraian'],
                    'jumlah' => $code['jumlah'],
                ]);
            }
        }
    }
}