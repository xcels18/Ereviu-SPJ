<?php

namespace Tests\Feature;

use App\Jobs\BuildSpjExport;
use App\Models\Opd;
use App\Models\Sp2d;
use App\Models\Spj;
use App\Models\SpjDocument;
use App\Models\SpjExport;
use App\Models\User;
use App\Services\SpjExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\Group;
use setasign\Fpdi\Fpdi;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;
use ZipArchive;

class SpjExportTest extends TestCase
{
    use RefreshDatabase;

    private array $directories = [];

    private mixed $realQueue;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Storage::fake('public');
        config(['spj_compile.min_free_bytes' => 0, 'spj_compile.part_bytes' => 10, 'spj_compile.part_files' => 2]);
        $this->realQueue = Queue::getFacadeRoot();
        Queue::fake();
    }

    protected function tearDown(): void
    {
        foreach ($this->directories as $directory) {
            File::deleteDirectory($directory);
        }
        parent::tearDown();
    }

    private function fixture(): array
    {
        Permission::findOrCreate('compile_spj', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('compile_spj');
        $opd = Opd::create(['nama_opd' => 'OPD Arsip']);
        $sp2d = Sp2d::create(['opd_id' => $opd->id, 'nomor_sp2d' => 'SP2D/UP/TEST', 'tahun' => 2026, 'triwulan' => 3, 'tanggal' => '2026-09-05', 'jumlah_total' => 100, 'created_by' => $user->id]);
        $spj = Spj::create(['opd_id' => $opd->id, 'sp2d_id' => $sp2d->id, 'nomor_spj' => 'TEST', 'tanggal' => '2026-09-05', 'jumlah' => 100, 'status' => 'submitted']);
        foreach (['aaaaaaaa', 'bbbbbbbb', 'cccccccc'] as $i => $content) {
            Storage::disk('local')->put('spj/test-'.$i.'.pdf', $content);
            SpjDocument::create(['spj_id' => $spj->id, 'document_type' => 'sp2d_scan', 'file_name' => 'same-name.pdf', 'file_path' => 'spj/test-'.$i.'.pdf', 'file_type' => 'application/pdf', 'size' => 8, 'uploaded_by' => $user->id]);
        }

        return [$user, $opd, $sp2d];
    }

    private function submit(User $user, Opd $opd, array $extra = []): SpjExport
    {
        $response = $this->actingAs($user)->postJson(route('spj-compile.generate'), array_merge(['opd_id' => $opd->id, 'all' => true, 'mode' => 'original', 'compression' => 'fast'], $extra))->assertStatus(202);
        $export = SpjExport::findOrFail($response->json('id'));
        $this->directories[] = $export->directory();

        return $export;
    }

    public function test_large_export_is_queued_and_split_without_modifying_source_bytes(): void
    {
        [$user, $opd] = $this->fixture();
        $export = $this->submit($user, $opd);
        Queue::assertPushed(BuildSpjExport::class, fn ($job) => $job->connection === 'spj_compile');
        $this->assertSame('queued', $export->status);
        app(SpjExportService::class)->build($export);
        $export->refresh();
        $this->assertSame('completed', $export->status);
        $this->assertCount(3, $export->parts);
        $this->assertEquals(24, $export->processed_bytes);
        $contents = [];
        foreach ($export->parts as $part) {
            $zip = new ZipArchive;
            $this->assertTrue($zip->open($export->directory().'/'.$part['name']) === true);
            $contents[] = $zip->getFromIndex(0);
            $this->assertSame(ZipArchive::CM_STORE, $zip->statIndex(0)['comp_method']);
            $zip->close();
        }
        $this->assertSame(['aaaaaaaa', 'bbbbbbbb', 'cccccccc'], $contents);
        $this->get(route('spj-compile.download', [$export, 0]), ['Range' => 'bytes=0-9'])->assertStatus(206)->assertHeader('Content-Length', '10');
        $this->assertFileExists($export->directory().'/bagian-001.zip');
        $other = User::factory()->create();
        $other->givePermissionTo('compile_spj');
        $this->actingAs($other)->get(route('spj-compile.download', [$export, 0]))->assertForbidden();
        $this->postJson(route('spj-compile.cancel', $export))->assertForbidden();
    }

    public function test_missing_source_fails_instead_of_silently_producing_incomplete_archive(): void
    {
        [$user, $opd] = $this->fixture();
        $export = $this->submit($user, $opd);
        Storage::disk('local')->delete('spj/test-1.pdf');
        app(SpjExportService::class)->build($export);
        $this->assertSame('failed', $export->fresh()->status);
        $this->get(route('spj-compile.download', [$export, 0]))->assertStatus(410);
        $this->assertSame([], glob($export->directory().'/*'));
    }

    public function test_duplicate_requests_and_cross_opd_selection_are_rejected_and_cancel_is_terminal(): void
    {
        [$user, $opd, $sp2d] = $this->fixture();
        $other = Opd::create(['nama_opd' => 'OPD Lain']);
        $this->actingAs($user)->postJson(route('spj-compile.generate'), ['opd_id' => $other->id, 'all' => false, 'mode' => 'original', 'compression' => 'fast', 'sp2d_ids' => [$sp2d->id]])->assertStatus(422);
        $export = $this->submit($user, $opd);
        $this->postJson(route('spj-compile.generate'), ['opd_id' => $opd->id, 'all' => true, 'mode' => 'original', 'compression' => 'fast'])->assertStatus(409);
        $this->postJson(route('spj-compile.cancel', $export))->assertOk();
        app(SpjExportService::class)->build($export);
        $this->assertSame('cancelled', $export->fresh()->status);
    }

    public function test_expired_packages_are_not_downloadable_and_are_pruned(): void
    {
        [$user, $opd] = $this->fixture();
        $export = $this->submit($user, $opd);
        app(SpjExportService::class)->build($export);
        $export->update(['expires_at' => now()->subMinute()]);
        $this->get(route('spj-compile.download', [$export, 0]))->assertStatus(410);
        $this->artisan('spj-exports:prune')->assertSuccessful();
        $this->assertSame('expired', $export->fresh()->status);
        $this->assertDirectoryDoesNotExist($export->directory());
    }

    public function test_pdf_merge_limit_and_disk_reserve_fail_safely(): void
    {
        [$user, $opd] = $this->fixture();
        config(['spj_compile.merge_max_bytes' => 1]);
        $export = $this->submit($user, $opd, ['mode' => 'merged']);
        app(SpjExportService::class)->build($export);
        $this->assertSame('failed', $export->fresh()->status);
        $this->assertStringContainsString('arsip asli', $export->fresh()->error);
        $next = $this->submit($user, $opd);
        config(['spj_compile.min_free_bytes' => PHP_INT_MAX]);
        app(SpjExportService::class)->build($next);
        $this->assertSame('failed', $next->fresh()->status);
    }

    public function test_database_queue_payload_executes_to_completion(): void
    {
        Queue::swap($this->realQueue);
        config(['queue.connections.spj_compile.after_commit' => false]);
        [$user, $opd] = $this->fixture();
        $export = $this->submit($user, $opd);
        $job = Queue::connection('spj_compile')->pop('spj-compile');
        $this->assertNotNull($job);
        $job->fire();
        $this->assertSame('completed', $export->fresh()->status);
        $this->assertDatabaseCount('jobs', 0);
    }

    public function test_small_pdf_merge_and_light_compression_produce_a_valid_pdf(): void
    {
        [$user, $opd] = $this->fixture();
        $pdf = new \FPDF;
        $pdf->AddPage();
        $pdf->SetFont('Arial', '', 12);
        $pdf->Cell(30, 10, 'SPJ test');
        foreach (range(0, 2) as $i) {
            Storage::disk('local')->put('spj/test-'.$i.'.pdf', $pdf->Output('S'));
        }
        config(['spj_compile.part_bytes' => 1024 * 1024]);
        $export = $this->submit($user, $opd, ['mode' => 'merged', 'compression' => 'balanced']);
        app(SpjExportService::class)->build($export);
        $export->refresh();
        $this->assertSame('completed', $export->status);
        $zip = new ZipArchive;
        $zip->open($export->directory().'/'.$export->parts[0]['name']);
        $this->assertSame(ZipArchive::CM_DEFLATE, $zip->statIndex(0)['comp_method']);
        $output = $export->directory().'/verify.pdf';
        file_put_contents($output, $zip->getFromIndex(0));
        $zip->close();
        $reader = new Fpdi;
        $this->assertEquals(3, $reader->setSourceFile($output));
    }

    #[Group('scale')]
    public function test_thousand_file_gib_archive_with_bounded_php_memory(): void
    {
        if (! getenv('SPJ_EXPORT_SCALE')) {
            $this->markTestSkipped('Set SPJ_EXPORT_SCALE=1 for the disk-intensive scale test.');
        }
        [$user, $opd, $sp2d] = $this->fixture();
        $spj = $sp2d->spj()->firstOrFail();
        SpjDocument::where('spj_id', $spj->id)->delete();
        Storage::disk('local')->put('spj/scale.bin', str_repeat('x', 1024 * 1024));
        for ($i = 0; $i < 1024; $i++) {
            SpjDocument::create(['spj_id' => $spj->id, 'document_type' => 'sp2d_scan', 'file_name' => 'source-'.$i.'.pdf', 'file_path' => 'spj/scale.bin', 'file_type' => 'application/pdf', 'size' => 1024 * 1024, 'uploaded_by' => $user->id]);
        }
        config(['spj_compile.part_bytes' => 64 * 1024 * 1024, 'spj_compile.part_files' => 1000]);
        $export = $this->submit($user, $opd);
        $start = microtime(true);
        app(SpjExportService::class)->build($export);
        $export->refresh();
        $this->assertSame('completed', $export->status);
        $this->assertEquals(1073741824, $export->processed_bytes);
        $this->assertCount(16, $export->parts);
        $this->assertLessThan(128 * 1024 * 1024, memory_get_peak_usage(true));
        fwrite(STDERR, sprintf("\nScale: 1024 entries / 1 GiB / 16 ZIPs, %.2fs, PHP peak %.1f MiB\n", microtime(true) - $start, memory_get_peak_usage(true) / 1048576));
    }
}
