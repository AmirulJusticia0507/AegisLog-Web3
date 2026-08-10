<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\AuditEncryption;
use App\Services\AuditVaultClient;
use Illuminate\Support\ServiceProvider;
use Web3p\EthereumUtil\Util;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuditEncryption::class, fn () => AuditEncryption::fromConfig());

        $this->app->bind(AuditVaultClient::class, fn () => new AuditVaultClient(
            new Util,
            (string) config('audit.vault_rpc_url'),
            (string) config('audit.vault_contract_address'),
        ));
    }

    public function boot(): void
    {
        //
    }
}
