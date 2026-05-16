<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('student_profiles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('student_code')->unique();
            $table->enum('current_level', ['A1', 'A2', 'B1', 'B2', 'C1'])->nullable();
            $table->date('enrollment_date')->useCurrent();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->unique('user_id');
        });
    }
    public function down(): void { Schema::dropIfExists('student_profiles'); }
};
