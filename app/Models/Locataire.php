<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;


/**
 * @OA\Schema(
 *     schema="Locataire",
 *     type="object",
 *     title="Locataire",
 *     description="Modèle de locataire",
 *     required={"name", "email", "password", "contact"},
 *     @OA\Property(property="id", type="integer", format="int64", description="ID du locataire"),
 *     @OA\Property(property="name", type="string", description="Nom du locataire"),
 *     @OA\Property(property="prenom", type="string", description="Prénom du locataire"),
 *     @OA\Property(property="email", type="string", format="email", description="Email du locataire"),
 *     @OA\Property(property="contact", type="string", description="Contact du locataire"),
 *     @OA\Property(property="piece", type="string", description="Type de pièce d'identité"),
 *     @OA\Property(property="adresse", type="string", description="Adresse du locataire"),
 *     @OA\Property(property="profession", type="string", description="Profession du locataire"),
 *     @OA\Property(property="status", type="string", enum={"Actif", "Inactif"}, description="Statut du locataire"),
 *     @OA\Property(property="agence_id", type="integer", description="ID de l'agence associée"),
 *     @OA\Property(property="proprietaire_id", type="integer", description="ID du propriétaire associé"),
 *     @OA\Property(property="contrat_id", type="integer", description="ID du contrat associé"),
 *     @OA\Property(property="bien_id", type="integer", description="ID du bien associé"),
 *     @OA\Property(property="image1", type="string", format="binary", description="Image 1 du locataire"),
 *     @OA\Property(property="image2", type="string", format="binary", description="Image 2 du locataire"),
 *     @OA\Property(property="image3", type="string", format="binary", description="Image 3 du locataire"),
 *     @OA\Property(property="image4", type="string", format="binary", description="Image 4 du locataire"),
 *     @OA\Property(property="created_at", type="string", format="date-time", description="Date de création"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", description="Date de mise à jour")
 * )
 */
class Locataire extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $fillable = [
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
        // ... autres champs
        'password_reset_token',
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
        return $this->belongsTo(Agence::class,'agence_id', 'code_id');
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
    public function verifycode()
    {
        return $this->belongsTo(CashVerificationCode::class);
    }

    public function verificationCodes()
    {
        return $this->hasMany(VerificationCode::class);
    }
    

}
