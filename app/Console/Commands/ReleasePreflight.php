<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ReleasePreflight extends Command
{
    protected $signature = 'app:preflight';

    protected $description = 'Pemeriksaan read-only sebelum update aplikasi';

    public function handle(): int
    {
        $failed = false;
        $checks = [
            'SP2D memiliki beberapa SPJ' => DB::table('spj')->select('sp2d_id')->groupBy('sp2d_id')->havingRaw('count(*) > 1')->get()->count(),
            'OPD SPJ berbeda dari SP2D' => DB::table('spj as s')->join('sp2d as p', 'p.id', '=', 's.sp2d_id')->whereColumn('s.opd_id', '<>', 'p.opd_id')->count(),
            'Dokumen terhubung ke akun SP2D lain' => DB::table('spj_documents as d')->join('spj as s', 's.id', '=', 'd.spj_id')->join('sp2d_budget_codes as c', 'c.id', '=', 'd.sp2d_budget_code_id')->whereColumn('s.sp2d_id', '<>', 'c.sp2d_id')->count(),
            'Pengguna tanpa role Spatie' => User::doesntHave('roles')->count(),
        ];
        foreach ($checks as $name => $count) {
            $this->line($name.': '.$count);
            $failed = $failed || $count > 0;
        }
        $this->line('Ruang disk bebas: '.number_format(disk_free_space(storage_path()) / 1073741824, 2).' GiB');
        $this->line('Kunci aplikasi tersedia: '.(config('app.key') ? 'ya' : 'tidak'));
        $failed = $failed || ! config('app.key');
        $this->warn('Kode belanja berulang adalah rincian sah, tidak dianggap error. Pemeriksaan ini tidak menggantikan backup dan uji restore.');
        if (Schema::hasTable('spj_exports')) {
            $this->line('Pekerjaan ekspor aktif: '.DB::table('spj_exports')->whereIn('status', ['queued', 'preparing', 'archiving', 'cancelling'])->count());
        }

        return $failed ? self::FAILURE : self::SUCCESS;
    }
}
