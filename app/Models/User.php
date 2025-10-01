<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'profile_image',
        'bio',
        'date_of_birth',
        'gender',
        'university',
        'course',
        'semester',
        'is_student',
        'student_id',
        'student_verified',
        'student_id_document',
        'city',
        'state',
        'country',
        'postal_code',
        'google_id',
        'facebook_id',
        'fcm_token',
        'device_id',
        'device_type',
        'last_token_update',
        'preferred_language',
        'is_active',
        'notifications_enabled',
        'email_notifications',
        'push_notifications',
        'phone_verified_at',
        'email_verified_at',
        'last_active_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_id',
        'facebook_id',
        'fcm_token',
        'device_id',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'student_verified' => 'boolean',
        'is_active' => 'boolean',
        'notifications_enabled' => 'boolean',
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'is_blocked' => 'boolean',
        'blocked_at' => 'datetime',
        'last_active_at' => 'datetime',
        'total_earnings' => 'decimal:2',
        'total_spent' => 'decimal:2',
        'seller_rating' => 'decimal:2',
        'last_token_update' => 'datetime',
    ];

    protected $appends = [
        'full_profile_image_url',
        'is_phone_verified',
        'is_email_verified',
        'profile_completion_percentage',
    ];

    // Relationships
    public function addresses()
    {
        return $this->hasMany(UserAddress::class);
    }

    public function preferences()
    {
        return $this->hasMany(UserPreference::class);
    }

    public function followers()
    {
        return $this->belongsToMany(User::class, 'user_followers', 'following_id', 'follower_id')
            ->withTimestamps();
    }

    public function following()
    {
        return $this->belongsToMany(User::class, 'user_followers', 'follower_id', 'following_id')
            ->withTimestamps();
    }

    public function givenRatings()
    {
        return $this->hasMany(UserRating::class, 'rater_id');
    }

    public function receivedRatings()
    {
        return $this->hasMany(UserRating::class, 'rated_id');
    }

    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'blocked_users', 'blocker_id', 'blocked_id')
            ->withTimestamps();
    }

    public function blockedBy()
    {
        return $this->belongsToMany(User::class, 'blocked_users', 'blocked_id', 'blocker_id')
            ->withTimestamps();
    }

    // Accessors
    public function fullProfileImageUrl(): Attribute
    {
        return Attribute::make(
            get: fn() => $this->profile_image
                ? (filter_var($this->profile_image, FILTER_VALIDATE_URL)
                    ? $this->profile_image
                    : asset('storage/' . $this->profile_image))
                : null
        );
    }

    public function isPhoneVerified(): Attribute
    {
        return Attribute::make(
            get: fn() => !is_null($this->phone_verified_at)
        );
    }

    public function isEmailVerified(): Attribute
    {
        return Attribute::make(
            get: fn() => !is_null($this->email_verified_at)
        );
    }

    public function emailVerificationOtps()
    {
        return $this->hasMany(EmailVerificationCode::class);
    }

    public function profileCompletionPercentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                $fields = [
                    'name',
                    'email',
                    'phone',
                    'profile_image',
                    'bio',
                    'university',
                    'course',
                    'semester',
                    'city',
                    'state'
                ];

                $completed = 0;
                foreach ($fields as $field) {
                    if (!empty($this->$field)) {
                        $completed++;
                    }
                }

                return round(($completed / count($fields)) * 100);
            }
        );
    }

    // Helper methods
    public function isFollowing(User $user): bool
    {
        return $this->following()->where('following_id', $user->id)->exists();
    }

    public function hasBlocked(User $user): bool
    {
        return $this->blockedUsers()->where('blocked_id', $user->id)->exists();
    }

    public function isBlockedBy(User $user): bool
    {
        return $this->blockedBy()->where('blocker_id', $user->id)->exists();
    }

    public function updateLastActive(): void
    {
        $this->update(['last_active_at' => now()]);
    }

    public function getAverageRating(string $type = null): float
    {
        $query = $this->receivedRatings();

        if ($type) {
            $query->where('type', $type);
        }

        return $query->avg('rating') ?: 0.0;
    }

    public function getTotalRatings(string $type = null): int
    {
        $query = $this->receivedRatings();

        if ($type) {
            $query->where('type', $type);
        }

        return $query->count();
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeVerified($query)
    {
        return $query->whereNotNull('email_verified_at');
    }

    public function scopeStudentVerified($query)
    {
        return $query->where('student_verified', true);
    }

    public function scopeByUniversity($query, $university)
    {
        return $query->where('university', 'LIKE', "%{$university}%");
    }

    public function scopeByCourse($query, $course)
    {
        return $query->where('course', 'LIKE', "%{$course}%");
    }

    public function notificationSettings()
    {
        return $this->hasOne(NotificationSetting::class);
    }

    public function sellerRatings()
    {
        return $this->hasMany(UserRating::class, 'rated_id')->byType('seller');
    }

    public function buyerRatings()
    {
        return $this->hasMany(UserRating::class, 'rated_id')->byType('buyer');
    }

    public function publicRatings()
    {
        return $this->hasMany(UserRating::class, 'rated_id')->public();
    }

    public function getAverageSellerRatingAttribute()
    {
        return UserRating::getAverageRating($this->id, 'seller');
    }

    public function getAverageBuyerRatingAttribute()
    {
        return UserRating::getAverageRating($this->id, 'buyer');
    }

    public function getTotalSellerReviewsAttribute()
    {
        return UserRating::getTotalRatings($this->id, 'seller');
    }

    public function getTotalBuyerReviewsAttribute()
    {
        return UserRating::getTotalRatings($this->id, 'buyer');
    }

    public function notificationPreferences()
    {
        return $this->hasOne(UserNotificationPreference::class);
    }

    public function notifications()
    {
        return $this->hasMany(UserNotification::class);
    }

    public function hasValidFCMToken(): bool
    {
        return !empty($this->fcm_token) && $this->last_token_update &&
            $this->last_token_update->gt(now()->subDays(60)); // Token not older than 60 days
    }

    public function updateFCMToken(string $token, string $deviceType = null): void
    {
        $this->update([
            'fcm_token' => $token,
            'device_type' => $deviceType ?: $this->device_type,
            'last_token_update' => now(),
        ]);
    }

    public function chatSessionsAsUserOne()
    {
        return $this->hasMany(ChatSession::class, 'user_one_id');
    }

    public function chatSessionsAsUserTwo()
    {
        return $this->hasMany(ChatSession::class, 'user_two_id');
    }

    public function chatSessions()
    {
        return $this->chatSessionsAsUserOne->merge($this->chatSessionsAsUserTwo);
    }
}
