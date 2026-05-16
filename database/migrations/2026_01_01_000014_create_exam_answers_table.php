<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('exam_answers', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('attempt_id')->constrained('exam_attempts')->cascadeOnDelete();
            $table->foreignUlid('question_id')->constrained('questions')->cascadeOnDelete();
            $table->integer('answer')->default(-1);
            $table->boolean('is_correct')->nullable();
            $table->timestamps();
            $table->unique(['attempt_id', 'question_id']);
        });
    }
    public function down(): void { Schema::dropIfExists('exam_answers'); }
};
