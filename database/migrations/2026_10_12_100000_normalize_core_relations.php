<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // SQLite must be allowed to disable FK checks while rebuilding tables.
    public $withinTransaction = false;

    public function up(): void
    {
        // Check before DDL: MySQL schema changes cannot be rolled back atomically.
        if (DB::table('spj as j')->join('sp2d as p', 'p.id', '=', 'j.sp2d_id')->whereColumn('j.opd_id', '<>', 'p.opd_id')->exists()) {
            throw new RuntimeException('OPD SPJ berbeda dari SP2D. Rekonsiliasi data sebelum normalisasi.');
        }
        foreach (DB::table('sp2d')->select('id', 'tanggal', 'tahun', 'triwulan')->cursor() as $row) {
            $date = new DateTimeImmutable($row->tanggal);
            if ((int) $row->tahun !== (int) $date->format('Y') || (int) $row->triwulan !== (int) ceil((int) $date->format('n') / 3)) {
                throw new RuntimeException('Periode SP2D #'.$row->id.' tidak sama dengan tanggal. Rekonsiliasi sebelum migrasi.');
            }
        }
        $model = (new User)->getMorphClass();
        if (DB::table('users')->whereNotExists(function ($q) use ($model) {
            $q->selectRaw('1')->from('model_has_roles')->whereColumn('model_id', 'users.id')->where('model_type', $model);
        })->exists()) {
            throw new RuntimeException('Ada pengguna tanpa role Spatie. Tetapkan hak akses secara eksplisit sebelum migrasi.');
        }
        Schema::create('budget_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->timestamps();
        });
        Schema::table('sp2d_budget_codes', function (Blueprint $table) {
            $table->foreignId('budget_account_id')->nullable()->constrained('budget_accounts')->restrictOnDelete();
        });
        DB::table('sp2d_budget_codes')->orderBy('id')->chunkById(500, function ($rows) {
            foreach ($rows as $row) {
                DB::table('budget_accounts')->insertOrIgnore(['code' => $row->kode_belanja, 'created_at' => now(), 'updated_at' => now()]);
                $id = DB::table('budget_accounts')->where('code', $row->kode_belanja)->value('id');
                DB::table('sp2d_budget_codes')->where('id', $row->id)->update(['budget_account_id' => $id]);
            }
        });
        Schema::table('sp2d_budget_codes', function (Blueprint $table) {
            $table->unsignedBigInteger('budget_account_id')->nullable(false)->change();
            $table->dropColumn('kode_belanja');
        });
        Schema::table('spj', fn (Blueprint $table) => $table->dropConstrainedForeignId('opd_id'));
        Schema::table('sp2d', fn (Blueprint $table) => $table->dropColumn(['tahun', 'triwulan']));
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('role'));
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->string('role', 50)->nullable());
        $model = (new User)->getMorphClass();
        foreach (DB::table('users')->select('id')->cursor() as $user) {
            $role = DB::table('model_has_roles as m')->join('roles as r', 'r.id', '=', 'm.role_id')->where('m.model_type', $model)->where('m.model_id', $user->id)->orderBy('r.id')->value('r.name');
            DB::table('users')->where('id', $user->id)->update(['role' => $role]);
        }
        Schema::table('sp2d', function (Blueprint $table) {
            $table->unsignedSmallInteger('tahun')->nullable();
            $table->unsignedTinyInteger('triwulan')->nullable();
        });
        foreach (DB::table('sp2d')->select('id', 'tanggal')->cursor() as $row) {
            $date = new DateTimeImmutable($row->tanggal);
            DB::table('sp2d')->where('id', $row->id)->update(['tahun' => $date->format('Y'), 'triwulan' => (int) ceil((int) $date->format('n') / 3)]);
        }
        Schema::table('spj', fn (Blueprint $table) => $table->foreignId('opd_id')->nullable()->constrained('opds')->cascadeOnDelete());
        foreach (DB::table('spj as j')->join('sp2d as p', 'p.id', '=', 'j.sp2d_id')->select('j.id', 'p.opd_id')->cursor() as $row) {
            DB::table('spj')->where('id', $row->id)->update(['opd_id' => $row->opd_id]);
        }
        Schema::table('sp2d_budget_codes', fn (Blueprint $table) => $table->string('kode_belanja', 50)->nullable());
        foreach (DB::table('sp2d_budget_codes as b')->join('budget_accounts as a', 'a.id', '=', 'b.budget_account_id')->select('b.id', 'a.code')->cursor() as $row) {
            DB::table('sp2d_budget_codes')->where('id', $row->id)->update(['kode_belanja' => $row->code]);
        }
        Schema::table('sp2d_budget_codes', fn (Blueprint $table) => $table->dropConstrainedForeignId('budget_account_id'));
        Schema::dropIfExists('budget_accounts');
    }
};
