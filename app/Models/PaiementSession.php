<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaiementSession extends Model
{
    protected $fillable = [
        'transaction_id', 'locataire_id', 'bien_id',
        'montant', 'mois_couvert', 'metadata', 'expires_at',
    ];

     public function locataire()
    {
        return $this->belongsTo(Locataire::class, 'locataire_id', 'code_id');
    }

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }

    protected $casts = [
        'metadata' => 'array', // Cast automatique en array
        'expires_at' => 'datetime',
    ];
}
