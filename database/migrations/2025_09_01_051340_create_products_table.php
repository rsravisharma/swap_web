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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->string('category')->nullable();
            $table->string('condition')->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('status', ['active', 'sold', 'inactive'])->default('active');
            $table->string('subject')->nullable();
            $table->string('location')->nullable();
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['category', 'status']);
            $table->index('status');
            $table->fullText(['title', 'description']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
