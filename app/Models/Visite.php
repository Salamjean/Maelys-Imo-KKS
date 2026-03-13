<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Visite",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="nom", type="string", example="John Doe"),
 *     @OA\Property(property="email", type="string", format="email", example="john.doe@example.com"),
 *     @OA\Property(property="date_visite", type="string", format="date", example="2023-11-15")
 * )
 */
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
