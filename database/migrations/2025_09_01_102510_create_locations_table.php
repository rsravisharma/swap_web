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

            $table->string('geocoding_source')->nullable();
            $table->decimal('geocoding_confidence', 3, 2)->nullable();
            $table->timestamp('geocoded_at')->nullable();
            $table->string('osm_id')->nullable();
            $table->string('osm_type')->nullable();
            $table->string('place_type')->nullable();
            $table->decimal('osm_importance', 8, 6)->nullable();

            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->foreignId('city_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('country_id')->nullable()->constrained()->cascadeOnDelete();

            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->foreignId('university_id')->nullable()->constrained()->cascadeOnDelete();

            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_popular')->default(false);
            $table->boolean('is_safe_meetup')->default(false);
            $table->integer('popularity_score')->default(0);
            $table->foreignId('created_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['latitude', 'longitude']);
            $table->index(['geocoding_source', 'geocoding_confidence']);
            $table->index(['geocoding_source', 'geocoded_at']);
            $table->index(['osm_type', 'osm_id']);
            $table->index('place_type');
            $table->index(['type', 'is_active']);
            $table->index('is_popular');
            $table->index('is_safe_meetup');
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
