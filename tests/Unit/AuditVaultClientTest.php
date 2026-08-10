<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AuditVaultClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use Web3p\EthereumUtil\Util;

class AuditVaultClientTest extends TestCase
{
    private const CONTRACT = '0x5fbdb2315678afecb367f032d93f642f64180aa3';

    private string $auditId = '019fe9dc-45e5-720b-8f30-c1c33fde387c';

    private function client(): AuditVaultClient
    {
        return new AuditVaultClient(new Util, 'http://127.0.0.1:8545', self::CONTRACT);
    }

    public function test_sends_batched_eth_call_requests(): void
    {
        Http::fake([
            'http://127.0.0.1:8545' => Http::response([
                ['jsonrpc' => '2.0', 'id' => 0, 'result' => $this->anchorPayload()],
                ['jsonrpc' => '2.0', 'id' => 1, 'result' => '0x'],
            ]),
        ]);

        $anchors = $this->client()->getAnchors([$this->auditId, 'another-id']);

        Http::assertSent(function ($request) {
            $body = $request->data();

            $this->assertCount(2, $body);
            $this->assertSame('eth_call', $body[0]['method']);
            $this->assertSame('latest', $body[0]['params'][1]);
            $this->assertSame(self::CONTRACT, $body[0]['params'][0]['to']);
            $this->assertStringStartsWith('0x7feb51d9', $body[0]['params'][0]['data']);

            return true;
        });

        $this->assertArrayHasKey($this->auditId, $anchors);
        $this->assertArrayNotHasKey('another-id', $anchors);
    }

    public function test_decodes_anchor_payload(): void
    {
        Http::fake([
            '*' => Http::response([['jsonrpc' => '2.0', 'id' => 0, 'result' => $this->anchorPayload()]]),
        ]);

        $anchor = $this->client()->getAnchors([$this->auditId])[$this->auditId];

        $this->assertSame(str_repeat('a', 64), $anchor['file_hash']);
        $this->assertSame('0x3c44cdddb6a900fa2b585dd299e03d12fa4293bc', $anchor['anchored_by']);
        $this->assertSame(12_345, $anchor['block_number']);
        $this->assertSame(1_772_000_000, $anchor['anchored_at']);
    }

    public function test_returns_empty_when_no_ids(): void
    {
        $this->assertSame([], $this->client()->getAnchors([]));

        Http::assertNothingSent();
    }

    public function test_throws_on_failed_response(): void
    {
        Http::fake(['*' => Http::response('', 500)]);

        $this->expectException(ConnectionException::class);

        $this->client()->getAnchors([$this->auditId]);
    }

    private function anchorPayload(): string
    {
        $fileHash = str_repeat('a', 64);
        $address = '3c44cdddb6a900fa2b585dd299e03d12fa4293bc';
        $block = '0000000000000000000000000000000000000000000000000000000000003039'; // 12345
        $time = '00000000000000000000000000000000000000000000000000000000699e9300'; // 1772000000

        return '0x'.$fileHash.str_repeat('0', 24).$address.$block.$time;
    }
}
