<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'json', // Automatically encode/decode JSON values
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Helper methods for common preference operations
    public static function setPreference(int $userId, string $key, mixed $value): self
    {
        return self::updateOrCreate(
            ['user_id' => $userId, 'key' => $key],
            ['value' => $value]
        );
    }

    public static function getPreference(int $userId, string $key, mixed $default = null): mixed
    {
        $preference = self::where('user_id', $userId)
            ->where('key', $key)
            ->first();

        return $preference ? $preference->value : $default;
    }

    public static function getUserPreferences(int $userId): array
    {
        return self::where('user_id', $userId)
            ->pluck('value', 'key')
            ->toArray();
    }

    public static function removePreference(int $userId, string $key): bool
    {
        return self::where('user_id', $userId)
            ->where('key', $key)
            ->delete() > 0;
    }

    public static function hasPreference(int $userId, string $key): bool
    {
        return self::where('user_id', $userId)
            ->where('key', $key)
            ->exists();
    }

    // Bulk operations
    public static function setMultiplePreferences(int $userId, array $preferences): void
    {
        foreach ($preferences as $key => $value) {
            self::setPreference($userId, $key, $value);
        }
    }

    public static function removeMultiplePreferences(int $userId, array $keys): void
    {
        self::where('user_id', $userId)
            ->whereIn('key', $keys)
            ->delete();
    }

    // Scopes
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function scopeByKeys($query, array $keys)
    {
        return $query->whereIn('key', $keys);
    }

    // Common preference keys as constants
    public const NOTIFICATION_SETTINGS = 'notification_settings';
    public const PRIVACY_SETTINGS = 'privacy_settings';
    public const SEARCH_FILTERS = 'search_filters';
    public const FAVORITE_CATEGORIES = 'favorite_categories';
    public const PREFERRED_CURRENCY = 'preferred_currency';
    public const PREFERRED_LANGUAGE = 'preferred_language';
    public const THEME_PREFERENCE = 'theme_preference';
    public const LOCATION_SETTINGS = 'location_settings';
    public const COMMUNICATION_PREFERENCES = 'communication_preferences';
    public const SELLER_PREFERENCES = 'seller_preferences';
    public const BUYER_PREFERENCES = 'buyer_preferences';
    public const APP_TUTORIAL_COMPLETED = 'app_tutorial_completed';
    public const LAST_SEEN_ANNOUNCEMENT = 'last_seen_announcement';
    public const SAVED_SEARCHES = 'saved_searches';
    public const BLOCKED_KEYWORDS = 'blocked_keywords';
    public const PREFERRED_MEETUP_LOCATIONS = 'preferred_meetup_locations';

    // Helper methods for specific preferences
    public static function getNotificationSettings(int $userId): array
    {
        return self::getPreference($userId, self::NOTIFICATION_SETTINGS, [
            'push_notifications' => true,
            'email_notifications' => true,
            'sms_notifications' => false,
            'marketing_notifications' => true,
            'new_message_notifications' => true,
            'offer_notifications' => true,
            'price_drop_notifications' => true,
        ]);
    }

    public static function getPrivacySettings(int $userId): array
    {
        return self::getPreference($userId, self::PRIVACY_SETTINGS, [
            'show_phone_number' => false,
            'show_email' => false,
            'show_location' => true,
            'show_online_status' => true,
            'allow_messages_from_strangers' => true,
            'show_items_to_followers_only' => false,
        ]);
    }

    public static function getSearchFilters(int $userId): array
    {
        return self::getPreference($userId, self::SEARCH_FILTERS, [
            'min_price' => null,
            'max_price' => null,
            'condition' => [],
            'location_radius' => 50,
            'categories' => [],
            'sort_by' => 'created_at',
            'sort_order' => 'desc',
        ]);
    }

    public static function getSellerPreferences(int $userId): array
    {
        return self::getPreference($userId, self::SELLER_PREFERENCES, [
            'auto_accept_offers' => false,
            'minimum_offer_percentage' => 80,
            'allow_negotiations' => true,
            'preferred_payment_methods' => ['cash', 'upi'],
            'default_meetup_location' => null,
            'auto_renewal_enabled' => false,
            'promotional_boost_enabled' => false,
        ]);
    }

    public static function getBuyerPreferences(int $userId): array
    {
        return self::getPreference($userId, self::BUYER_PREFERENCES, [
            'max_travel_distance' => 25,
            'preferred_payment_methods' => ['cash', 'upi'],
            'save_search_history' => true,
            'auto_save_favorites' => true,
            'price_alert_enabled' => true,
        ]);
    }

    // JSON value helpers
    public function addToJsonArray(string $item): self
    {
        $currentValue = is_array($this->value) ? $this->value : [];
        
        if (!in_array($item, $currentValue)) {
            $currentValue[] = $item;
            $this->value = $currentValue;
            $this->save();
        }

        return $this;
    }

    public function removeFromJsonArray(string $item): self
    {
        $currentValue = is_array($this->value) ? $this->value : [];
        
        $this->value = array_values(array_filter($currentValue, fn($val) => $val !== $item));
        $this->save();

        return $this;
    }

    public function updateJsonKey(string $key, mixed $value): self
    {
        $currentValue = is_array($this->value) ? $this->value : [];
        $currentValue[$key] = $value;
        
        $this->value = $currentValue;
        $this->save();

        return $this;
    }
}
