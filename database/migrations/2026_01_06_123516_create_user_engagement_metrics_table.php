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
        Schema::create('user_engagement_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->integer('dau_score')->default(0); // Daily Active User score
            $table->integer('engagement_score')->default(0); // Overall engagement
            $table->integer('quality_score')->default(0); // Quality of listings/interactions
            $table->integer('response_time_avg')->nullable(); // Average response time in minutes
            $table->decimal('completion_rate', 5, 2)->default(0); // Transaction completion rate
            $table->integer('reported_count')->default(0); // Times reported
            $table->integer('reports_made')->default(0); // Reports made by user
            $table->boolean('is_suspicious')->default(false);
            $table->timestamps();
            
            $table->unique(['user_id', 'date']);
            $table->index(['date', 'engagement_score']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_engagement_metrics');
    }
};
