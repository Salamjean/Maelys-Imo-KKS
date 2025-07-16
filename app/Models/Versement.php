<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Versement extends Model
{
    protected $fillable = [
        'agent_id', 
        'comptable_id', 
        'montant_verse',
        'montant_percu',
        'reste_a_verser',
    ];
    
    // Relation avec l'agent (comptable de type agent)
    public function agent()
    {
        return $this->belongsTo(Comptable::class, 'agent_id');
    }
    
    // Relation avec le comptable
    public function comptable()
    {
        return $this->belongsTo(Comptable::class, 'comptable_id');
    }
}
