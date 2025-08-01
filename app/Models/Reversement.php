<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reversement extends Model
{
    protected $fillable = [
        'montant',
        'reference',
        'date_reversement',
        'rib_id',
        'proprietaire_id',
    ];

    public function rib()
    {
        return $this->belongsTo(Rib::class,);
    }

    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'code_id');
    }
}
