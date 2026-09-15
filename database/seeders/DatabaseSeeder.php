<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RolePermissionSeeder::class,
            ReviewGuideSeeder::class,
            SampleDataSeeder::class,
            Sp2dSampleSeeder::class,
        ]);
    }
}