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
        Schema::create('daily_user_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('items_viewed')->default(0);
            $table->integer('searches_made')->default(0);
            $table->integer('messages_sent')->default(0);
            $table->integer('messages_received')->default(0);
            $table->integer('offers_made')->default(0);
            $table->integer('offers_received')->default(0);
            $table->integer('items_listed')->default(0);
            $table->integer('items_sold')->default(0);
            $table->integer('items_bought')->default(0);
            $table->decimal('revenue_earned', 10, 2)->default(0);
            $table->decimal('amount_spent', 10, 2)->default(0);
            $table->integer('coins_earned')->default(0);
            $table->integer('coins_spent')->default(0);
            $table->integer('login_count')->default(0);
            $table->integer('active_minutes')->default(0);
            $table->timestamps();
            
            $table->unique(['user_id', 'date']);
            $table->index('date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_user_stats');
    }
};
