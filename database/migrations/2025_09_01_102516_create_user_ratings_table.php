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
        Schema::create('user_ratings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('rater_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('rated_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('transaction_id')->nullable()->constrained('transactions')->onDelete('set null');
            $table->integer('rating')->unsigned()->check('rating >= 1 AND rating <= 5');
            $table->text('review')->nullable();
            $table->json('tags')->nullable();
            $table->enum('type', ['buyer', 'seller']); // Rating as buyer or seller
            $table->boolean('is_public')->default(true);
            $table->timestamps();

            $table->unique(['rater_id', 'rated_id', 'transaction_id']);
            $table->index(['rated_id', 'type']);
            $table->index('rating');

            // Prevent self-rating
            // $table->check('rater_id != rated_id');
        });
            DB::statement('ALTER TABLE user_ratings ADD CONSTRAINT chk_rating_range CHECK (rating >= 1 AND rating <= 5)');
            DB::statement('ALTER TABLE user_ratings ADD CONSTRAINT chk_no_self_rating CHECK (rater_id != rated_id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop constraints before dropping table
        DB::statement('ALTER TABLE user_ratings DROP CONSTRAINT IF EXISTS chk_rating_range');
        DB::statement('ALTER TABLE user_ratings DROP CONSTRAINT IF EXISTS chk_no_self_rating');
        Schema::dropIfExists('user_ratings');
    }
};
