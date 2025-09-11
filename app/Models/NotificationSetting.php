<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'email_notifications',
        'push_notifications',
        'sms_notifications',
        'marketing_emails',
        'new_message_notifications', 
        'new_offer_notifications',
        'security_notifications',
        'product_update_notifications'
    ];

    protected $casts = [
        'email_notifications' => 'boolean',
        'push_notifications' => 'boolean',
        'sms_notifications' => 'boolean',
        'marketing_emails' => 'boolean',
        'new_message_notifications' => 'boolean',
        'new_offer_notifications' => 'boolean',
        'security_notifications' => 'boolean',
        'product_update_notifications' => 'boolean'
    ];

    /**
     * Get the user that owns the notification settings
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
