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
        Schema::create('user_session_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->unique();
            $table->timestamp('started_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->string('device_type')->nullable();
            $table->string('device_model')->nullable();
            $table->string('os_version')->nullable();
            $table->string('app_version')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('location')->nullable(); // City, State
            $table->integer('actions_count')->default(0);
            $table->json('pages_visited')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'started_at']);
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_session_logs');
    }
};
