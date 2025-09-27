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
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('chat_sessions')->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('reply_to_id')->nullable()->constrained('chat_messages')->nullOnDelete();
            $table->text('message');
            $table->enum('message_type', ['text', 'image', 'file', 'offer', 'location'])->default('text');
            $table->json('metadata')->nullable();
            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent');
            $table->timestamp('read_at')->nullable();
            $table->boolean('is_edited')->default(false);
            $table->boolean('is_deleted')->default(false);
            $table->timestamp('edited_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamps();
            
            $table->index(['session_id', 'created_at']);
            $table->index(['sender_id', 'status']);
            $table->index(['session_id', 'sender_id']);
            $table->index('message_type');
            $table->index(['session_id', 'created_at', 'status'], 'chat_messages_sync_index');
            $table->index(['created_at', 'is_deleted'], 'chat_messages_cleanup_index');
            $table->index(['session_id', 'sender_id', 'status'], 'chat_messages_user_status_index');
            $table->fullText('message'); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
