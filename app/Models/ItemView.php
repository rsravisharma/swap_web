<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemView extends Model
{
    protected $fillable = [
        'item_id',
        'user_id', 
        'ip_address',
        'user_agent',
        'viewed_at'
    ];

    protected $casts = [
        'viewed_at' => 'datetime'
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
