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
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt');
            $table->longText('content');
            $table->string('category')->nullable(); // Added
            $table->json('tags')->nullable(); // Added
            $table->string('featured_image')->nullable();
            $table->string('author_name')->nullable(); // Added
            $table->string('author_title')->nullable(); // Added
            $table->text('author_bio')->nullable(); // Added
            $table->json('author_social')->nullable(); // Added (for social links)
            $table->integer('reading_time')->default(5); // Added
            $table->boolean('published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
