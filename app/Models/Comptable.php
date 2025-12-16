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
        'date_naissance', // Correction de la faute de frappe ici
        'user_type',
        'profile_image',
        'agence_id', // Ajouté car utilisé dans le controller
        'proprietaire_id', // Ajouté si nécessaire
        'password_reset_token',
        'password_reset_expires',
        'password_reset_otp',
        'fcm_token',
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

    public function versementsEnvoyes()
    {
        return $this->hasMany(Versement::class, 'agent_id');
    }

    public function versementsRecus()
    {
        return $this->hasMany(Versement::class, 'comptable_id');
    }

    public function isComptable()
    {
        return $this->user_type === 'Comptable';
    }

    public function isAgent()
    {
        return $this->user_type === 'Agent de recouvrement';
    }
}