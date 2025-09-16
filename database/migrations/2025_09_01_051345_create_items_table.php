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
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('category');
            $table->decimal('price', 10, 2);
            $table->enum('condition', ['new', 'like_new', 'good', 'fair', 'poor']);
            $table->enum('status', ['active', 'sold', 'archived'])->default('active');
            $table->string('location')->nullable();
            $table->enum('contact_method', ['chat', 'phone', 'email'])->default('chat');
            $table->json('tags')->nullable();
            $table->boolean('is_sold')->default(false);
            $table->boolean('is_archived')->default(false);
            $table->boolean('is_promoted')->default(false);
            $table->string('promotion_type')->nullable();
            $table->timestamp('promoted_until')->nullable();
            $table->timestamp('sold_at')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['category', 'status']);
            $table->index('is_promoted');
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};
