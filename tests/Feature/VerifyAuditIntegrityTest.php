<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\AuditLogTampered;
use App\Models\AuditLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class VerifyAuditIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_marks_log_verified_when_on_chain_hash_matches(): void
    {
        Http::preventStrayRequests();
        Http::fake(['*' => Http::response($this->rpcResult('matched'))]);

        $log = AuditLog::factory()->create([
            'title' => 'Good Report',
            'file_hash' => str_repeat('a', 64),
            'integrity_status' => 'pending',
        ]);

        $this->artisan('audit:verify')->assertExitCode(0);

        $log->refresh();

        $this->assertSame('verified', $log->integrity_status);
        $this->assertSame(12_345, $log->block_number);
        $this->assertSame('verified', AuditLog::where('title', 'Good Report')->first()->integrity_status);
    }

    public function test_marks_log_tampered_and_broadcasts_event_when_hash_mismatches(): void
    {
        Http::fake(['*' => Http::response($this->rpcResult('mismatch'))]);
        Event::fake([AuditLogTampered::class]);

        $log = AuditLog::factory()->create([
            'title' => 'Bad Report',
            'file_hash' => str_repeat('a', 64),
            'integrity_status' => 'pending',
        ]);

        $this->artisan('audit:verify')->assertExitCode(0);

        $log->refresh();

        $this->assertSame('tampered', $log->integrity_status);
        $this->assertArrayHasKey('tampered_at', $log->metadata);

        Event::assertDispatched(AuditLogTampered::class, fn (AuditLogTampered $event) => $event->auditLog->is($log));
    }

    public function test_leaves_log_pending_when_not_anchored(): void
    {
        Http::fake(['*' => Http::response([
            ['jsonrpc' => '2.0', 'id' => 0, 'result' => '0x'],
        ])]);

        $log = AuditLog::factory()->create([
            'integrity_status' => 'pending',
        ]);

        $this->artisan('audit:verify')->assertExitCode(0);

        $this->assertSame('pending', $log->fresh()->integrity_status);
    }

    public function test_skips_when_no_logs(): void
    {
        Http::fake();
        Http::assertNothingSent();

        $this->artisan('audit:verify')->assertExitCode(0);

        Http::assertNothingSent();
    }

    private function rpcResult(string $kind): array
    {
        $matched = str_repeat('a', 64);
        $mismatch = str_repeat('b', 64);
        $address = '3c44cdddb6a900fa2b585dd299e03d12fa4293bc';
        $block = '0000000000000000000000000000000000000000000000000000000000003039'; // 12345
        $time = '00000000000000000000000000000000000000000000000000000000699e9300'; // 1772000000

        $fileHash = $kind === 'matched' ? $matched : $mismatch;

        return [
            ['jsonrpc' => '2.0', 'id' => 0, 'result' => '0x'.$fileHash.str_repeat('0', 24).$address.$block.$time],
        ];
    }
}
