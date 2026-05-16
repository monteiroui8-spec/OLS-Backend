<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('grades', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('student_id')->references('id')->on('student_profiles')->restrictOnDelete();
            $table->foreignUlid('teacher_id')->nullable()->references('id')->on('teacher_profiles')->nullOnDelete();
            $table->string('title');
            $table->string('type')->default('Exam');
            $table->foreignUlid('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignUlid('class_group_id')->nullable()->constrained('class_groups')->nullOnDelete();
            $table->decimal('grade', 5, 2);
            $table->decimal('max_grade', 5, 2)->default(100);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index('student_id');
            $table->index('course_id');
        });
    }
    public function down(): void { Schema::dropIfExists('grades'); }
};
