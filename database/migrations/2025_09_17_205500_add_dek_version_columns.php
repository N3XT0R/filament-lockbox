<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('lockbox', static function (Blueprint $table): void {
            $table->unsignedInteger('dek_version')->default(1)->after('encrypted_dek');
        });

        Schema::table('lockbox_grants', static function (Blueprint $table): void {
            $table->unsignedInteger('dek_version')->default(1)->after('wrapped_dek');
            $table->index(['lockbox_id', 'grantee_type', 'grantee_id'], 'lockbox_grants_lockbox_grantee_index');
        });

        Schema::table('lockbox_group_user', static function (Blueprint $table): void {
            $table->unique(['group_id', 'user_id'], 'lockbox_group_user_group_id_user_id_unique');
        });
    }

    public function down(): void
    {
        Schema::table('lockbox_grants', static function (Blueprint $table): void {
            $table->dropIndex('lockbox_grants_lockbox_grantee_index');
            $table->dropColumn('dek_version');
        });

        Schema::table('lockbox_group_user', static function (Blueprint $table): void {
            $table->dropUnique('lockbox_group_user_group_id_user_id_unique');
        });

        Schema::table('lockbox', static function (Blueprint $table): void {
            $table->dropColumn('dek_version');
        });
    }
};
