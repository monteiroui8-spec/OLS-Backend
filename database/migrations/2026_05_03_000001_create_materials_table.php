<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('materials', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('title');
            $table->text('description')->nullable();
            $table->enum('type', ['PDF', 'Video', 'Image', 'Document', 'Audio', 'Link', 'Other']);
            $table->string('mime_type')->nullable();
            $table->bigInteger('size_bytes')->unsigned()->nullable();
            $table->string('storage_key')->nullable();   // local/S3 path
            $table->string('external_url')->nullable();  // for Link type
            $table->foreignUlid('class_group_id')->nullable()->constrained('class_groups')->nullOnDelete();
            $table->foreignUlid('course_id')->nullable()->constrained('courses')->nullOnDelete();
            $table->foreignUlid('uploaded_by_id')->references('id')->on('users')->restrictOnDelete();
            $table->boolean('is_published')->default(true);
            $table->unsignedInteger('views')->default(0);
            $table->unsignedInteger('downloads')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('class_group_id');
            $table->index('course_id');
            $table->index('uploaded_by_id');
            $table->index('is_published');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('materials');
    }
};
