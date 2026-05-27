<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->string('student_id')->nullable()->after('id');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('approved')->after('is_active');
            // existing admin-created testimonials keep is_active as approval marker
        });
    }

    public function down(): void
    {
        Schema::table('testimonials', function (Blueprint $table) {
            $table->dropColumn(['student_id', 'status']);
        });
    }
};
