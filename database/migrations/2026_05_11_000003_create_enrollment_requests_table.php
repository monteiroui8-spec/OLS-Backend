<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('enrollment_requests', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('protocol')->unique();

            $table->foreignUlid('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignUlid('course_id')->constrained('courses')->restrictOnDelete();
            $table->foreignUlid('class_group_id')->nullable()->constrained('class_groups')->nullOnDelete();
            $table->foreignUlid('class_schedule_id')->nullable()->constrained('class_schedules')->nullOnDelete();

            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->text('admin_notes')->nullable();

            $table->timestamps();
            $table->index(['status', 'created_at']);
            $table->index('course_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enrollment_requests');
    }
};

