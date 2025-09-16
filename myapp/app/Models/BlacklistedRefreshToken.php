<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BlacklistedRefreshToken extends Model
{
    protected $table = 'blacklisted_refresh_token';
    protected $fillable = [
        'user_id',
        'jti',
        'refresh_token',
        'reason'
    ];
}
