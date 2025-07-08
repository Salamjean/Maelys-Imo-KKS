<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentPartner extends Model
{
   protected $fillable = [
        'agence_id',
        'proprietaire_id',
        'montant',
        'mode_paiement',
        'beneficiaire_nom',
        'beneficiaire_prenom',
        'beneficiaire_contact',
        'beneficiaire_email',
        'rib',
        'fichier_paiement',
        'est_proprietaire',
        'verification_code',
        'statut',
        'code_valide_par',
        'numero_cni',
        'date_validation'
    ];

    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'code_id' );
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'code_id');
    }
    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }
}
