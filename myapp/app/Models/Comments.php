<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comments extends Model
{
    protected $fillable = [
        'post_id',
        'user_id',
        'parent_id',
        'body',
        'created_by',
        'updated_by'
    ];

    public function post()
    {
        return $this->belongsTo(Post::class, 'post_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function comment()
    {
        return $this->belongsTo(Comments::class, 'parent_id');
    }
    public function replies()
    {
        return $this->hasMany(Comments::class, 'parent_id');
    }
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
    }
}
