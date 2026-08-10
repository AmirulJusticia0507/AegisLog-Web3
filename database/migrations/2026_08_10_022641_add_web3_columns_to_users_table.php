<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('wallet_address', 42)->nullable()->unique()->index();
            $table->string('role', 20)->default('auditor');
            $table->string('nonce', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['wallet_address']);
            $table->dropColumn(['wallet_address', 'role', 'nonce']);
        });
    }
};
