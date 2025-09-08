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
        Schema::create('user_followers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('follower_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('following_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['follower_id', 'following_id']);
            $table->index('follower_id');
            $table->index('following_id');

            // Prevent self-following
            // $table->check('follower_id != following_id');
        });
        DB::statement('ALTER TABLE user_followers ADD CONSTRAINT chk_no_self_follow CHECK (follower_id != following_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE user_followers DROP CONSTRAINT IF EXISTS chk_no_self_follow');
        Schema::dropIfExists('user_followers');
    }
};
