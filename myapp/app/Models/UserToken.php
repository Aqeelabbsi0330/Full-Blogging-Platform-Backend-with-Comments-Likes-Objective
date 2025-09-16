<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserToken extends Model
{
    protected $fillable = [
        'access_token',
        'jti',
        'user_id',
        'device_type',
        'ip_address',
        'expire_at'
    ];
}
