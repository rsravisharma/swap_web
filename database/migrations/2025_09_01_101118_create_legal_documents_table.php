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
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                'privacy_policy', 
                'terms_and_conditions', 
                'refund_policy', 
                'cancellation_policy',
                'cookie_policy'
            ]);
            $table->string('title');
            $table->longText('content');
            $table->string('version')->default('1.0');
            $table->boolean('is_active')->default(true);
            $table->timestamp('effective_date')->nullable();
            $table->timestamps();
            
            $table->index(['type', 'is_active']);
            $table->index(['type', 'version']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
