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

];
