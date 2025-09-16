<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    //filables
    protected $fillable =
    [
        'user_id',
        'title',
        'slug',
        'body',
        'excerpt',
        'status',
        'created_by',
        'updated_by'
    ];
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
    public function comments()
    {
        return $this->hasMany(Comments::class, 'post_id', 'id');
    }
    public function likes()
    {
        return $this->morphMany(Like::class, 'likeable');
        //in laravel for the morphMany relation no need to metioned the foreign key we just give the name
        // of the morph name that is likeable given by default the laravel it expect two values(column)
        // likeable_id and likeable_type
    }
    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tag');
    }
}
