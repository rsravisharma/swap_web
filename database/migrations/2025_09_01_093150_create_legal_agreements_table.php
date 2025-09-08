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
        Schema::create('legal_agreements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', [
                'privacy_policy', 
                'terms_and_conditions', 
                'refund_policy', 
                'cancellation_policy',
                'cookie_policy'
            ]);
            $table->timestamp('accepted_at');
            $table->string('version')->nullable();
            $table->string('signature')->nullable();
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->unique(['user_id', 'document_type']);
            $table->index(['document_type', 'accepted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('legal_agreements');
    }
};
