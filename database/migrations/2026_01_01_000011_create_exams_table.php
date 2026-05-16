<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('title');
            $table->foreignUlid('class_group_id')->nullable()->constrained('class_groups')->nullOnDelete();
            $table->foreignUlid('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignUlid('teacher_id')->references('id')->on('teacher_profiles')->restrictOnDelete();
            $table->unsignedSmallInteger('duration')->default(60);
            $table->unsignedTinyInteger('max_attempts')->default(1);
            $table->enum('status', ['draft','published','closed'])->default('draft');
            $table->timestamp('due_date')->nullable();
            $table->unsignedSmallInteger('total_points')->default(100);
            $table->unsignedTinyInteger('pass_score')->default(50);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['class_group_id', 'status']);
            $table->index('teacher_id');
        });
    }
    public function down(): void { Schema::dropIfExists('exams'); }
};
