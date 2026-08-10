<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Actions\AuditLog\StoreAuditLogAction;
use App\Models\AuditLog;
use App\Services\AuditEncryption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use Web3p\EthereumUtil\Util;

class AuditLogStoreTest extends TestCase
{
    use RefreshDatabase;

    private const PRIVATE_KEY = 'ac0974bec39a17e36ba4a6b4d238ff944bacb478cbed5efcae784d7bf4f2ff80';

    private function sign(string $message): array
    {
        $util = new Util;
        $signature = $util->ecsign(self::PRIVATE_KEY, $util->hashPersonalMessage($message));
        $r = str_pad($signature->r->toString(16), 64, '0', STR_PAD_LEFT);
        $s = str_pad($signature->s->toString(16), 64, '0', STR_PAD_LEFT);
        $v = dechex($signature->recoveryParam);

        return [
            'signature' => '0x'.$r.$s.$v,
            'address' => $util->publicKeyToAddress($util->privateKeyToPublicKey(self::PRIVATE_KEY)),
        ];
    }

    private function payload(string $content = 'audit-report-content'): array
    {
        $hash = hash('sha256', $content);
        $message = str_replace('{hash}', $hash, StoreAuditLogAction::SIGN_MESSAGE_TEMPLATE);
        $signed = $this->sign($message);

        return [
            'title' => 'Pentest Report 2026',
            'file' => UploadedFile::fake()->createWithContent('report.txt', $content),
            'client_hash' => $hash,
            'signature' => $signed['signature'],
            'address' => $signed['address'],
        ];
    }

    public function test_stores_audit_log_on_valid_upload(): void
    {
        $response = $this->post('/audit-logs', $this->payload());

        $response->assertRedirect('/audit-logs');

        $this->assertDatabaseHas('audit_logs', [
            'title' => 'Pentest Report 2026',
            'integrity_status' => 'pending',
        ]);

        $log = AuditLog::first();
        $this->assertSame(64, strlen($log->file_hash));
        $this->assertSame('pending', $log->integrity_status);

        $stored = Storage::disk('local')->get($log->file_path);
        $this->assertIsString($stored);
        $this->assertNotSame('audit-report-content', $stored);
        $this->assertSame('aes-256-gcm', $log->metadata['encryption']['algorithm']);
        $this->assertSame('audit-report-content', app(AuditEncryption::class)->decrypt($stored));
    }

    public function test_rejects_on_hash_mismatch(): void
    {
        $payload = $this->payload();
        $payload['client_hash'] = str_repeat('a', 64);

        $this->post('/audit-logs', $payload)
            ->assertSessionHasErrors('client_hash');

        $this->assertDatabaseCount('audit_logs', 0);
    }

    public function test_rejects_on_invalid_signature(): void
    {
        $payload = $this->payload();
        $payload['signature'] = '0x'.str_repeat('1', 130);

        $this->post('/audit-logs', $payload)
            ->assertSessionHasErrors('signature');

        $this->assertDatabaseCount('audit_logs', 0);
    }
}
