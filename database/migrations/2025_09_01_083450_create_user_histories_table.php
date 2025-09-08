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
        Schema::create('user_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('type', 50); // search, view, purchase, offer, etc.
            $table->string('action'); // Description of the action
            $table->string('title')->nullable(); // Title for display
            $table->text('description')->nullable(); // Detailed description
            $table->string('category', 100)->nullable(); // Category for filtering
            $table->json('details')->nullable(); // Additional details as JSON
            $table->unsignedBigInteger('related_id')->nullable(); // Related item/user ID
            $table->string('related_type', 100)->nullable(); // Related model type
            $table->timestamps();
            
            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'type']);
            $table->index(['user_id', 'category']);
            $table->index(['related_id', 'related_type']);
            $table->fullText(['title', 'description', 'action']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_histories');
    }
};
