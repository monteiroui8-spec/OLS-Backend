<?php
// ============================================================
// MIGRATION 001: users
// File: database/migrations/2026_01_01_000001_create_users_table.php
// ============================================================
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->string('password');
            $table->enum('role', ['admin', 'teacher', 'student'])->default('student');
            $table->enum('status', ['active', 'inactive', 'pending', 'suspended'])->default('pending');
            $table->string('avatar_url')->nullable();
            $table->string('country', 2)->default('AO');
            $table->enum('preferred_language', ['pt', 'en'])->default('pt');
            $table->enum('preferred_currency', ['AOA', 'EUR', 'USD'])->default('AOA');
            $table->boolean('two_factor_enabled')->default(false);
            $table->string('two_factor_secret')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('email_verify_token')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['role', 'status']);
            $table->index('email');
        });
    }

    public function down(): void { Schema::dropIfExists('users'); }
};
