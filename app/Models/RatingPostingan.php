<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingPostingan extends Model
{
    protected $table = 'ratings';
    protected $fillable = ['user_id', 'travel_post_id', 'score'];
}