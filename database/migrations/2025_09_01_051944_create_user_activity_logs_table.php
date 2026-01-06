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
        Schema::create('user_activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('action'); // login, logout, profile_update, etc.
            $table->string('action_type');
            $table->text('description')->nullable();
            $table->json('metadata')->nullable(); // Additional data
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('device_type')->nullable();
            $table->timestamp('performed_at');
            $table->timestamps();
            
            $table->index(['user_id', 'action']);
            $table->index(['user_id', 'created_at']);
            $table->index('action');
            $table->index('action_type');
            $table->index('performed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_activity_logs');
    }
};
