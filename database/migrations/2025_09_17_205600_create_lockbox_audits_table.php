<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('lockbox_audits', static function (Blueprint $table): void {
            $table->id();
            $table->foreignId('lockbox_id')->constrained('lockbox')->cascadeOnDelete();
            $table->foreignId('grant_id')->nullable()->constrained('lockbox_grants')->nullOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['lockbox_id', 'event']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lockbox_audits');
    }
};
