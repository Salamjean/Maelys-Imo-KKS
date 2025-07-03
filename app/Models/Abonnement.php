<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Abonnement extends Model
{
    protected $fillable = [
        'proprietaire_id',
        'agence_id',
        'date_abonnement',
        'date_debut',
        'date_fin',
        'mois_abonne',
        'montant',
        'montant_actuel',
        'statut',
        'mode_paiement',
        'reference_paiement',
        'notes'
    ];

    protected $dates = [
        'date_abonnement',
        'date_debut',
        'date_fin',
        'created_at',
        'updated_at'
    ];

    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'code_id');
    }
    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'code_id');
    }
}
