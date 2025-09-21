<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class Comptable extends Authenticatable
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
        'date_naisance',
        'user_type',
        'profile_image',
        // ... autres champs
        'password_reset_token',
        'password_reset_expires',
        'password_reset_otp',
        'otp_attempts',
        'reset_access_token',
        'reset_access_expires',
        'otp_verified_at'
    ];
    public function agence()
    {
        return $this->belongsTo(Agence::class,'agence_id', 'code_id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    // Relation : Versements effectués par un agent (pour dashboard agent)
    public function versementsEnvoyes()
    {
        return $this->hasMany(Versement::class, 'agent_id');
    }

    // Relation : Versements reçus par un comptable (pour dashboard comptable)
    public function versementsRecus()
    {
        return $this->hasMany(Versement::class, 'comptable_id');
    }

    // Vérifie si l'utilisateur est un comptable
    public function isComptable()
    {
        return $this->user_type === 'Comptable';
    }

    // Vérifie si l'utilisateur est un agent
    public function isAgent()
    {
        return $this->user_type === 'Agent de recouvrement';
    }
}
