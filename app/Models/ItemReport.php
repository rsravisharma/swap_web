<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'reporter_id',
        'item_id',
        'item_owner_id',
        'reason',
        'category',
        'description',
        'status'
    ];

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function item()
    {
        return $this->belongsTo(Product::class, 'item_id');
    }

    public function itemOwner()
    {
        return $this->belongsTo(User::class, 'item_owner_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }
}
