<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('contact_form_submissions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone')->nullable();
            $table->string('subject');
            $table->text('message');
            $table->enum('language', ['pt','en'])->default('pt');
            $table->string('ip_address')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('contact_form_submissions'); }
};
