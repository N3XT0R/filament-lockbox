<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('lockbox_groups', static function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            // Symmetric group key, encrypted with APP_KEY.
            // This key will be re-wrapped per user in the pivot table.
            $table->text('encrypted_group_key');
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lockbox_groups');
    }
};
