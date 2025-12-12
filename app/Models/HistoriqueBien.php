<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HistoriqueBien extends Model
{
    use HasFactory;
    
    // Autorise tous les champs
    protected $guarded = []; 
    
    // Si tu as des erreurs de date, ajoute ceci :
    protected $dates = ['date_suppression'];
}