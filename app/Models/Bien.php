<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bien extends Model
{
    protected $fillable = [
        'type',
        'description',
        'superficie',
        'nombre_de_chambres',
        'nombre_de_toilettes',
        'garage',
        'avance',
        'caution',
        'prix',
        'commune',
        'date_fixe',
        'image',
        'image2',
        'agence_id',
        'montant_majore',
    ];

    public function agence()
{
    return $this->belongsTo(Agence::class);
}
    public function visites()
    {
        return $this->hasMany(Visite::class);
    }

    public function locataire()
{
    return $this->hasOne(Locataire::class);
}

public function getImages()
{
    return array_filter([
        $this->image,
        $this->image1,
        $this->image2,
        $this->image3,
        $this->image4,
        $this->image5
    ]);
}

public function hasImages()
{
    return !empty(array_filter($this->getImages()));
}

}
