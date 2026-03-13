<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @OA\Schema(
 *     schema="Proprietaire",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code_id", type="string", example="PROP123"),
 *     @OA\Property(property="name", type="string", example="Dupont"),
 *     @OA\Property(property="prenom", type="string", example="Jean"),
 *     @OA\Property(property="email", type="string", example="jean@example.com"),
 *     @OA\Property(property="contact", type="string", example="0102030405")
 * )
 */
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
        'diaspora',
        'contact',
        'commune',
        'choix_paiement',
        'rib',
        'pourcentage',
        'profil_image',
        'contrat',
        'cni',
        'gestion',
        'agence_id',
        'solde',
        'commercial_id',
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
    public function locataire()
    {
        return $this->hasMany(Locataire::class, 'proprietaire_id', 'code_id');
    }

    public function reversements()
    {
        return $this->hasMany(Reversement::class, 'proprietaire_id', 'code_id');
    }

    public function abonnements()
    {
        return $this->hasMany(Abonnement::class, 'proprietaire_id', 'code_id');
    }
    public function etatlieu()
    {
        return $this->hasMany(EtatLieu::class, 'proprietaire_id', 'code_id');
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
        return $this->biens->sum(function ($bien) {
            return (float) $bien->prix;
        });
    }

    public function commercial()
    {
        return $this->belongsTo(Commercial::class, 'commercial_id', 'code_id');
    }
}
