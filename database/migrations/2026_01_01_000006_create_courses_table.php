<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('slug')->unique();
            $table->string('title_pt');
            $table->string('title_en');
            $table->text('description_pt');
            $table->text('description_en');
            $table->string('level');
            $table->enum('service_type', [
                'group_daily_communication','individual','kanuca',
                'oil_gas','banking_finance','corporate'
            ]);
            $table->string('duration')->nullable();
            $table->string('image_url')->nullable();
            $table->string('flyer_url')->nullable();
            $table->decimal('price_aoa', 12, 2)->default(0);
            $table->decimal('price_eur', 10, 2)->default(0);
            $table->decimal('price_usd', 10, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->jsonb('tags')->default('[]');
            $table->timestamps();
            $table->softDeletes();
            $table->index(['service_type', 'is_active']);
        });
    }
    public function down(): void { Schema::dropIfExists('courses'); }
};
