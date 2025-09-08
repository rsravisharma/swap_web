<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Rating extends Model
{
    use HasFactory;

    protected $fillable = [
        'rater_id',
        'rated_user_id',
        'transaction_id',
        'rating',
        'comment',
        'helpful_count'
    ];

    protected $casts = [
        'rating' => 'integer',
        'helpful_count' => 'integer'
    ];

    public function rater()
    {
        return $this->belongsTo(User::class, 'rater_id');
    }

    public function ratedUser()
    {
        return $this->belongsTo(User::class, 'rated_user_id');
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function userHelpfulMarks()
    {
        return $this->hasMany(RatingHelpful::class);
    }

    public function reports()
    {
        return $this->hasMany(RatingReport::class);
    }
}
