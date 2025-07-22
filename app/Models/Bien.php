<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bien extends Model
{
    protected $fillable = [
        'type',
        'utilisation',
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
        'image', 'image1', 'image2', 'image3', 'image4', 'image5',
        'agence_id',
        'montant_majore',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'code_id');
    }
    public function visites()
    {
        return $this->hasMany(Visite::class);
    }

    public function locataire()
    {
        return $this->hasOne(Locataire::class);
    }

    public function etatlieu()
    {
        return $this->hasMany(EtatLieu::class);
    }

    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'code_id');
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
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
