<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Proprietaire extends Authenticatable
{
    protected $casts = [
    'last_balance_update' => 'datetime', // Convertit automatiquement en Carbon
    ];
    protected $table = 'proprietaires';
    protected $fillable = [
        'code_id',
        'name',
        'prenom',
        'email',
        'password',
        'contact',
        'commune',
        'choix_paiement',
        'rib',
        'pourcentage',
        'profil_image',
        'contrat',
        'gestion',
        'agence_id',
        'solde',
    ];
    public function biens()
    {
        return $this->hasMany(Bien::class, 'proprietaire_id', 'code_id');
    }
    public function proprietaire()
    {
        return $this->hasMany(Proprietaire::class);
    }
    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    public function reversements()
    {
        return $this->hasMany(Reversement::class, 'proprietaire_id', 'code_id');
    }
  public function needsMonthlyRefresh()
    {
        $lastUpdate = $this->last_balance_update 
            ? Carbon::parse($this->last_balance_update) 
            : null;

        return now()->day >= 15 && 
            (!$lastUpdate || $lastUpdate->format('Y-m') !== now()->format('Y-m'));
    }

    public function calculateCurrentBalance()
    {
        return $this->solde;
    }

    public function monthlyRevenue()
    {
        return $this->biens->sum(function($bien) {
            return (float) $bien->prix;
        });
    }
}
