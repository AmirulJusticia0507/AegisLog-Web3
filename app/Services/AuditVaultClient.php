<?php

declare(strict_types=1);

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use Web3p\EthereumUtil\Util;

/**
 * Minimal JSON-RPC client for the AuditVault smart contract.
 *
 * Encodes `getAnchor(bytes32)` calls and batch-sends them as JSON-RPC
 * batch requests so the cron worker can verify hundreds of hashes in a
 * single HTTP round-trip.
 */
class AuditVaultClient
{
    private const FUNCTION_SELECTOR_GET_ANCHOR = '7feb51d9';

    public function __construct(
        private readonly Util $ethereumUtil,
        private readonly string $rpcUrl,
        private readonly string $contractAddress,
    ) {}

    /**
     * Fetch the on-chain anchor for a batch of audit IDs.
     *
     * @param  array<int, string>  $auditIds  UUIDs of audit logs
     * @return array<string, array{file_hash: string, anchored_by: string, block_number: int, anchored_at: int}>
     *                                                                                                           Keyed by the audit ID (UUID). Missing/unanchored IDs are omitted.
     *
     * @throws ConnectionException|\Throwable
     */
    public function getAnchors(array $auditIds): array
    {
        if ($auditIds === []) {
            return [];
        }

        $requests = [];

        foreach (array_values($auditIds) as $index => $auditId) {
            $requests[] = [
                'jsonrpc' => '2.0',
                'id' => $index,
                'method' => 'eth_call',
                'params' => [
                    ['to' => $this->contractAddress, 'data' => $this->encodeGetAnchor($auditId)],
                    'latest',
                ],
            ];
        }

        $response = Http::connectTimeout(10)
            ->timeout(30)
            ->acceptJson()
            ->post($this->rpcUrl, $requests);

        if ($response->failed()) {
            throw new ConnectionException('AuditVault RPC request failed with status '.$response->status());
        }

        $anchors = [];

        foreach ($response->json() as $item) {
            if (! isset($item['result']) || $item['result'] === '0x') {
                continue;
            }

            $auditId = $auditIds[$item['id']] ?? null;

            if ($auditId === null) {
                continue;
            }

            $anchors[$auditId] = $this->decodeAnchor($item['result']);
        }

        return $anchors;
    }

    private function encodeGetAnchor(string $auditId): string
    {
        $bytes32 = $this->auditIdToBytes32($auditId);

        return '0x'.self::FUNCTION_SELECTOR_GET_ANCHOR.$bytes32;
    }

    /**
     * Convert a UUID into the 32-byte (64 hex) on-chain audit ID.
     */
    private function auditIdToBytes32(string $auditId): string
    {
        $hex = preg_replace('/[^a-fA-F0-9]/', '', $auditId);

        if (strlen($hex) > 64) {
            throw new InvalidArgumentException("Audit ID [{$auditId}] cannot fit into bytes32.");
        }

        return str_pad($hex, 64, '0', STR_PAD_LEFT);
    }

    /**
     * Decode `(bytes32 fileHash, address anchoredBy, uint256 blockNumber, uint256 anchoredAt)`.
     */
    private function decodeAnchor(string $data): array
    {
        $hex = substr(trim($data, " \t\n\r"), 2);

        $word = static fn (int $offset): string => substr($hex, $offset * 64, 64);

        $fileHash = strtolower($word(0));
        $anchoredBy = '0x'.substr($word(1), 24);
        $blockNumber = (int) hexdec($word(2));
        $anchoredAt = (int) hexdec($word(3));

        return [
            'file_hash' => $fileHash,
            'anchored_by' => $anchoredBy,
            'block_number' => $blockNumber,
            'anchored_at' => $anchoredAt,
        ];
    }
}
