<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Agence extends Authenticatable
{
    protected $fillable = [
        'name',
        'email',
        'commune',
        'password',
        'contact',
        'adresse',
        'profile_image',
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
