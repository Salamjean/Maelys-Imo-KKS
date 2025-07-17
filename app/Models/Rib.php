<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rib extends Model
{
    protected $fillable = [
        'rib',
        'banque',
        'proprietaire_id',
        'agence_id'
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
