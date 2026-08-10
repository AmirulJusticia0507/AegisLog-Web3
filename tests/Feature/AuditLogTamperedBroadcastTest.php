<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Events\AuditLogTampered;
use App\Models\AuditLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AuditLogTamperedBroadcastTest extends TestCase
{
    use RefreshDatabase;

    public function test_event_broadcasts_on_public_tamper_channel(): void
    {
        Event::fake([AuditLogTampered::class]);

        $log = AuditLog::factory()->create([
            'title' => 'Tampered Report',
            'integrity_status' => 'tampered',
        ]);

        AuditLogTampered::dispatch($log);

        Event::assertDispatched(AuditLogTampered::class, function (AuditLogTampered $event) use ($log) {
            $this->assertSame($log->id, $event->auditLog->id);
            $this->assertSame('audit.tamper', $event->broadcastOn()[0]->name);
            $this->assertSame('audit-log.tampered', $event->broadcastAs());

            return true;
        });
    }

    public function test_broadcast_payload_contains_audit_log(): void
    {
        $log = AuditLog::factory()->create([
            'title' => 'Payload Report',
            'file_hash' => str_repeat('a', 64),
            'integrity_status' => 'tampered',
        ]);

        $payload = (new AuditLogTampered($log))->broadcastWith();

        $this->assertSame($log->id, $payload['audit_log']['id']);
        $this->assertSame('Payload Report', $payload['audit_log']['title']);
        $this->assertSame('tampered', $payload['audit_log']['integrity_status']);
    }

    public function test_channel_is_public(): void
    {
        $log = AuditLog::factory()->create();
        $event = new AuditLogTampered($log);

        $channels = $event->broadcastOn();

        $this->assertCount(1, $channels);
        $this->assertInstanceOf(Channel::class, $channels[0]);
    }
}
