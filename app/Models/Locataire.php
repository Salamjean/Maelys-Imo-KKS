<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * @OA\Schema(
 *     schema="Locataire",
 *     title="Locataire",
 *     description="Modèle de locataire",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="code_id", type="string", example="LOC123"),
 *     @OA\Property(property="name", type="string", example="John"),
 *     @OA\Property(property="prenom", type="string", example="Doe"),
 *     @OA\Property(property="email", type="string", example="john@example.com"),
 *     @OA\Property(property="contact", type="string", example="0102030405"),
 *     @OA\Property(property="adresse", type="string", example="Abidjan"),
 *     @OA\Property(property="status", type="string", example="Actif")
 * )
 * 
 * @OA\Schema(
 *     schema="LocataireWithRelations",
 *     title="Locataire avec Relations",
 *     description="Modèle de locataire incluant ses relations",
 *     allOf={
 *         @OA\Schema(ref="#/components/schemas/Locataire"),
 *         @OA\Schema(
 *             @OA\Property(property="agence", type="object"),
 *             @OA\Property(property="proprietaire", type="object"),
 *             @OA\Property(property="bien", type="object")
 *         )
 *     }
 * )
 */
class Locataire extends Authenticatable
{
    use HasApiTokens, Notifiable;

    protected $fillable = [
        'code_id', // AJOUTÉ ICI
        'name',
        'prenom',
        'email',
        'password',
        'contact',
        'piece',
        'adresse',
        'profession',
        'attestation',
        'status',
        'motif',
        'agence_id',
        'proprietaire_id',
        'contrat_id',
        'comptable_id',
        'bien_id',
        'contrat',
        'image1',
        'image2',
        'image3',
        'image4',
        'password_reset_token',
        'fcm_token',
        'password_reset_expires',
        'password_reset_otp',
        'otp_attempts',
        'reset_access_token',
        'reset_access_expires',
        'otp_verified_at'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function agence()
    {
        return $this->belongsTo(Agence::class, 'agence_id', 'code_id');
    }

    public function contrat()
    {
        return $this->belongsTo(Contrat::class);
    }

    public function bien()
    {
        return $this->belongsTo(Bien::class);
    }

    public function paiements()
    {
        return $this->hasMany(Paiement::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'Actif');
    }
    public function proprietaire()
    {
        return $this->belongsTo(Proprietaire::class, 'proprietaire_id', 'code_id');
    }
    public function comptable()
    {
        return $this->belongsTo(Comptable::class);
    }

    public function etatlieu()
    {
        return $this->hasMany(EtatLieu::class);
    }
    public function etatlieusorti()
    {
        return $this->hasMany(EtatLieuSorti::class);
    }

    public function historiqueLocations()
    {
        return $this->hasMany(HistoriqueLocation::class);
    }
    public function verifycode()
    {
        return $this->belongsTo(CashVerificationCode::class);
    }

    public function verificationCodes()
    {
        return $this->hasMany(VerificationCode::class);
    }
}
