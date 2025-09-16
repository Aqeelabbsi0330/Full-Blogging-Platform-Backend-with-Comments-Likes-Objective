<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlacklistedAccessToken extends Model
{
    protected $table = 'blacklisted_access_token';
    protected $fillable = [
        'user_id',
        'access_token',
        'jti',
        'reason'
    ];
}
