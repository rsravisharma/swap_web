<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'referral_code',
        'referred_by',
        'name',
        'email',
        'phone',
        'password',
        'profile_image',
        'bio',
        'date_of_birth',
        'gender',
        'subscription_plan_id',
        'coins',
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
        'last_login_at',
        'login_streak_days',
        'monthly_coins_awarded',
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
        'last_login_at' => 'datetime',
        'login_streak_days' => 'integer',
        'monthly_coins_awarded' => 'boolean',
        'stats_last_updated' => 'datetime',
        'coins' => 'integer',
    ];

    protected $appends = [
        'full_profile_image_url',
        'is_phone_verified',
        'is_email_verified',
        'profile_completion_percentage',
    ];

    public function referrer()
    {
        return $this->belongsTo(User::class, 'referred_by', 'id');
    }

    // Users that were referred by this user
    public function referrals()
    {
        return $this->hasMany(User::class, 'referred_by', 'id');
    }

    public function getTotalReferralsAttribute()
    {
        return $this->referrals()->count();
    }

    /**
     * Get coins earned from referrals
     */
    public function getReferralCoinsEarnedAttribute()
    {
        return $this->referrals()->count() * 5;
    }

    /**
     * Scope to get users with referral stats
     */
    public function scopeWithReferralStats($query)
    {
        return $query->withCount('referrals')
            ->addSelect([
                'referral_coins' => User::selectRaw('COUNT(*) * 5')
                    ->whereColumn('referred_by', 'users.id')
            ]);
    }

    public static function generateUniqueReferralCode($length = 8): string
    {
        do {
            $code = strtoupper(substr(bin2hex(random_bytes($length)), 0, $length));
        } while (self::where('referral_code', $code)->exists());

        return $code;
    }

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
        return $this->belongsToMany(
            User::class,
            'user_follows',
            'followed_id',
            'follower_id'
        )->withTimestamps();
    }

    /**
     * Users that this user is following
     */
    public function following()
    {
        return $this->belongsToMany(
            User::class,
            'user_follows',
            'follower_id',
            'followed_id'
        )->withTimestamps();
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
    public function isFollowing($user): bool
    {
        $userId = $user instanceof User ? $user->id : $user;
        return $this->following()->where('followed_id', $userId)->exists();
    }

    public function isFollowedBy($userId): bool
    {
        $userId = $userId instanceof User ? $userId->id : $userId;
        return $this->followers()->where('follower_id', $userId)->exists();
    }

    public function follow(User $user): bool
    {
        if ($this->id === $user->id) {
            return false;
        }

        if ($this->isFollowing($user)) {
            return false;
        }

        DB::transaction(function () use ($user) {
            $this->following()->attach($user->id);

            // Increment follower/following counts atomically
            DB::table('users')->where('id', $this->id)->increment('following_count');
            DB::table('users')->where('id', $user->id)->increment('followers_count');
        });

        $this->refresh();
        return true;
    }

    /**
     * Unfollow a user
     */
    public function unfollow(User $user): bool
    {
        if (!$this->isFollowing($user)) {
            return false;
        }

        DB::transaction(function () use ($user) {
            $this->following()->detach($user->id);

            // Decrement counts atomically with minimum 0
            DB::table('users')
                ->where('id', $this->id)
                ->where('following_count', '>', 0)
                ->decrement('following_count');

            DB::table('users')
                ->where('id', $user->id)
                ->where('followers_count', '>', 0)
                ->decrement('followers_count');
        });

        $this->refresh();
        return true;
    }

    /**
     * Toggle follow status
     */
    public function toggleFollow(User $user): array
    {
        if ($this->isFollowing($user)) {
            $this->unfollow($user);
            return ['is_following' => false, 'message' => 'Unfollowed successfully'];
        } else {
            $this->follow($user);
            return ['is_following' => true, 'message' => 'Followed successfully'];
        }
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

    public function items()
    {
        return $this->hasMany(Item::class, 'user_id');
    }

    /**
     * Items this user has wishlisted
     */
    public function wishlistedItems()
    {
        return $this->belongsToMany(Item::class, 'wishlists', 'user_id', 'item_id')
            ->withTimestamps();
    }

    /**
     * Direct wishlist entries
     */
    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * Check if user has wishlisted an item
     */
    public function hasWishlisted(Item $item): bool
    {
        return $this->wishlistedItems()->where('item_id', $item->id)->exists();
    }

    public function getBadgeAttribute(): string
    {
        return $this->subscriptionPlan->badge ?? 'normal';
    }

    public function subscriptionPlan()
    {
        return $this->belongsTo(SubscriptionPlan::class);
    }

    public function canUploadPdf(): bool
    {
        return $this->subscriptionPlan && $this->subscriptionPlan->allowed_pdf_uploads;
    }

    public function getAvailableSlots(): int
    {
        return $this->subscriptionPlan ? $this->subscriptionPlan->monthly_slots : 0;
    }

    public function deductCoins(int $amount, string $reason = null): bool
    {
        // Use atomic decrement with WHERE condition to prevent race conditions
        $affected = DB::table('users')
            ->where('id', $this->id)
            ->where('coins', '>=', $amount)
            ->decrement('coins', $amount);

        if ($affected > 0) {
            // Refresh model to get updated coins value
            $this->refresh();

            // Log transaction if reason provided
            if ($reason) {
                $this->logCoinTransaction(-$amount, $reason);
            }

            return true;
        }

        return false;
    }

    // ✅ Also update addCoins for consistency
    public function addCoins(int $amount, string $reason = null): void
    {
        $this->increment('coins', $amount);

        if ($reason) {
            $this->logCoinTransaction($amount, $reason);
        }

        $this->refresh();
    }

    // Add helper method for logging
    protected function logCoinTransaction(int $amount, string $reason): void
    {
        DB::table('coin_transactions')->insert([
            'user_id' => $this->id,
            'amount' => $amount,
            'type' => $reason,
            'balance_after' => $this->coins,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function updateLoginStreak(): array
    {
        $lastLogin = $this->last_login_at;
        $now = now();

        if (!$lastLogin) {
            // First login
            $this->update([
                'login_streak_days' => 1,
                'last_login_at' => $now,
            ]);

            return [
                'streak' => 1,
                'is_new_streak' => true,
                'coins_awarded' => 0
            ];
        }

        // ✅ Add timezone safety
        $daysSinceLastLogin = $lastLogin->startOfDay()->diffInDays($now->startOfDay());

        if ($daysSinceLastLogin === 0) {
            // Same day login - no change
            return [
                'streak' => $this->login_streak_days,
                'is_new_streak' => false,
                'coins_awarded' => 0
            ];
        } elseif ($daysSinceLastLogin === 1) {
            // Consecutive day login - increment streak
            $newStreak = $this->login_streak_days + 1;

            $this->update([
                'login_streak_days' => $newStreak,
                'last_login_at' => $now,
            ]);

            // Award coins for streak milestones
            $coinsAwarded = $this->awardStreakCoins($newStreak);

            return [
                'streak' => $newStreak,
                'is_new_streak' => true,
                'coins_awarded' => $coinsAwarded
            ];
        } else {
            // Streak broken - reset to 1
            $this->update([
                'login_streak_days' => 1,
                'last_login_at' => $now,
            ]);

            return [
                'streak' => 1,
                'is_new_streak' => true,
                'streak_broken' => true,
                'coins_awarded' => 0,
                'previous_streak' => $this->login_streak_days,
            ];
        }
    }


    /**
     * Award coins based on streak milestones
     */
    protected function awardStreakCoins(int $streak): int
    {
        $coins = 0;
        $reason = '';

        // Award coins for streak milestones (highest priority first)
        if ($streak % 30 === 0) {
            $coins = 50;
            $reason = "login_streak_30_days";
        } elseif ($streak % 7 === 0) {
            $coins = 10;
            $reason = "login_streak_7_days";
        } elseif ($streak % 5 === 0) {
            $coins = 5;
            $reason = "login_streak_5_days";
        }

        if ($coins > 0) {
            $this->addCoins($coins, $reason);
        }

        return $coins;
    }

    /**
     * Check if eligible for monthly streak bonus
     */
    public function checkMonthlyStreakBonus(): bool
    {
        if ($this->login_streak_days >= 30 && !$this->monthly_coins_awarded) {
            $this->addCoins(100, 'monthly_streak_bonus');
            $this->update(['monthly_coins_awarded' => true]);
            return true;
        }

        return false;
    }

    /**
     * Reset monthly bonus flag (run via scheduler)
     */
    public function resetMonthlyBonus(): void
    {
        $this->update(['monthly_coins_awarded' => false]);
    }

    public function coinTransactions()
    {
        return $this->hasMany(CoinTransaction::class)->orderBy('created_at', 'desc');
    }

    /**
     * Get recent coin transactions
     */
    public function getRecentCoinTransactions(int $limit = 10)
    {
        return $this->coinTransactions()->limit($limit)->get();
    }

    /**
     * Get coin balance with pending transactions
     */
    public function getCoinBalanceWithPending(): int
    {
        return $this->coins;
    }

    public function incrementListingCount(): void
    {
        DB::table('users')
            ->where('id', $this->id)
            ->update([
                'total_listings' => DB::raw('total_listings + 1'),
                'active_listings' => DB::raw('active_listings + 1'),
            ]);

        $this->refresh();
    }

    /**
     * Decrement active listings (when sold/archived)
     */
    public function decrementActiveListings(): void
    {
        DB::table('users')
            ->where('id', $this->id)
            ->where('active_listings', '>', 0)
            ->decrement('active_listings');

        $this->refresh();
    }

    /**
     * Increment items sold and update earnings
     */
    public function recordSale(float $amount): void
    {
        DB::table('users')
            ->where('id', $this->id)
            ->update([
                'items_sold' => DB::raw('items_sold + 1'),
                'total_earnings' => DB::raw("total_earnings + {$amount}"),
                'stats_last_updated' => now(),
            ]);

        $this->refresh();
    }

    /**
     * Record purchase
     */
    public function recordPurchase(float $amount): void
    {
        DB::table('users')
            ->where('id', $this->id)
            ->update([
                'items_bought' => DB::raw('items_bought + 1'),
                'total_spent' => DB::raw("total_spent + {$amount}"),
                'stats_last_updated' => now(),
            ]);

        $this->refresh();
    }

    public function scopeHasCoins($query, int $minCoins)
    {
        return $query->where('coins', '>=', $minCoins);
    }

    /**
     * Scope for users with active streak
     */
    public function scopeWithActiveStreak($query, int $minDays = 7)
    {
        return $query->where('login_streak_days', '>=', $minDays);
    }

    /**
     * Get top sellers
     */
    public function scopeTopSellers($query, int $limit = 10)
    {
        return $query->orderBy('items_sold', 'desc')
            ->orderBy('seller_rating', 'desc')
            ->limit($limit);
    }

    public function canAfford(int $amount): bool
    {
        return $this->coins >= $amount;
    }

    /**
     * Get coins needed for an amount
     */
    public function coinsNeeded(int $amount): int
    {
        return max(0, $amount - $this->coins);
    }

    public function subscriptions()
    {
        return $this->hasMany(UserSubscription::class)->orderBy('created_at', 'desc');
    }

    public function activeSubscription()
    {
        return $this->hasOne(UserSubscription::class)
            ->where('status', 'active')
            ->where('expires_at', '>', now());
    }

    public function pdfBooksForSale()
    {
        return $this->hasMany(PdfBook::class, 'seller_id');
    }

    public function pdfBookSales()
    {
        return $this->hasMany(PdfBookPurchase::class, 'seller_id');
    }

    public function purchasedPdfBooks()
    {
        return $this->hasMany(PdfBookPurchase::class, 'user_id');
    }

    public function sessionLogs()
    {
        return $this->hasMany(UserSessionLog::class);
    }

    public function engagementMetrics()
    {
        return $this->hasMany(UserEngagementMetric::class);
    }

    public function violations()
    {
        return $this->hasMany(UserViolation::class);
    }

    public function reportedViolations()
    {
        return $this->hasMany(UserViolation::class, 'reported_by');
    }

    public function getCurrentSession()
    {
        return $this->sessionLogs()->active()->latest()->first();
    }

    public function getTodayEngagement()
    {
        return $this->engagementMetrics()
            ->where('date', today())
            ->first();
    }

    public function getAverageEngagementScore($days = 30)
    {
        return $this->engagementMetrics()
            ->where('date', '>=', now()->subDays($days))
            ->avg('engagement_score') ?? 0;
    }

}
