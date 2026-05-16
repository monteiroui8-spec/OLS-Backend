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
        Schema::create('exam_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignUlid('exam_id')->constrained('exams')->cascadeOnDelete();
            $table->ulidMorphs('assignable'); // creates assignable_type and assignable_id
            $table->timestamps();
            
            $table->unique(['exam_id', 'assignable_id', 'assignable_type'], 'exam_assign_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('exam_assignments');
    }
};
