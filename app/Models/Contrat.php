<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contrat extends Model
{
    use HasFactory;

    protected $fillable = [
        'locataire_id',
        'date_debut',
        'date_fin',
        'loyer_mensuel',
        'caution',
        'fichier_path'
    ];

    protected $dates = ['date_debut', 'date_fin'];

    public function locataire()
    {
        return $this->belongsTo(Locataire::class);
    }

    
}
