<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @OA\Schema(
 *     schema="CashVerificationCode",
 *     title="CashVerificationCode",
 *     description="Modèle de code de vérification cash",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="locataire_id", type="integer"),
 *     @OA\Property(property="code", type="string", example="123456"),
 *     @OA\Property(property="expires_at", type="string", format="date-time"),
 *     @OA\Property(property="used_at", type="string", format="date-time"),
 *     @OA\Property(property="is_archived", type="boolean")
 * )
 */
class CashVerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'locataire_id',
        'paiement_id',
        'code',
        'expires_at',
        'used_at',
        'is_archived',
        'nombre_mois',
        'mois_couverts',
        'montant_total',
        'qr_code_path',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'used_at' => 'datetime'
    ];

    public function locataire()
    {
        return $this->belongsTo(Locataire::class);
    }

    public function paiement()
    {
        return $this->belongsTo(Paiement::class);
    }

        // Dans le modèle CashVerificationCode
    public function agence()
    {
        return $this->belongsTo(Agence::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_archived', false)
                    ->where('expires_at', '>', now());
    }

    public function scopeArchived($query)
    {
        return $query->where('is_archived', true);
    }

    public function archive()
    {
        $this->update([
            'is_archived' => true,
            'used_at' => now()
        ]);
    }
}
