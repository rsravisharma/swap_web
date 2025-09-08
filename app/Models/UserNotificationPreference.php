<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserNotificationPreference extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'chat_notifications',
        'offer_notifications',
        'item_notifications',
        'marketing_notifications',
        'sound_enabled',
        'vibration_enabled',
    ];

    protected $casts = [
        'chat_notifications' => 'boolean',
        'offer_notifications' => 'boolean',
        'item_notifications' => 'boolean',
        'marketing_notifications' => 'boolean',
        'sound_enabled' => 'boolean',
        'vibration_enabled' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
