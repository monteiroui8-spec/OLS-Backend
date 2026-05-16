<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('class_schedules', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('class_group_id')->constrained('class_groups')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->string('start_time', 5);
            $table->string('end_time', 5);
            $table->string('room')->nullable();
            $table->boolean('is_recurring')->default(true);
            $table->timestamps();
            $table->index('class_group_id');
        });
    }
    public function down(): void { Schema::dropIfExists('class_schedules'); }
};
