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
            $table->string('term')->unique();
            $table->integer('hits')->default(0);
            $table->timestamps();
            
            $table->index(['hits', 'updated_at']);
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
