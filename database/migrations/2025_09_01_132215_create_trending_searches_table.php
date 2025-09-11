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
        Schema::create('trending_searches', function (Blueprint $table) {
            $table->id();
            $table->string('term', 255);
            $table->integer('hits')->default(1);
            $table->date('search_date')->default(now()->toDateString());
            $table->timestamps();

            // Indexes for performance
            $table->unique(['term', 'search_date']); // Prevent duplicate terms per day
            $table->index(['hits', 'search_date']);
            $table->index('search_date');
            $table->index('term');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trending_searches');
    }
};
