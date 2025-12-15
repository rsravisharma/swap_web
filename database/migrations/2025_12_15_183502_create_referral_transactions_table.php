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
        Schema::create('referral_transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('referrer_id');
            $table->unsignedBigInteger('referred_user_id');
            $table->string('referral_code', 10);
            $table->integer('coins_awarded')->default(5);
            $table->timestamp('awarded_at')->useCurrent();
            $table->timestamps();

            $table->foreign('referrer_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('referred_user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->index(['referrer_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('referral_transactions');
    }
};
