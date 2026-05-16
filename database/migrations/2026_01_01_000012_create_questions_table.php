<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->text('text');
            $table->enum('type', ['multiple_choice','true_false','open_text']);
            $table->jsonb('options')->default('[]');
            $table->unsignedTinyInteger('correct')->default(0);
            $table->unsignedTinyInteger('points')->default(10);
            $table->unsignedSmallInteger('order')->default(1);
            $table->timestamps();
            $table->index('exam_id');
        });
    }
    public function down(): void { Schema::dropIfExists('questions'); }
};
