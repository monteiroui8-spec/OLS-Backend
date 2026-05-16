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
        Schema::create('assignment_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('assignment_id')->constrained('assignments')->onDelete('cascade');
            $table->foreignUlid('student_id')->constrained('student_profiles')->onDelete('cascade');
            $table->string('file_url')->nullable();
            $table->text('student_notes')->nullable();
            $table->decimal('grade', 5, 2)->nullable();
            $table->text('teacher_feedback')->nullable();
            $table->timestamp('submitted_at')->useCurrent();
            $table->enum('status', ['pending', 'graded', 'late', 'missing'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignment_submissions');
    }
};
