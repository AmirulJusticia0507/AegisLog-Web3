<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_renders_live_stats_and_chart(): void
    {
        AuditLog::factory()->create(['integrity_status' => 'verified']);
        AuditLog::factory()->create(['integrity_status' => 'tampered']);
        AuditLog::factory()->create(['integrity_status' => 'pending']);

        $response = $this->get('/');

        $response->assertOk();

        $response->assertInertia(fn ($page) => $page
            ->component('Dashboard')
            ->has('stats', fn ($stats) => $stats
                ->where('total', 3)
                ->where('anchored', 1)
                ->where('tampered', 1)
                ->where('pending', 1))
            ->has('chart', 30)
            ->has('recentLogs', 3));
    }

    public function test_dashboard_returns_zero_when_no_logs(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Dashboard')
                ->where('stats.total', 0)
                ->has('chart', 30));
    }
}
