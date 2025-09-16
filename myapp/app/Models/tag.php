<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class tag extends Model
{
    //
    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tag');
    }
}
