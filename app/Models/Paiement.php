<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="Paiement",
 *     type="object",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="reference", type="string", example="PAY-12345"),
 *     @OA\Property(property="montant", type="number", format="float", example=500.00),
 *     @OA\Property(property="date_paiement", type="string", format="date-time", example="2023-10-26T10:00:00Z"),
 *     @OA\Property(property="mois_couvert", type="string", example="2023-10"),
 *     @OA\Property(property="statut", type="string", enum={"payé", "En attente"}, example="payé")
 * )
 * 
 * @OA\Schema(
 *     schema="PaiementDetails",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Paiement"),
 *         @OA\Schema(
 *             type="object",
 *             @OA\Property(property="locataire", type="object", 
 *                 @OA\Property(property="name", type="string"),
 *                 @OA\Property(property="prenom", type="string"),
 *                 @OA\Property(property="email", type="string"),
 *             ),
 *             @OA\Property(property="bien", type="object",
 *                 @OA\Property(property="commune", type="string"),
 *                 @OA\Property(property="type", type="string"),
 *             )
 *         )
 *     }
 * )
 */
class Paiement extends Model
{
    protected $fillable = [
        'montant', 'date_paiement', 'mois_couvert',
        'methode_paiement', 'verif_espece', 'transaction_id', 'statut',
        'locataire_id', 'bien_id','comptable_id', 'contrat_id', 'proof_path','reference'
    ];

    public function locataire()
    {
        return $this->belongsTo(Locataire::class, 'locataire_id', 'code_id');
    }

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }
    

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }
    public function comptable()
    {
        return $this->belongsTo(Comptable::class, 'comptable_id', 'code_id');
    }
}
