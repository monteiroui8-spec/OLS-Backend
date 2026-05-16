<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_attempts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('exam_id')->constrained('exams')->restrictOnDelete();
            $table->foreignUlid('student_id')->references('id')->on('student_profiles')->restrictOnDelete();
            $table->enum('status', ['in_progress','completed','expired'])->default('in_progress');
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('submitted_at')->nullable();
            $table->decimal('score', 5, 2)->nullable();
            $table->boolean('passed')->nullable();
            $table->timestamps();
            $table->index('student_id');
            $table->index('exam_id');
        });
    }
    public function down(): void { Schema::dropIfExists('exam_attempts'); }
};
