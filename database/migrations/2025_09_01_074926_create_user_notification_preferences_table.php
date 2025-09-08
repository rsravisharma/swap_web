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
        Schema::create('user_notification_preferences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->boolean('chat_notifications')->default(true);
            $table->boolean('offer_notifications')->default(true);
            $table->boolean('item_notifications')->default(true);
            $table->boolean('marketing_notifications')->default(false);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('sms_notifications')->default(false);
            $table->boolean('sound_enabled')->default(true);
            $table->boolean('vibration_enabled')->default(true);
            $table->boolean('led_enabled')->default(true);
            $table->boolean('quiet_hours_enabled')->default(false);
            $table->string('quiet_hours_start')->default('22:00');
            $table->string('quiet_hours_end')->default('07:00');
            $table->boolean('security_notifications')->default(true);
            $table->timestamps();
            
            $table->unique('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_notification_preferences');
    }
};
