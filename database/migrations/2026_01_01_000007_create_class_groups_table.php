<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('class_groups', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->foreignUlid('course_id')->constrained('courses')->restrictOnDelete();
            $table->foreignUlid('teacher_id')->references('id')->on('teacher_profiles')->restrictOnDelete();
            $table->year('year');
            $table->unsignedSmallInteger('capacity')->default(6);
            $table->string('room')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['course_id', 'year']);
            $table->index('teacher_id');
        });
    }
    public function down(): void { Schema::dropIfExists('class_groups'); }
};
