<?php

namespace Database\Seeders;

use App\Models\Opd;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SampleDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create sample OPDs
        $opds = [
            ['nama_opd' => 'Dinas Pendidikan'],
            ['nama_opd' => 'Dinas Kesehatan'],
            ['nama_opd' => 'Dinas Pekerjaan Umum'],
            ['nama_opd' => 'Dinas Sosial'],
            ['nama_opd' => 'Inspektorat'],
        ];

        foreach ($opds as $opdData) {
            Opd::create($opdData);
        }

        // Create admin inspektorat user
        $adminInsp = User::create([
            'name' => 'Admin Inspektorat',
            'email' => 'admin@inspektorat.go.id',
            'password' => Hash::make('password'),
            'opd_id' => 5, // Inspektorat
        ]);
        $adminInsp->assignRole('admin');

        // Create OPD Admin users
        $opdAdmin1 = User::create([
            'name' => 'Admin Dinas Pendidikan',
            'email' => 'admin@dinaspendidikan.go.id',
            'password' => Hash::make('password'),
            'opd_id' => 1,
        ]);
        $opdAdmin1->assignRole('opd_admin');

        $opdAdmin2 = User::create([
            'name' => 'Admin Dinas Kesehatan',
            'email' => 'admin@dinkes.go.id',
            'password' => Hash::make('password'),
            'opd_id' => 2,
        ]);
        $opdAdmin2->assignRole('opd_admin');

        // Create Irban users
        for ($i = 1; $i <= 4; $i++) {
            $irban = User::create([
                'name' => "Irban $i",
                'email' => "irban$i@inspektorat.go.id",
                'password' => Hash::make('password'),
                'opd_id' => 5, // Inspektorat
            ]);
            $irban->assignRole('irban');
        }

        // Create Inspektur
        $inspektur = User::create([
            'name' => 'Inspektur',
            'email' => 'inspektur@inspektorat.go.id',
            'password' => Hash::make('password'),
            'opd_id' => 5,
        ]);
        $inspektur->assignRole('inspektur');

        // Assign some OPDs to Irbans
        \App\Models\AuditorOpdAssignment::create(['user_id' => 3, 'opd_id' => 1]); // Irban 1 -> Dinas Pendidikan
        \App\Models\AuditorOpdAssignment::create(['user_id' => 3, 'opd_id' => 2]); // Irban 1 -> Dinas Kesehatan
        \App\Models\AuditorOpdAssignment::create(['user_id' => 4, 'opd_id' => 3]); // Irban 2 -> Dinas PU
    }
}