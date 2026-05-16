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
        Schema::table('enrollments', function (Blueprint $table) {
            $table->date('payment_start_date')->nullable()->after('notes');
            $table->enum('payment_frequency', ['monthly', 'quarterly', 'semiannual', 'annual'])->nullable()->after('payment_start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn(['payment_start_date', 'payment_frequency']);
        });
    }
};
