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
        Schema::create('meetups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('buyer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
            $table->foreignId('offer_id')->nullable()->constrained('offers')->onDelete('set null');

            $table->decimal('agreed_price', 10, 2);
            $table->decimal('original_price', 10, 2);

            $table->string('meetup_location', 500);
            $table->enum('meetup_location_type', ['public', 'campus', 'doorstep']);
            $table->json('meetup_location_details')->nullable();

            $table->dateTime('preferred_meetup_time');
            $table->dateTime('alternative_meetup_time')->nullable();

            $table->enum('payment_method', ['cash', 'upi', 'card']);
            $table->text('buyer_notes')->nullable();

            $table->boolean('acknowledged_safety')->default(false);

            $table->enum('status', ['pending', 'confirmed', 'completed', 'cancelled'])->default('pending');
            $table->boolean('buyer_confirmed')->default(false);
            $table->boolean('seller_confirmed')->default(false);
            $table->timestamp('buyer_confirmed_at')->nullable();
            $table->timestamp('seller_confirmed_at')->nullable();

            $table->dateTime('completed_at')->nullable();
            $table->dateTime('cancelled_at')->nullable();
            $table->text('cancellation_reason')->nullable();

            $table->timestamps();

            $table->index(['buyer_id', 'status']);
            $table->index(['seller_id', 'status']);
            $table->index('preferred_meetup_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meetups');
    }
};
