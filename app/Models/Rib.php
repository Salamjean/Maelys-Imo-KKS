<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rib extends Model
{
    protected $fillable = [
        'rib',
        'banque',
        'proprietaire_id',
    ];

    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class);
    }
    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }
}
