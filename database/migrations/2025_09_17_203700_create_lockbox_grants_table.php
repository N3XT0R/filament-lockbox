<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('lockbox_grants', function (Blueprint $table): void {
            $table->id();

            // The lockbox item this grant belongs to
            $table->foreignId('lockbox_id')
                ->constrained('lockbox')
                ->cascadeOnDelete();

            // Recipient: either a user or a group
            $table->nullableMorphs('grantee'); // grantee_type, grantee_id

            // DEK wrapped with the recipient's key (user key or group key)
            $table->text('wrapped_dek');

            // Optional: expiration date for time-limited access
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();

            $table->index(['lockbox_id', 'grantee_type', 'grantee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lockbox_grants');
    }
};
