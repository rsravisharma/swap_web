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
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained('user_payment_methods')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('USD');
            $table->enum('status', ['pending', 'completed', 'failed', 'cancelled', 'refunded', 'partially_refunded'])->default('pending');
            $table->string('gateway_transaction_id')->nullable(); // Transaction ID from payment gateway
            $table->json('gateway_response')->nullable(); // Full gateway response
            $table->text('description')->nullable();
            $table->decimal('refund_amount', 10, 2)->default(0);
            $table->text('refund_reason')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->string('failure_reason')->nullable(); // Reason for failed payments
            $table->timestamps();
            
            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index('gateway_transaction_id');
            $table->index('order_id');
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
