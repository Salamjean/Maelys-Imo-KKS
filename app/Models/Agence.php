<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Agence extends Authenticatable
{
    use Notifiable;
    protected $fillable = [
        'name',
        'email',
        'commune',
        'password',
        'contact',
        'adresse',
        'profile_image',
        'password_reset_token',
        'password_reset_expires'
    ];

    public function biens()
    {
        return $this->hasMany(Bien::class);
    }
    public function locataire()
    {
        return $this->hasMany(Locataire::class);
    }
    public function comptable()
    {
        return $this->hasMany(Comptable::class);
    }
    public function proprietaire()
    {
        return $this->hasMany(Proprietaire::class);
    }
    
}
