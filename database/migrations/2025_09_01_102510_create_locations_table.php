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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['current', 'campus', 'custom', 'online', 'shipping']);

            // General info
            $table->string('name')->nullable(); // e.g. "IIT Delhi", "User’s Custom Location"
            $table->string('address')->nullable();
            $table->foreignId('city_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->cascadeOnDelete();

            // Coordinates
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            // Special links
            $table->foreignId('university_id')->nullable()->constrained()->cascadeOnDelete();

            // Meta
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_safe_meetup')->default(false);
            $table->integer('popularity_score')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            // Indexes
            $table->index(['latitude', 'longitude']);
            $table->index(['type', 'is_active']);
            $table->index('is_popular');
            $table->index('is_safe_meetup');

            $table->point('coordinates')->nullable()->spatialIndex(); 
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
