<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    protected $fillable = ['title', 'content', 'image', 'user_id', 'slug'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
