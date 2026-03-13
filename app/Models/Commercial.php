<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Commercial extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'name',
        'prenom',
        'code_id',
        'email',
        'commune',
        'password',
        'contact',
        'date_naissance',
        'profile_image',
        'is_active',
        'fcm_token',
        'password_reset_otp',
        'password_reset_token',
        'password_reset_expires',
        'otp_attempts',
        'otp_verified_at',
    ];
}
