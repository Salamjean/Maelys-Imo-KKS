<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paiement extends Model
{
    protected $fillable = [
        'montant', 'date_paiement', 'mois_couvert',
        'methode_paiement', 'verif_espece', 'transaction_id', 'statut',
        'locataire_id', 'bien_id','comptable_id', 'contrat_id'
    ];

    public function locataire()
    {
        return $this->belongsTo(Locataire::class);
    }

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }
    public function comptable()
    {
        return $this->belongsTo(Comptable::class);
    }
}
