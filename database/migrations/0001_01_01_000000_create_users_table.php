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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            // Phone authentication
            $table->string('phone', 20)->nullable();
            $table->timestamp('phone_verified_at')->nullable();

            // Social login fields
            $table->string('google_id')->nullable()->unique();
            $table->string('facebook_id')->nullable()->unique();

            // Profile information
            $table->string('profile_image')->nullable();
            $table->text('bio')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            // Academic information
            $table->string('university', 255)->nullable();
            $table->string('course', 255)->nullable();
            $table->string('semester', 50)->nullable();
            $table->string('student_id')->nullable();
            $table->boolean('student_verified')->default(false);
            $table->string('student_id_document')->nullable(); // Path to uploaded student ID

            // Location information
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code', 10)->nullable();

            // App-specific fields
            $table->string('fcm_token')->nullable();
            $table->string('device_id')->nullable();
            $table->enum('preferred_language', ['en', 'hi', 'es', 'fr'])->default('en');


            // Add to users migration
            $table->enum('device_type', ['android', 'ios'])->nullable();
            $table->timestamp('last_token_update')->nullable();

            // Account settings
            $table->boolean('is_active')->default(true);
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('email_notifications')->default(true);
            $table->boolean('push_notifications')->default(true);

            // Safety and moderation
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('blocked_at')->nullable();
            $table->string('blocked_reason')->nullable();

            // Seller/Buyer metrics
            $table->decimal('total_earnings', 10, 2)->default(0.00);
            $table->decimal('total_spent', 10, 2)->default(0.00);
            $table->integer('items_sold')->default(0);
            $table->integer('items_bought')->default(0);
            $table->decimal('seller_rating', 3, 2)->default(0.00);
            $table->integer('total_reviews')->default(0);

            $table->integer('total_listings')->default(0); // Total items listed
            $table->integer('active_listings')->default(0); // Currently active listings
            $table->integer('followers_count')->default(0);
            $table->integer('following_count')->default(0);
            $table->timestamp('stats_last_updated')->nullable();

            // Timestamps
            $table->timestamp('last_active_at')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->index('fcm_token');
            $table->index('email');
            $table->index('phone');
            $table->index('email_verified_at');
            $table->index(['email', 'email_verified_at']);
            // Indexes for performance
            $table->index(['email', 'phone']);
            $table->index(['university', 'course']);
            $table->index(['is_active', 'student_verified']);
            $table->index('last_active_at');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
