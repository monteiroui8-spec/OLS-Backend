<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fouls', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('student_id')->references('id')->on('student_profiles')->restrictOnDelete();
            $table->foreignUlid('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->string('description');
            $table->decimal('amount', 10, 2)->default(0);
            $table->enum('currency', ['AOA','EUR','USD'])->default('AOA');
            $table->enum('status', ['pending','overdue','paid'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->date('due_date')->nullable();
            $table->foreignUlid('created_by')->references('id')->on('users');
            $table->timestamps();
            $table->index(['student_id', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('fouls'); }
};
