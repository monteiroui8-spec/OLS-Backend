<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('class_group_id')->constrained('class_groups')->restrictOnDelete();
            $table->foreignUlid('student_id')->references('id')->on('student_profiles')->restrictOnDelete();
            $table->foreignUlid('teacher_id')->references('id')->on('teacher_profiles')->restrictOnDelete();
            $table->date('date');
            $table->enum('status', ['present','absent','late','justified'])->default('present');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique(['class_group_id', 'student_id', 'date']);
            $table->index('student_id');
            $table->index(['class_group_id', 'date']);
        });
    }
    public function down(): void { Schema::dropIfExists('attendances'); }
};
