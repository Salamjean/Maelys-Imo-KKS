<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Agence;
use App\Models\Bien;
use App\Models\Paiement;
use App\Models\ResetCodePasswordAgence;
use App\Models\Reversement;
use App\Models\Visite;
use App\Notifications\SendEmailToAgenceAfterRegistrationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class CommercialAgenceController extends Controller
{
    public function index()
    {
        // Demandes de visite en attente (même logique que l'admin)
        $pendingVisits = Visite::where('statut', 'en attente')
            ->whereHas('bien', function ($query) {
                $query->whereNull('agence_id');
                $query->whereNull('proprietaire_id')
                    ->orWhereHas('proprietaire', function ($q) {
                    $q->where('gestion', 'agence');
                });
            })
            ->count();
            
        // Récupération de toutes les agences
        $agences = Agence::paginate(6);
        return view('commercial.agence.index', compact('agences', 'pendingVisits'));
    }

    public function create()
    {
        // Demandes de visite en attente
        $pendingVisits = Visite::where('statut', 'en attente')
            ->whereHas('bien', function ($query) {
                $query->whereNull('agence_id');
                $query->whereNull('proprietaire_id')
                    ->orWhereHas('proprietaire', function ($q) {
                    $q->where('gestion', 'agence');
                });
            })
            ->count();
        return view('commercial.agence.create', compact('pendingVisits'));
    }

    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agences,email',
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'rib' => 'required|file|mimes:pdf|max:2048',
            'rccm' => 'required|string|max:255',
            'rccm_file' => 'required|file|mimes:pdf|max:2048',
            'dfe' => 'required|string|max:255',
            'dfe_file' => 'required|file|mimes:pdf|max:2048',
        ], [
            'name.required' => 'Le nom de l\'agence est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'La commune est obligatoire.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'rib.required' => 'Le RIB est obligatoire.',
            'rib.mimes' => 'Le fichier doit être un pdf',
        ]);

        try {
            // Génération du code PRO unique
            do {
                $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $codeId = 'AG' . $randomNumber;
            } while (Agence::where('code_id', $codeId)->exists());

            // Traitement de l'image de profil
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            }

            $ribPath = null;
            if ($request->hasFile('rib')) {
                $ribPath = $request->file('rib')->store('ribs', 'public');
            }
            $rccmPath = null;
            if ($request->hasFile('rccm_file')) {
                $rccmPath = $request->file('rccm_file')->store('rccm_files', 'public');
            }
            $dfe_filePath = null;
            if ($request->hasFile('dfe_file')) {
                $dfe_filePath = $request->file('dfe_file')->store('dfe_files', 'public');
            }

            // Création de l'agence
            $agence = new Agence();
            $agence->code_id = $codeId;
            $agence->name = $request->name;
            $agence->email = $request->email;
            $agence->contact = $request->contact;
            $agence->commune = $request->commune;
            $agence->adresse = $request->adresse;
            $agence->rccm_file = $rccmPath;
            $agence->dfe_file = $dfe_filePath;
            $agence->rccm = $request->rccm;
            $agence->dfe = $request->dfe;
            $agence->rib = $ribPath;
            $agence->password = Hash::make('password');
            $agence->profile_image = $profileImagePath;
            $agence->commercial_id = Auth::guard('commercial')->user()->code_id;
            $agence->save();

            // CRÉATION AUTOMATIQUE DE L'ABONNEMENT
            $today = now();
            $dateDebut = $today->format('Y-m-d');
            $dateFin = $today->copy()->addMonths(3)->format('Y-m-d');

            $abonnementData = [
                'agence_id' => $agence->code_id,
                'date_abonnement' => $today,
                'date_debut' => $dateDebut,
                'date_fin' => $dateFin,
                'mois_abonne' => $today->format('m-Y'),
                'montant' => 0,
                'montant_actuel' => 0,
                'statut' => 'actif',
                'type' => 'standard',
                'mode_paiement' => 'offert',
                'reference_paiement' => 'CREA-' . $agence->code_id,
                'notes' => 'Abonnement créé automatiquement lors de l\'inscription',
            ];

            Abonnement::create($abonnementData);
            Log::info('Abonnement créé par commercial', ['agence_id' => $agence->code_id]);

            // Envoi de l'e-mail de vérification
            ResetCodePasswordAgence::where('email', $agence->email)->delete();
            $code = rand(1000, 4000);
            ResetCodePasswordAgence::create([
                'code' => $code,
                'email' => $agence->email,
            ]);

            Notification::route('mail', $agence->email)
                ->notify(new SendEmailToAgenceAfterRegistrationNotification($code, $agence->email));

            return redirect()->route('commercial.agences.index')
                ->with('success', 'Agence enregistrée avec succès.');

        } catch (\Exception $e) {
            Log::error('Error creating agence par commercial: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $pendingVisits = Visite::where('statut', 'en attente')
            ->whereHas('bien', function ($query) {
                $query->whereNull('agence_id');
                $query->whereNull('proprietaire_id')
                    ->orWhereHas('proprietaire', function ($q) {
                    $q->where('gestion', 'agence');
                });
            })
            ->count();
        $agence = Agence::findOrFail($id);
        return view('commercial.agence.edit', compact('agence', 'pendingVisits'));
    }

    public function update(Request $request, $id)
    {
        $agence = Agence::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:agences,email,' . $agence->id,
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'adresse' => 'required|string|max:255',
            'rib' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'rccm' => 'required|string|max:255',
            'rccm_file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'dfe' => 'required|string|max:255',
            'dfe_file' => 'nullable|file|mimes:pdf,jpeg,png,jpg|max:2048',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        try {
            if ($request->hasFile('profile_image')) {
                if ($agence->profile_image) Storage::disk('public')->delete($agence->profile_image);
                $agence->profile_image = $request->file('profile_image')->store('profile_images', 'public');
            }

            if ($request->hasFile('rib')) {
                if ($agence->rib) Storage::disk('public')->delete($agence->rib);
                $agence->rib = $request->file('rib')->store('ribs', 'public');
            }

            if ($request->hasFile('rccm_file')) {
                if ($agence->rccm_file) Storage::disk('public')->delete($agence->rccm_file);
                $agence->rccm_file = $request->file('rccm_file')->store('rccm_files', 'public');
            }

            if ($request->hasFile('dfe_file')) {
                if ($agence->dfe_file) Storage::disk('public')->delete($agence->dfe_file);
                $agence->dfe_file = $request->file('dfe_file')->store('dfe_files', 'public');
            }

            $agence->name = $request->name;
            $agence->email = $request->email;
            $agence->contact = $request->contact;
            $agence->commune = $request->commune;
            $agence->adresse = $request->adresse;
            $agence->rccm = $request->rccm;
            $agence->dfe = $request->dfe;
            $agence->save();

            return redirect()->route('commercial.agences.index')
                ->with('success', 'Agence mise à jour avec succès.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $agence = Agence::findOrFail($id);
            Abonnement::where('agence_id', $agence->code_id)->delete();

            if ($agence->rib) Storage::delete('public/' . $agence->rib);
            if ($agence->profile_image) Storage::delete('public/' . $agence->profile_image);

            $agence->delete();
            DB::commit();

            return redirect()->back()->with('success', 'Agence et ses abonnements supprimés avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}
