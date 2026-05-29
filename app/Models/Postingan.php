<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postingan extends Model
{
    protected $table = 'travel_posts';

    protected $fillable = [
        'user_id', 'title', 'location', 'story',
        'destinations', 'total_budget', 'travel_date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(FotoPostingan::class, 'travel_post_id');
    }

    public function ratings()
    {
        return $this->hasMany(RatingPostingan::class, 'travel_post_id');
    }

    public function avgRating()
    {
        return round($this->ratings()->avg('score'), 1) ?: 0;
    }
}
