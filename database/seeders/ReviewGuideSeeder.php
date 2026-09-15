<?php

namespace Database\Seeders;

use App\Models\ReviewGuide;
use Illuminate\Database\Seeder;

class ReviewGuideSeeder extends Seeder
{
    public function run(): void
    {
        $guides = [
            [
                'kode_rekening' => '5.1.02.01.01.0001',
                'nama_belanja' => 'Makan Minum',
                'required_documents' => json_encode([
                    'Kwitansi',
                    'Surat Pesanan',
                    'Fotodokumentasi',
                    'Bukti Setoran Pajak',
                    'Daftar Hadir'
                ]),
                'notes' => 'Untuk kegiatan rapat/seminar. Fotodokumentasi wajib menunjukkan suasana kegiatan.',
                'is_active' => true,
            ],
            [
                'kode_rekening' => '5.1.02.02.01.0001',
                'nama_belanja' => 'Pengiriman Dokumen',
                'required_documents' => json_encode([
                    'Kwitansi',
                    'Resi Pengiriman'
                ]),
                'notes' => null,
                'is_active' => true,
            ],
            [
                'kode_rekening' => '5.1.02.03.02.0001',
                'nama_belanja' => 'Listrik',
                'required_documents' => json_encode([
                    'Kwitansi',
                    'Rekening Listrik'
                ]),
                'notes' => null,
                'is_active' => true,
            ],
            [
                'kode_rekening' => '5.1.02.04.01.0001',
                'nama_belanja' => 'ATK',
                'required_documents' => json_encode([
                    'Kwitansi',
                    'Faktur Pembelian'
                ]),
                'notes' => null,
                'is_active' => true,
            ],
            [
                'kode_rekening' => '5.1.02.05.01.0001',
                'nama_belanja' => 'Telephone',
                'required_documents' => json_encode([
                    'Bukti Bayar',
                    'Rincian Telepon'
                ]),
                'notes' => null,
                'is_active' => true,
            ],
            [
                'kode_rekening' => '5.1.02.06.01.0001',
                'nama_belanja' => 'Perbaikan Kendaraan Dinas',
                'required_documents' => json_encode([
                    'Kwitansi',
                    'Surat Perintah Kerja',
                    'Daftar Suku Cadang',
                    'Foto Sebelum/Sesudah'
                ]),
                'notes' => 'Wajib ada foto sebelum dan sesudah perbaikan.',
                'is_active' => true,
            ],
            [
                'kode_rekening' => '5.1.02.07.01.0001',
                'nama_belanja' => 'Belanja Modal - Pengadaan Tanah',
                'required_documents' => json_encode([
                    'Sertifikat Tanah',
                    'Akta Jual Beli',
                    'Bukti Bayar PHT',
                    'NPWP Penjual',
                    'KTP Penjual'
                ]),
                'notes' => 'Pengadaan tanah harus dilengkapi legalitas lengkap.',
                'is_active' => true,
            ],
            [
                'kode_rekening' => '5.1.02.08.01.0001',
                'nama_belanja' => 'Belanja Modal - Pengadaan Bangunan',
                'required_documents' => json_encode([
                    'RAB',
                    'Kontrak',
                    'BAST',
                    'Foto Progress',
                    'Bukti Bayar'
                ]),
                'notes' => 'Untuk pembangunan baru atau rehab.',
                'is_active' => true,
            ],
            [
                'kode_rekening' => '5.1.02.10.01.0001',
                'nama_belanja' => 'Honor Narasumber',
                'required_documents' => json_encode([
                    'Surat Tugas',
                    'Kwitansi',
                    'Daftar Hadir',
                    'Materi/Modul'
                ]),
                'notes' => null,
                'is_active' => true,
            ],
            [
                'kode_rekening' => '5.1.02.11.01.0001',
                'nama_belanja' => 'Belanja Jamaah Haji',
                'required_documents' => json_encode([
                    'SK Haji',
                    'Bukti Pelunasan BPIH',
                    'Paspor',
                    'Kartu kuning'
                ]),
                'notes' => null,
                'is_active' => true,
            ],
        ];

        foreach ($guides as $guide) {
            ReviewGuide::create($guide);
        }
    }
}