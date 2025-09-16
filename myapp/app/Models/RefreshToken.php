<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class RefreshToken extends Model
{
    protected $fillable = [
        'user_id',
        'jti',
        'refresh_token',
        'jti',
        'expire_at',
        'device_type',
        'ip_address'
    ];
}
