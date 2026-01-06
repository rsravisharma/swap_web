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
        Schema::create('user_violations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('reported_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('admin_id')->nullable()->constrained('admins')->onDelete('set null');
            $table->enum('violation_type', [
                'spam', 'fake_listing', 'inappropriate_content', 
                'fraud', 'harassment', 'fake_profile', 'other'
            ]);
            $table->enum('severity', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['pending', 'reviewed', 'action_taken', 'dismissed'])->default('pending');
            $table->text('description');
            $table->text('admin_notes')->nullable();
            $table->json('evidence')->nullable(); // Screenshots, links, etc.
            $table->enum('action_taken', [
                'none', 'warning', 'temporary_ban', 'permanent_ban', 'account_deletion'
            ])->nullable();
            $table->timestamp('action_taken_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index('violation_type');
            $table->index('severity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_violations');
    }
};
