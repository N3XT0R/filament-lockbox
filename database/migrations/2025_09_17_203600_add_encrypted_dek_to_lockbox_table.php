<?php


declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('lockbox', function (Blueprint $table): void {
            // Data Encryption Key (DEK), encrypted with the owner's user key.
            // This allows sharing the same ciphertext with multiple recipients.
            // Null for legacy entries until migration is performed.
            $table->text('encrypted_dek')->nullable()->after('value');
        });
    }

    public function down(): void
    {
        Schema::table('lockbox', function (Blueprint $table): void {
            $table->dropColumn('encrypted_dek');
        });
    }
};
