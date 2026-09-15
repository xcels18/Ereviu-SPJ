<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AuditTrailTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(): User
    {
        Permission::findOrCreate('view_audit_trail', 'web');
        $user = User::factory()->create();
        $user->givePermissionTo('view_audit_trail');
        $this->actingAs($user);

        return $user;
    }

    private function entry(array $attributes = []): ActivityLog
    {
        return ActivityLog::create(array_merge([
            'action' => 'login', 'module' => 'auth', 'description' => 'Aktivitas uji',
            'ip_address' => '192.0.2.10', 'created_at' => now(),
        ], $attributes));
    }

    public function test_guest_and_users_without_permission_cannot_read_audit_trail(): void
    {
        $this->get(route('audit-trail.index'))->assertRedirect(route('login'));
        Permission::findOrCreate('view_audit_trail', 'web');
        $this->actingAs(User::factory()->create())->get(route('audit-trail.index'))->assertForbidden();
    }

    public function test_summary_covers_all_filtered_results_and_pagination_retains_filters(): void
    {
        $user = $this->administrator();
        for ($i = 0; $i < 17; $i++) {
            $this->entry(['user_id' => $user->id, 'action' => 'update', 'module' => 'spj']);
        }
        $this->entry(['action' => 'login_failed']);
        $response = $this->get(route('audit-trail.index', ['module' => 'spj', 'per_page' => 15]));
        $response->assertOk()->assertViewHas('summary', fn ($s) => (int) $s->total === 17 && (int) $s->changes === 17 && (int) $s->failed === 0 && (int) $s->users === 1);
        $response->assertViewHas('logs', fn ($logs) => $logs->count() === 15 && str_contains($logs->nextPageUrl(), 'module=spj'));
    }

    public function test_date_range_ip_search_and_stable_order(): void
    {
        $this->administrator();
        $first = $this->entry();
        $first->forceFill(['created_at' => '2026-09-04 00:00:00'])->save();
        $second = $this->entry();
        $second->forceFill(['created_at' => '2026-09-04 23:59:59'])->save();
        $excluded = $this->entry();
        $excluded->forceFill(['created_at' => '2026-09-05 00:00:00'])->save();
        $this->get(route('audit-trail.index', ['search' => '192.0.2.10', 'date_from' => '2026-09-04', 'date_to' => '2026-09-04', 'sort' => 'oldest']))
            ->assertOk()->assertViewHas('logs', fn ($logs) => $logs->pluck('id')->all() === [$first->id, $second->id]);
    }

    public function test_invalid_filters_are_rejected_and_untrusted_descriptions_are_escaped(): void
    {
        $this->administrator();
        $this->from(route('audit-trail.index'))->get(route('audit-trail.index', ['date_from' => '2026-09-05', 'date_to' => '2026-09-01', 'per_page' => 10000]))
            ->assertRedirect(route('audit-trail.index'))->assertSessionHasErrors(['date_to', 'per_page']);
        $this->entry(['description' => '<script>alert("test")</script>']);
        $this->get(route('audit-trail.index'))->assertOk()->assertSee('&lt;script&gt;', false)->assertDontSee('<script>alert("test")</script>', false);
        $this->get(route('audit-trail.index', ['search' => 'tidak-ada-hasil']))->assertOk()->assertSee('Aktivitas tidak ditemukan');
    }
}
