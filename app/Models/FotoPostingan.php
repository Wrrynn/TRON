<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FotoPostingan extends Model
{
    protected $table = 'post_photos';
    protected $fillable = ['travel_post_id', 'file_path'];
}