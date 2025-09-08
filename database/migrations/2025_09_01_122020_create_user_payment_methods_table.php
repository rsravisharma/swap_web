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
        Schema::create('user_payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('card_holder_name');
            $table->string('card_type', 50)->nullable(); // Visa, MasterCard, etc.
            $table->string('last_four', 4)->nullable();
            $table->integer('expiry_month');
            $table->integer('expiry_year');
            $table->text('billing_address')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('token')->nullable(); // Gateway token
            $table->string('fingerprint')->nullable(); // Card fingerprint for duplicate detection
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['user_id', 'is_default']);
            $table->index(['user_id', 'is_active']);
            $table->unique(['token']); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_payment_methods');
    }
};
