<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Locataire extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'code_id', // AJOUTÉ ICI
        'name',
        'prenom',
        'email',
        'password',
        'contact',
        'piece',
        'adresse',
        'profession',
        'attestation',
        'status',
        'motif',
        'agence_id',
        'proprietaire_id',
        'contrat_id',
        'comptable_id',
        'bien_id',
        'contrat',
        'image1',
        'image2',
        'image3',
        'image4',
        'password_reset_token',
        'fcm_token',
        'password_reset_expires',
        'password_reset_otp',
        'otp_attempts',
        'reset_access_token',
        'reset_access_expires',
        'otp_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class,'agence_id', 'code_id');
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Actif');
    }
    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'code_id');
    }
    public function comptable()
    {
        return $this->belongsTo(Comptable::class);
    }

    public function etatlieu()
    {
        return $this->hasMany(EtatLieu::class);
    }
    public function etatlieusorti()
    {
        return $this->hasMany(EtatLieuSorti::class);
    }
    public function verifycode()
    {
        return $this->belongsTo(CashVerificationCode::class);
    }

    public function verificationCodes()
    {
        return $this->hasMany(VerificationCode::class);
    }
}