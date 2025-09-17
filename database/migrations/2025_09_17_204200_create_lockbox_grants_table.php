<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('lockbox_grants', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lockbox_id')->constrained('lockbox')->cascadeOnDelete();

            // Recipient can be either a user or a group (polymorphic relation).
            $table->nullableMorphs('grantee');

            // DEK wrapped with the recipient's key (user key or group key).
            $table->text('wrapped_dek');

            // Optional: expiration date or future fine-grained permissions.
            $table->timestamp('expires_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lockbox_grants');
    }
};
