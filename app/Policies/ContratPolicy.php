<?php

namespace App\Policies;

use App\Models\Agence;
use App\Models\Contrat;
use Illuminate\Auth\Access\HandlesAuthorization;

class ContratPolicy
{
    use HandlesAuthorization;

    public function view(Agence $agence, Contrat $contrat)
    {
        return $agence->id === $contrat->locataire->agence_id;
    }

    public function download(Agence $agence, Contrat $contrat)
    {
        return $agence->id === $contrat->locataire->agence_id;
    }

    public function delete(Agence $agence, Contrat $contrat)
    {
        return $agence->id === $contrat->locataire->agence_id;
    }
}