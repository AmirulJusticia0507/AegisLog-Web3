<?php

declare(strict_types=1);

namespace App\Services;

use RuntimeException;

final class AuditEncryption
{
    public const CIPHER = 'aes-256-gcm';

    private const NONCE_LENGTH = 12;
    private const TAG_LENGTH = 16;

    private string $key;

    public function __construct(string $key)
    {
        $this->key = $this->normalizeKey($key);
    }

    private function normalizeKey(string $key): string
    {
        if (strlen($key) === 64 && preg_match('/^[a-f0-9]{64}$/i', $key)) {
            return hex2bin($key);
        }

        if (str_starts_with($key, 'base64:')) {
            return base64_decode(substr($key, 7));
        }

        return $key;
    }

    public static function fromConfig(): self
    {
        return new self((string) config('audit.encryption_key'));
    }

    /**
     * Encrypt plaintext with AES-256-GCM.
     * Payload format (base64): nonce(12) | tag(16) | ciphertext.
     */
    public function encrypt(string $plaintext): string
    {
        $nonce = random_bytes(self::NONCE_LENGTH);
        $ciphertext = openssl_encrypt($plaintext, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $nonce, $tag);

        if ($ciphertext === false) {
            throw new RuntimeException('Enkripsi AES-256-GCM gagal.');
        }

        return base64_encode($nonce . $tag . $ciphertext);
    }

    public function decrypt(string $payload): string
    {
        $decoded = base64_decode($payload, true);

        if ($decoded === false || strlen($decoded) <= self::NONCE_LENGTH + self::TAG_LENGTH) {
            throw new RuntimeException('Payload terenkripsi tidak valid.');
        }

        $nonce = substr($decoded, 0, self::NONCE_LENGTH);
        $tag = substr($decoded, self::NONCE_LENGTH, self::TAG_LENGTH);
        $ciphertext = substr($decoded, self::NONCE_LENGTH + self::TAG_LENGTH);

        $plaintext = openssl_decrypt($ciphertext, self::CIPHER, $this->key, OPENSSL_RAW_DATA, $nonce, $tag);

        if ($plaintext === false) {
            throw new RuntimeException('Dekripsi gagal atau payload diubah (tag tidak cocok).');
        }

        return $plaintext;
    }
}
