<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('lockbox_group_user', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('group_id')->constrained('lockbox_groups')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();

            // Group key re-wrapped for this user using their user key.
            // Allows decrypting the group's DEKs after unlocking their own key.
            $table->text('wrapped_group_key_for_user');

            $table->timestamps();
            $table->unique(['group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lockbox_group_user');
    }
};
