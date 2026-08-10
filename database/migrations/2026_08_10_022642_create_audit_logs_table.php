<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title', 255);
            $table->text('file_path');
            $table->string('file_hash', 64)->index();
            $table->string('tx_hash', 66)->nullable()->index();
            $table->bigInteger('block_number')->nullable();
            $table->string('integrity_status', 20)->default('pending');
            $table->jsonb('metadata');
            $table->timestamp('created_at')->useCurrent();
        });

        if (DB::getDriverName() === 'pgsql') {
            DB::statement('CREATE INDEX idx_audit_logs_metadata_gin ON audit_logs USING gin (metadata);');
            DB::statement("CREATE INDEX idx_tampered_logs ON audit_logs (integrity_status) WHERE integrity_status = 'tampered';");
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS idx_audit_logs_metadata_gin;');
            DB::statement('DROP INDEX IF EXISTS idx_tampered_logs;');
        }
        Schema::dropIfExists('audit_logs');
    }
};
