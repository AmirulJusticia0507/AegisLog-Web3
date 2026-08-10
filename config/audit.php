<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Audit Encryption Key
    |--------------------------------------------------------------------------
    | AES-256-GCM key used to encrypt audit report files at rest.
    | Falls back to a SHA-256 derivation of APP_KEY when AUDIT_ENCRYPTION_KEY
    | is not set (set a dedicated key in production).
    */

    'encryption_key' => env('AUDIT_ENCRYPTION_KEY', hash('sha256', (string) env('APP_KEY'))),

    /*
    |--------------------------------------------------------------------------
    | AuditVault Smart Contract
    |--------------------------------------------------------------------------
    | RPC endpoint and deployed contract address used by the scheduled worker
    | to batch-verify anchored hashes on-chain.
    */

    'vault_rpc_url' => env('AUDIT_VAULT_RPC_URL', 'http://127.0.0.1:8545'),
    'vault_contract_address' => env('AUDIT_VAULT_CONTRACT_ADDRESS', '0x0000000000000000000000000000000000000000'),

    /*
    |--------------------------------------------------------------------------
    | Verification Batch Size
    |--------------------------------------------------------------------------
    | Maximum number of audit logs to verify per cron run so each RPC request
    | stays well under the target 10k logs/minute using batched eth_calls.
    */

    'verify_batch_size' => env('AUDIT_VERIFY_BATCH_SIZE', 100),

];
