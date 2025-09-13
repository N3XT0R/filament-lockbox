<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            // Stores server-side part of the encryption key, encrypted with APP_KEY
            $table->text('encrypted_user_key')->nullable();

            // Stores hashed crypto password (used as partB if TOTP is not enabled)
            $table->string('crypto_password_hash')->nullable();

            // Selected user key material provider
            $table->string('lockbox_provider')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['encrypted_user_key', 'crypto_password_hash', 'lockbox_provider']);
        });
    }
};
