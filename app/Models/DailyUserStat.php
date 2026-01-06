<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyUserStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'date',
        'items_viewed',
        'searches_made',
        'messages_sent',
        'messages_received',
        'offers_made',
        'offers_received',
        'items_listed',
        'items_sold',
        'items_bought',
        'revenue_earned',
        'amount_spent',
        'coins_earned',
        'coins_spent',
        'login_count',
        'active_minutes',
    ];

    protected $casts = [
        'date' => 'date',
        'revenue_earned' => 'decimal:2',
        'amount_spent' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function incrementStat($userId, $field, $amount = 1)
    {
        $stat = self::firstOrCreate([
            'user_id' => $userId,
            'date' => today(),
        ]);

        $stat->increment($field, $amount);
    }
}
