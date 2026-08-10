<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Events\AuditLogTampered;
use App\Models\AuditLog;
use App\Services\AuditVaultClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class VerifyAuditIntegrity extends Command
{
    protected $signature = 'audit:verify {--limit= : Maximum logs to check this run (defaults to config)}';

    protected $description = 'Batch-verify anchored audit hashes on-chain and flag tampered logs';

    public function handle(AuditVaultClient $vault): int
    {
        $limit = (int) ($this->option('limit') ?? config('audit.verify_batch_size', 100));

        $logs = AuditLog::query()
            ->whereIn('integrity_status', ['pending', 'verified'])
            ->latest('created_at')
            ->limit($limit)
            ->get();

        if ($logs->isEmpty()) {
            $this->info('No audit logs pending verification.');

            return self::SUCCESS;
        }

        $anchors = $vault->getAnchors($logs->pluck('id')->all());

        $verified = 0;
        $tampered = 0;
        $skipped = 0;

        foreach ($logs as $log) {
            $anchor = $anchors[$log->id] ?? null;

            if ($anchor === null) {
                $skipped++;

                continue;
            }

            if ($anchor['file_hash'] !== $log->file_hash) {
                $this->markTampered($log);
                $tampered++;

                continue;
            }

            $log->forceFill([
                'integrity_status' => 'verified',
                'tx_hash' => $log->tx_hash,
                'block_number' => $anchor['block_number'],
                'metadata' => array_merge($log->metadata ?? [], [
                    'on_chain' => [
                        'file_hash' => $anchor['file_hash'],
                        'anchored_by' => $anchor['anchored_by'],
                        'block_number' => $anchor['block_number'],
                        'anchored_at' => $anchor['anchored_at'],
                    ],
                ]),
            ])->save();

            $verified++;
        }

        $this->components->twoColumnDetail('Verified on-chain', (string) $verified);
        $this->components->twoColumnDetail('Tampered detected', (string) $tampered);
        $this->components->twoColumnDetail('Not anchored / skipped', (string) $skipped);

        return self::SUCCESS;
    }

    private function markTampered(AuditLog $log): void
    {
        $log->forceFill([
            'integrity_status' => 'tampered',
            'metadata' => array_merge($log->metadata ?? [], [
                'tampered_at' => now()->toIso8601String(),
            ]),
        ])->save();

        if (app()->environment('testing') || config('broadcasting.default') === 'log') {
            Log::warning('Audit hash mismatch detected', [
                'audit_log_id' => $log->id,
                'title' => $log->title,
            ]);
        }

        AuditLogTampered::dispatch($log);
    }
}
