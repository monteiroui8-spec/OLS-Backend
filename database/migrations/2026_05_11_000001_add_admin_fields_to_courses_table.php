<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->text('prerequisites')->nullable()->after('description_en');
            $table->date('start_date')->nullable()->after('duration');
            $table->unsignedSmallInteger('available_seats')->nullable()->after('start_date');
            $table->foreignUlid('responsible_teacher_id')
                ->nullable()
                ->after('available_seats')
                ->references('id')
                ->on('teacher_profiles')
                ->nullOnDelete();
            $table->longText('syllabus')->nullable()->after('responsible_teacher_id');
            $table->longText('required_material')->nullable()->after('syllabus');

            $table->enum('visibility_status', ['draft', 'published', 'scheduled', 'hidden'])
                ->default('published')
                ->after('is_active');
            $table->timestamp('published_at')->nullable()->after('visibility_status');
            $table->string('meta_description', 180)->nullable()->after('published_at');

            $table->index(['visibility_status', 'published_at']);
            $table->index('responsible_teacher_id');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropIndex(['visibility_status', 'published_at']);
            $table->dropIndex(['responsible_teacher_id']);

            $table->dropConstrainedForeignId('responsible_teacher_id');
            $table->dropColumn([
                'prerequisites',
                'start_date',
                'available_seats',
                'syllabus',
                'required_material',
                'visibility_status',
                'published_at',
                'meta_description',
            ]);
        });
    }
};
