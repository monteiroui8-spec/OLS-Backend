<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('teacher_profiles', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('teacher_code')->unique();
            $table->text('bio')->nullable();
            $table->jsonb('certifications')->default('[]');
            $table->jsonb('specializations')->default('[]');
            $table->date('hire_date')->useCurrent();
            $table->decimal('salary', 12, 2)->nullable();
            $table->enum('salary_currency', ['AOA', 'EUR', 'USD'])->default('AOA');
            $table->timestamps();
            $table->unique('user_id');
        });
    }
    public function down(): void { Schema::dropIfExists('teacher_profiles'); }
};
