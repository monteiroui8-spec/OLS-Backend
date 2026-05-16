<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ── chat_conversations ────────────────────────────────────────────────
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->enum('type', ['direct', 'group'])->default('direct');
            $table->string('name', 150)->nullable();            // só grupos
            $table->foreignUlid('class_id')
                  ->nullable()
                  ->constrained('class_groups')
                  ->nullOnDelete();
            $table->timestamps();
        });

        // ── chat_participants ─────────────────────────────────────────────────
        Schema::create('chat_participants', function (Blueprint $table) {
            $table->foreignUlid('conversation_id')
                  ->constrained('chat_conversations')
                  ->cascadeOnDelete();
            $table->foreignUlid('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamp('joined_at')->useCurrent();

            $table->primary(['conversation_id', 'user_id']);
        });

        // ── chat_attachments ──────────────────────────────────────────────────
        Schema::create('chat_attachments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('uploader_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->string('url', 500);
            $table->string('name', 255);
            $table->string('mime_type', 100);
            $table->unsignedBigInteger('size_bytes');
            $table->unsignedInteger('duration_seconds')->nullable(); // só áudio
            $table->timestamps();
        });

        // ── chat_messages ─────────────────────────────────────────────────────
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('conversation_id')
                  ->constrained('chat_conversations')
                  ->cascadeOnDelete();
            $table->foreignUlid('sender_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->enum('type', ['text', 'audio', 'file', 'image'])->default('text');
            $table->text('body')->nullable();
            $table->foreignUlid('attachment_id')
                  ->nullable()
                  ->constrained('chat_attachments')
                  ->nullOnDelete();
            $table->timestamps();

            $table->index(['conversation_id', 'created_at']);
        });

        // ── chat_message_reads ────────────────────────────────────────────────
        Schema::create('chat_message_reads', function (Blueprint $table) {
            $table->foreignUlid('message_id')
                  ->constrained('chat_messages')
                  ->cascadeOnDelete();
            $table->foreignUlid('user_id')
                  ->constrained('users')
                  ->cascadeOnDelete();
            $table->timestamp('read_at')->useCurrent();

            $table->primary(['message_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_message_reads');
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_attachments');
        Schema::dropIfExists('chat_participants');
        Schema::dropIfExists('chat_conversations');
    }
};
