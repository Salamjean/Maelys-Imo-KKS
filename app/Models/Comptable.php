<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Comptable extends Authenticatable
{
    protected $fillable = [
        'name',
        'prenom',
        'email',
        'commune',
        'password',
        'contact',
        'date_naisance',
        'profile_image',
    ];
    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }
}
