<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtatLieu extends Model
{
     protected $fillable = [
            'adresse_bien',
            'type_bien',
            'lot',
            'date_etat',
            'nature_etat',
            'nom_locataire',
            'nom_proprietaire',
            'presence_partie',
            'etat_entre',
            'etat_sorti',
            'type_compteur',
            'numero_compteur',
            'releve_entre',
            'releve_sorti',
            'sol',
            'murs',
            'plafond',
            'porte_entre',
            'interrupteur',
            'eclairage',
            'remarque',
            'locataire_id',
            'proprietaire_id',
            'agence_id'
        ];
        public function locataire()
        {
            return $this->belongsTo(Locataire::class, 'locataire_id', 'code_id');
        }

        public function proprietaire()
        {
            return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'code_id');
        }
        public function agence()
        {
            return $this->belongsTo(Agence::class, 'agence_id', 'code_id');
        }
}
