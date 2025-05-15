<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visite extends Model
{
    protected $fillable = [
        'bien_id',
        'nom',
        'email',
        'telephone',
        'date_visite',
        'heure_visite',
        'message',
    ];

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }
}
