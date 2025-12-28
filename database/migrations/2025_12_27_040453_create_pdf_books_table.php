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
         Schema::create('pdf_books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->foreignId('seller_id')->constrained('users')->cascadeOnDelete();
            $table->string('isbn')->unique()->nullable();
            $table->text('description')->nullable();
            $table->string('author')->nullable();
            $table->string('publisher')->nullable();
            $table->year('publication_year')->nullable();
            $table->string('cover_image')->nullable();
            $table->decimal('price', 10, 2);
            $table->string('google_drive_file_id')->unique(); 
            $table->string('google_drive_shareable_link')->nullable(); 
            $table->bigInteger('file_size')->nullable(); 
            $table->boolean('is_available')->default(true);
            $table->integer('total_pages')->nullable();
            $table->string('language')->default('en');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['seller_id']);
            $table->index(['seller_id', 'is_available']);
            $table->index('is_available');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pdf_books');
    }
};
