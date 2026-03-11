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
        'image',
        'image1',
        'image2',
        'image3',
        'image4',
        'image5',
        'agence_id',
        'montant_majore',
        'video_3d',
        'commercial_id',
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

    public function getVideo3dEmbedUrl()
    {
        if (empty($this->video_3d)) {
            return null;
        }

        // Si c'est déjà un iframe, on le retourne tel quel
        if (str_contains($this->video_3d, '<iframe')) {
            return $this->video_3d;
        }

        $url = trim($this->video_3d);

        // Nettoyage et normalisation
        if (str_contains($url, 'kuula.co')) {
            if (!str_contains($url, '/share/')) {
                $url = str_replace(['/post/', '/explore/', '/p/'], '/share/', $url);
            }
            // Add Kuula specific parameters
            if (!str_contains($url, 'muted=')) {
                $url .= (str_contains($url, '?') ? '&' : '?') . 'muted=1&controls=0&autorotate=0.5';
            }
        } elseif (str_contains($url, 'keypano.com')) {
            // Le sous-domaine player. semble ne pas fonctionner chez tout le monde
            // On s'assure juste que c'est le lien /v/ qui est souvent embeddable
            // et on retire le .html si présent pour certains players
            $url = str_replace('.html', '', $url);
            // Add Keypano specific parameters
            if (!str_contains($url, 'muted=')) {
                $url .= (str_contains($url, '?') ? '&' : '?') . 'muted=1&controls=0';
            }
        } elseif (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            if (str_contains($url, 'watch?v=')) {
                $url = str_replace(['www.youtube.com', 'youtube.com'], 'www.youtube-nocookie.com', $url);
                $url = str_replace('watch?v=', 'embed/', $url);
            } elseif (str_contains($url, 'youtu.be/')) {
                $url = str_replace('youtu.be/', 'www.youtube-nocookie.com/embed/', $url);
            } else {
                $url = str_replace(['www.youtube.com', 'youtube.com'], 'www.youtube-nocookie.com', $url);
            }

            // Paramètres pour une lecture "propre" (sans recommandations externes, branding discret)
            $params = [
                'rel' => 0,
                'modestbranding' => 1,
                'iv_load_policy' => 3,
                'showinfo' => 0,
                'mute' => 1,
                'controls' => 0,
                'autoplay' => 1,
                'disablekb' => 1
            ];

            foreach ($params as $key => $value) {
                if (!str_contains($url, $key . '=')) {
                    $url .= (str_contains($url, '?') ? '&' : '?') . $key . '=' . $value;
                }
            }
        } elseif (str_contains($url, 'vimeo.com/')) {
            if (!str_contains($url, 'player.vimeo.com')) {
                $url = str_replace('vimeo.com/', 'player.vimeo.com/video/', $url);
            }
        } elseif (str_contains($url, 'matterport.com/show/?m=')) {
            // Format standard Matterport
        }

        return $url;
    }

}
