<?php

declare(strict_types=1);

namespace App\Providers;

use App\Services\AuditEncryption;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AuditEncryption::class, fn () => AuditEncryption::fromConfig());
    }

    public function boot(): void
    {
        //
    }
}
