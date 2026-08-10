<?php

declare(strict_types=1);

namespace App\Events;

use App\Models\AuditLog;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuditLogTampered implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public readonly AuditLog $auditLog)
    {
        //
    }

    public function broadcastOn(): array
    {
        return [new Channel('audit.tamper')];
    }

    public function broadcastAs(): string
    {
        return 'audit-log.tampered';
    }

    public function broadcastWith(): array
    {
        return [
            'audit_log' => [
                'id' => $this->auditLog->id,
                'title' => $this->auditLog->title,
                'file_hash' => $this->auditLog->file_hash,
                'integrity_status' => $this->auditLog->integrity_status,
            ],
        ];
    }
}
