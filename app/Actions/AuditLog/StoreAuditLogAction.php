<?php

declare(strict_types=1);

namespace App\Actions\AuditLog;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Web3p\EthereumUtil\Util;

class StoreAuditLogAction
{
    public const SIGN_MESSAGE_TEMPLATE = 'AegisLog-Web3 anchor {hash}';

    public function __construct(private readonly Util $ethereumUtil) {}

    /**
     * Persist an uploaded audit file after verifying hash integrity
     * and the wallet signature that authorized the anchoring.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(UploadedFile $file, array $data): AuditLog
    {
        $this->assertHashMatches($file, $data['client_hash']);
        $this->assertSignatureValid($data);

        $message = str_replace('{hash}', $data['client_hash'], self::SIGN_MESSAGE_TEMPLATE);
        $path = $file->store('audit-files', 'local');

        $user = User::firstOrCreate(
            ['wallet_address' => Str::lower($data['address'])],
            [
                'name' => 'Wallet '.substr($data['address'], 0, 10),
                'password' => Hash::make(Str::random(32)),
                'nonce' => Str::random(32),
            ]
        );

        return AuditLog::create([
            'user_id' => $user->id,
            'title' => $data['title'],
            'file_path' => $path,
            'file_hash' => $data['client_hash'],
            'integrity_status' => 'pending',
            'metadata' => [
                'size' => $file->getSize(),
                'mime' => $file->getMimeType(),
                'signed_by' => Str::lower($data['address']),
                'signed_message' => $message,
                'signature' => $data['signature'],
            ],
        ]);
    }

    private function assertHashMatches(UploadedFile $file, string $clientHash): void
    {
        $serverHash = hash_file('sha256', $file->getRealPath());

        if (! hash_equals($serverHash, Str::lower($clientHash))) {
            throw ValidationException::withMessages([
                'client_hash' => 'Hash tidak cocok dengan berkas yang diunggah (hash mismatch).',
            ]);
        }
    }

    private function assertSignatureValid(array $data): void
    {
        $message = str_replace('{hash}', $data['client_hash'], self::SIGN_MESSAGE_TEMPLATE);

        $signature = substr($data['signature'], 2);
        $r = substr($signature, 0, 64);
        $s = substr($signature, 64, 64);
        $v = hexdec(substr($signature, 128, 2));

        $recovery = match (true) {
            $v >= 35 => $v - 35,
            $v >= 27 => $v - 27,
            default => $v,
        };

        try {
            $publicKey = $this->ethereumUtil->recoverPublicKey(
                $this->ethereumUtil->hashPersonalMessage($message),
                $r,
                $s,
                $recovery
            );
            $recovered = Str::lower($this->ethereumUtil->publicKeyToAddress($publicKey));
        } catch (\Throwable) {
            throw ValidationException::withMessages([
                'signature' => 'Tanda tangan wallet tidak valid untuk alamat yang diberikan.',
            ]);
        }

        if (! hash_equals($recovered, Str::lower($data['address']))) {
            throw ValidationException::withMessages([
                'signature' => 'Tanda tangan wallet tidak valid untuk alamat yang diberikan.',
            ]);
        }
    }
}
