<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('blocked_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blocker_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('blocked_id')->constrained('users')->onDelete('cascade');
            $table->text('reason')->nullable();
            $table->enum('status', ['active', 'resolved'])->default('active');
            $table->timestamp('expires_at')->nullable(); // For temporary blocks
            $table->timestamps();
            
            $table->unique(['blocker_id', 'blocked_id']);
            $table->index('blocker_id');
            $table->index('blocked_id');
            $table->index(['status', 'expires_at']);
            
            // Prevent self-blocking
            // $table->check('blocker_id != blocked_id');
        });
            DB::statement('ALTER TABLE blocked_users ADD CONSTRAINT chk_no_self_block CHECK (blocker_id != blocked_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop constraint before dropping table
        DB::statement('ALTER TABLE blocked_users DROP CONSTRAINT IF EXISTS chk_no_self_block');
        Schema::dropIfExists('blocked_users');
    }
};
