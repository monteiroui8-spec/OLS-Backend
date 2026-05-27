<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop the existing index that references notifiable_id
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['notifiable_id', 'notifiable_type', 'read_at']);
        });

        // Truncate any bad rows (bigint 0 values stored due to previous truncation)
        DB::table('notifications')
            ->whereRaw("notifiable_id REGEXP '^[0-9]+$'")
            ->where('notifiable_id', '0')
            ->delete();

        // Change notifiable_id from unsignedBigInteger → char(26) for ULID support
        Schema::table('notifications', function (Blueprint $table) {
            $table->char('notifiable_id', 26)->change();
        });

        // Re-create the index
        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_id', 'notifiable_type', 'read_at']);
        });
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['notifiable_id', 'notifiable_type', 'read_at']);
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->unsignedBigInteger('notifiable_id')->change();
        });

        Schema::table('notifications', function (Blueprint $table) {
            $table->index(['notifiable_id', 'notifiable_type', 'read_at']);
        });
    }
};
