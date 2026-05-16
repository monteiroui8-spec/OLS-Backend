<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name');
            $table->enum('type', ['PDF','Video','Image','Document','Audio']);
            $table->string('mime_type');
            $table->bigInteger('size_bytes')->unsigned();
            $table->string('storage_key');
            $table->string('public_url')->nullable();
            $table->foreignUlid('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignUlid('uploaded_by_id')->references('id')->on('users')->restrictOnDelete();
            $table->boolean('is_public')->default(false);
            $table->unsignedInteger('downloads')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->index('course_id');
            $table->index('uploaded_by_id');
        });
    }
    public function down(): void { Schema::dropIfExists('documents'); }
};
