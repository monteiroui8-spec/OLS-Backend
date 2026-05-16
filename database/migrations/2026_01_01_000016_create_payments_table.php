<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('student_id')->references('id')->on('student_profiles')->restrictOnDelete();
            $table->foreignUlid('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignUlid('enrollment_id')->nullable()->constrained('enrollments')->nullOnDelete();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->enum('currency', ['AOA','EUR','USD'])->default('AOA');
            $table->decimal('amount_aoa', 12, 2)->nullable();
            $table->date('due_date');
            $table->timestamp('paid_at')->nullable();
            $table->enum('status', ['paid','pending','overdue','cancelled','refunded'])->default('pending');
            $table->enum('method', ['credit_card','bank_transfer','mpesa','multicaixa','cash','other'])->nullable();
            $table->string('transaction_ref')->nullable();
            $table->string('receipt_url')->nullable();
            $table->string('invoice_number')->unique();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'status']);
            $table->index(['due_date', 'status']);
        });
    }
    public function down(): void { Schema::dropIfExists('payments'); }
};
