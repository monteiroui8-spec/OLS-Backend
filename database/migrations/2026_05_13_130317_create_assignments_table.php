<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->foreignUlid('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignUlid('class_group_id')->nullable()->constrained('class_groups')->nullOnDelete();
            $table->foreignUlid('teacher_id')->constrained('teacher_profiles')->onDelete('cascade');
            $table->timestamp('due_date')->nullable();
            $table->integer('points')->default(100);
            $table->boolean('is_published')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
