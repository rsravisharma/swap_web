<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pdf_book_id')->nullable()->constrained('pdf_books')->onDelete('set null');
            $table->enum('order_type', ['coins','product', 'pdf_book'])->default('coins');
            $table->string('razorpay_order_id')->nullable()->unique();
            $table->string('razorpay_payment_id')->nullable();
            $table->string('razorpay_signature')->nullable();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('delivery_option_id')->nullable()->constrained()->nullOnDelete();
            $table->json('delivery_address')->nullable();
            $table->text('notes')->nullable();

            // Define columns in the order you want them
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'])
                ->default('pending');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded', 'partially_refunded'])
                ->default('pending');
            $table->timestamp('paid_at')->nullable();

            $table->decimal('total_amount', 10, 2);
            $table->timestamp('cancelled_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index('razorpay_order_id');
            $table->index('razorpay_payment_id');
            $table->index('order_type');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
