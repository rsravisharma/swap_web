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
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('badge')->default('normal');
            $table->decimal('monthly_price', 8, 2)->nullable();
            $table->decimal('annual_price', 8, 2)->nullable();
            $table->integer('monthly_slots')->default(0);
            $table->boolean('allowed_pdf_uploads')->default(false);
            $table->integer('coins_monthly')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscription_plans');
    }
};
