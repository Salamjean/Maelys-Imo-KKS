<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Locataire extends Authenticatable
{
    protected $fillable = [
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
        'bien_id',
        'contrat',
        'image1',
        'image2',
        'image3',
        'image4',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class);
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
        return $this->belongsTo(Proprietaire::class);
    }
    

}
