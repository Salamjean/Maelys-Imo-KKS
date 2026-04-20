<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoriqueLocation extends Model
{
    protected $fillable = [
        'locataire_id',
        'bien_id',
        'agence_id',
        'proprietaire_id',
        'date_entree',
        'date_sortie',
        'motif_sortie',
        'etat_lieu_entree_id',
        'etat_lieu_sortie_id',
    ];

    protected $casts = [
        'date_entree' => 'date',
        'date_sortie' => 'date',
    ];

    public function locataire()
    {
        return $this->belongsTo(Locataire::class);
    }

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'code_id');
    }

    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'code_id');
    }

    public function etatLieuEntree()
    {
        return $this->belongsTo(EtatLieu::class, 'etat_lieu_entree_id');
    }

    public function etatLieuSortie()
    {
        return $this->belongsTo(EtatLieuSorti::class, 'etat_lieu_sortie_id');
    }

    public function scopeEnCours($query)
    {
        return $query->whereNull('date_sortie');
    }

    public function scopeTermine($query)
    {
        return $query->whereNotNull('date_sortie');
    }
}
