<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Abonnement;
use App\Models\Proprietaire;
use App\Models\HistoriqueBien;
use App\Models\ResetCodePasswordProprietaire;
use App\Models\Visite;
use App\Models\Locataire;
use App\Notifications\SendEmailToOwnerAfterRegistrationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Twilio\Rest\Client;
use Twilio\Exceptions\TwilioException;

class CommercialProprietaireController extends Controller
{
    public function index()
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
        $proprietaires = Proprietaire::whereNull('agence_id')->paginate(6);
        return view('commercial.proprietaire.index', compact('proprietaires', 'pendingVisits'));
    }

    public function create()
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
        return view('commercial.proprietaire.create', compact('pendingVisits'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:proprietaires,email',
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'rib' => 'nullable|file|mimes:pdf,jpg,png|max:2048',
            'diaspora' => 'nullable|string',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'name.required' => 'Le nom du proprietaire est obligatoire.',
            'prenom.required' => 'Le prénom du proprietaire est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'Lieu de residence est obligatoire.',
            'rib.max' => 'Le fichier RIB ne doit pas dépasser 2Mo.',
            'rib.mimes' => 'Le RIB doit être au format PDF, JPG ou PNG.',
            'profile_image.image' => 'Le fichier doit être une image.',
            'profile_image.mimes' => 'L\'image doit être au format JPEG, PNG ou JPG.',
            'profile_image.max' => 'L\'image ne doit pas dépasser 2Mo.',
        ]);

        DB::beginTransaction();

        try {
            do {
                $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
                $codeId = 'PRO' . $randomNumber;
            } while (Proprietaire::where('code_id', $codeId)->exists());

            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            }

            $cniImagePath = null;
            if ($request->hasFile('cni')) {
                $cniImagePath = $request->file('cni')->store('cnis', 'public');
            }

            $ribPath = null;
            if ($request->hasFile('rib')) {
                $ribPath = $request->file('rib')->store('ribs', 'public');
            }

            $ownerData = [
                'code_id' => $codeId,
                'name' => $validatedData['name'],
                'prenom' => $validatedData['prenom'],
                'email' => $validatedData['email'],
                'contact' => $validatedData['contact'],
                'commune' => $validatedData['commune'],
                'rib' => $ribPath,
                'choix_paiement' => 'Virement Bancaire',
                'password' => Hash::make('password'),
                'profil_image' => $profileImagePath,
                'cni' => $cniImagePath,
                'diaspora' => $request->input('diaspora', '0') === '1' ? 'Oui' : 'Non',
                'gestion' => 'proprietaire',
                'commercial_id' => Auth::guard('commercial')->user()->code_id,
            ];

            $owner = Proprietaire::create($ownerData);

            $today = now();
            $abonnementData = [
                'proprietaire_id' => $owner->code_id,
                'date_abonnement' => $today,
                'date_debut' => $today->format('Y-m-d'),
                'date_fin' => $today->copy()->addMonths(3)->format('Y-m-d'),
                'mois_abonne' => $today->format('m-Y'),
                'montant' => 0,
                'montant_actuel' => 0,
                'statut' => 'actif',
                'type' => 'standard',
                'mode_paiement' => 'offert',
                'reference_paiement' => 'CREA-' . $owner->code_id,
                'notes' => 'Abonnement créé automatiquement lors de l\'inscription par commercial',
            ];
            Abonnement::create($abonnementData);

            if ($owner->gestion === 'proprietaire') {
                try {
                    ResetCodePasswordProprietaire::where('email', $owner->email)->delete();
                    $code = rand(1000, 4000);
                    ResetCodePasswordProprietaire::create([
                        'code' => $code,
                        'email' => $owner->email,
                    ]);
                    Notification::route('mail', $owner->email)
                        ->notify(new SendEmailToOwnerAfterRegistrationNotification($code, $owner->email, $owner->code_id));
                } catch (\Exception $e) {
                    // Log the error but proceed
                }
            }

            DB::commit();
            return redirect()->route('commercial.proprietaires.index')->with('success', 'Propriétaire enregistré avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

    public function edit($id)
    {
        $proprietaire = Proprietaire::findOrFail($id);
        $pendingVisits = Visite::where('statut', 'en attente')
            ->whereHas('bien', function ($query) {
                $query->whereNull('agence_id');
                $query->whereNull('proprietaire_id')
                    ->orWhereHas('proprietaire', function ($q) {
                        $q->where('gestion', 'agence');
                    });
            })
            ->count();
        return view('commercial.proprietaire.edit', compact('proprietaire', 'pendingVisits'));
    }

    public function update(Request $request, $id)
    {
        $proprietaire = Proprietaire::findOrFail($id);

        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:proprietaires,email,' . $proprietaire->id,
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'pourcentage' => 'nullable|integer|between:1,15',
            'choix_paiement' => 'nullable|in:Virement Bancaire,Chèques',
            'rib' => 'nullable',
            'contrat' => 'nullable|file|mimes:pdf,doc,docx|max:2048',
        ]);

        try {
            $proprietaire->name = $validatedData['name'];
            $proprietaire->prenom = $validatedData['prenom'];
            $proprietaire->email = $validatedData['email'];
            $proprietaire->contact = $validatedData['contact'];
            $proprietaire->commune = $validatedData['commune'];
            if (isset($validatedData['pourcentage'])) $proprietaire->pourcentage = $validatedData['pourcentage'];
            if (isset($validatedData['choix_paiement'])) $proprietaire->choix_paiement = $validatedData['choix_paiement'];
            if ($request->has('rib')) $proprietaire->rib = $validatedData['rib'];

            if ($request->hasFile('contrat')) {
                if ($proprietaire->contrat && Storage::exists($proprietaire->contrat)) {
                    Storage::delete($proprietaire->contrat);
                }
                $proprietaire->contrat = $request->file('contrat')->store('contrats_proprietaires');
            }

            $proprietaire->save();

            return redirect()->route('commercial.proprietaires.index')
                ->with('success', 'Propriétaire mis à jour avec succès.');
        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour.')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            DB::beginTransaction();

            $proprietaire = Proprietaire::findOrFail($id);
            Abonnement::where('proprietaire_id', $proprietaire->code_id)->delete();

            if ($proprietaire->rib) {
                Storage::delete('public/' . $proprietaire->rib);
            }

            $proprietaire->delete();
            DB::commit();

            return redirect()->back()->with('success', 'Propriétaire et ses abonnements supprimés avec succès.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Erreur lors de la suppression : ' . $e->getMessage());
        }
    }
}
