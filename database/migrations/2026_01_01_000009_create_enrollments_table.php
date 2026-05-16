<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('student_id')->references('id')->on('student_profiles')->restrictOnDelete();
            $table->foreignUlid('course_id')->constrained('courses')->restrictOnDelete();
            $table->foreignUlid('class_group_id')->nullable()->constrained('class_groups')->nullOnDelete();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('status', ['active','completed','suspended','cancelled'])->default('active');
            $table->unsignedTinyInteger('progress_pct')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['student_id', 'class_group_id']);
            $table->index('student_id');
            $table->index('class_group_id');
        });
    }
    public function down(): void { Schema::dropIfExists('enrollments'); }
};
