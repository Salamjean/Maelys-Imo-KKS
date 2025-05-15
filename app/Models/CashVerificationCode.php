<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CashVerificationCode extends Model
{
    use HasFactory;

    protected $fillable = [
        'locataire_id',
        'paiement_id',
        'code',
        'expires_at',
        'used_at',
        'is_archived'
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
