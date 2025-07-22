<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EtatLieuSorti extends Model
{
     protected $fillable = [
                'locataire_id',
                'bien_id',
                'type_bien',
                'commune_bien',
                'presence_partie',
                'status_etat_entre',
                'status_sorti',
                'parties_communes',
                'chambres',
                'nombre_cle',
                
        ];

        public function locataire()
        {
            return $this->belongsTo(Locataire::class);
        }

        public function proprietaire()
        {
            return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'code_id');
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
