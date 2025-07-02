<?php

namespace App\Http\Controllers\Proprietaire;
use App\Http\Controllers\Controller;
use App\Models\Comptable;
use App\Models\ResetCodePasswordComptable;
use App\Models\Visite;
use App\Notifications\SendEmailToComptableAfterRegistrationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

class OwnerComptableController extends Controller
{
    public function index(){
          $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        $comptables = Comptable::where('proprietaire_id', $ownerId)->paginate(6);
        return view('proprietaire.comptable.index', compact('comptables', 'pendingVisits'));
    }
    
    public function create(){
          $ownerId = Auth::guard('owner')->user()->code_id;
     // Demandes de visite en attente
       $pendingVisits = Visite::where('statut', 'en attente')->where('statut', '!=', 'effectuée')
                        ->where('statut', '!=', 'annulée')
                        ->whereHas('bien', function ($query) use ($ownerId) {
                             $query->where('proprietaire_id', $ownerId);  // Filtrer par l'ID de l'agence
                        })
                        ->count();
        return view('proprietaire.comptable.create', compact('pendingVisits'));
    }

    public function store(Request $request)
    {
        // Validation des données
        $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:comptables,email',
            'contact' => 'required|string|min:10',
            'commune' => 'required|string|max:255',
            'user_type' =>'required',
            'date_naissance' => 'required|max:255',
        ],[
            'name.required' => 'Le nom du comptable est obligatoire.',
            'prenom.required' => 'Le prénom du comptable est obligatoire.',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'L\'adresse e-mail n\'est pas valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'contact.required' => 'Le contact est obligatoire.',
            'contact.min' => 'Le contact doit avoir au moins 10 chiffres.',
            'commune.required' => 'Lieu de residence est obligatoire.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'user_type.required' => 'Le type d\'agent est obligatoire'
        ]);
    
        try {
            $ownerId = Auth::guard('owner')->user()->code_id;
            // Traitement de l'image de profil
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            }
    
            // Création de l'agent
            $comptable = new Comptable();
            $comptable->name = $request->name;
            $comptable->prenom = $request->prenom;
            $comptable->email = $request->email;
            $comptable->contact = $request->contact;
            $comptable->commune = $request->commune;
            $comptable->date_naissance = $request->date_naissance;
            $comptable->password = Hash::make('password');
            $comptable->user_type = $request->user_type;
            $comptable->profile_image = $profileImagePath;
            $comptable->proprietaire_id = $ownerId;
            $comptable->save();
    
            // Envoi de l'e-mail de vérification
            ResetCodePasswordComptable::where('email', $comptable->email)->delete();
            $code = rand(1000, 4000);
            ResetCodePasswordComptable::create([
                'code' => $code,
                'email' => $comptable->email,
            ]);

            Notification::route('mail', $comptable->email)
                ->notify(new SendEmailToComptableAfterRegistrationNotification($code, $comptable->email));
        
            return redirect()->route('accounting.index.owner')
                ->with('success', 'Agent enregistrée avec succès.');
    
        } catch (\Exception $e) {
            Log::error('Error creating Agent: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Une erreur est survenue : ' . $e->getMessage()])->withInput();
        }
    }

      public function destroy($id)
    {
        try {
            $comptable = Comptable::findOrFail($id);
            
            // Supprimer le RIB si existant
            if ($comptable->rib) {
                Storage::delete('public/' . $comptable->rib);
            }
            
            $comptable->delete();
            
            return redirect()->back()->with('success', 'Agent supprimé avec succès.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Erreur lors de la suppression du comptable.');
        }
    }
}
