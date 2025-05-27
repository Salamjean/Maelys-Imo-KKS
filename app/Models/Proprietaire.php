<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class Proprietaire extends Authenticatable
{
    protected $table = 'proprietaires';
    protected $fillable = [
        'name',
        'prenom',
        'email',
        'password',
        'contact',
        'commune',
        'fonction',
        'profil_image',
    ];
    public function biens()
    {
        return $this->hasMany(Bien::class);
    }
    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }
}
