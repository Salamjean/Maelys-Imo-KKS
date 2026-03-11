<?php

namespace App\Http\Controllers;

use App\Models\Commercial;
use App\Models\Visite;
use App\Models\ResetCodePasswordCommercial;
use App\Notifications\SendEmailToCommercialAfterRegistrationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use PDF;

class AdminCommercialController extends Controller
{
    public function index()
    {
        $commercials = Commercial::paginate(6);
        $pendingVisits = $this->getPendingVisitsCount();
        return view('admin.commercial.index', compact('commercials', 'pendingVisits'));
    }

    public function create()
    {
        $pendingVisits = $this->getPendingVisitsCount();
        return view('admin.commercial.create', compact('pendingVisits'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:commercials,email',
            'contact' => 'required|string|min:10|unique:commercials,contact',
            'commune' => 'required|string|max:255',
            'date_naissance' => 'required|date',
        ]);

        try {
            $profileImagePath = null;
            if ($request->hasFile('profile_image')) {
                $profileImagePath = $request->file('profile_image')->store('profile_images', 'public');
            }

            $commercial = new Commercial();
            $commercial->name = $request->name;
            $commercial->code_id = $this->generateUniqueCodeId();
            $commercial->prenom = $request->prenom;
            $commercial->email = $request->email;
            $commercial->contact = $request->contact;
            $commercial->commune = $request->commune;
            $commercial->date_naissance = $request->date_naissance;
            $commercial->password = Hash::make('password');
            $commercial->profile_image = $profileImagePath;
            $commercial->save();

            // Envoi de l'e-mail de vérification
            ResetCodePasswordCommercial::where('email', $commercial->email)->delete();
            $code = rand(1000, 4000);
            ResetCodePasswordCommercial::create([
                'code' => $code,
                'email' => $commercial->email,
            ]);

            Notification::route('mail', $commercial->email)
                ->notify(new SendEmailToCommercialAfterRegistrationNotification($code, $commercial->email));

            return redirect()->route('admin.commercial.index')->with('success', 'Commercial créé avec succès et email envoyé.');
        } catch (\Exception $e) {
            Log::error('Error creating commercial: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la création.')->withInput();
        }
    }

    public function edit($id)
    {
        $commercial = Commercial::findOrFail($id);
        $pendingVisits = $this->getPendingVisitsCount();
        return view('admin.commercial.edit', compact('commercial', 'pendingVisits'));
    }

    public function update(Request $request, $id)
    {
        $commercial = Commercial::findOrFail($id);
        $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'email' => 'required|email|unique:commercials,email,' . $commercial->id,
            'contact' => 'required|string|min:10|unique:commercials,contact,' . $commercial->id,
            'commune' => 'required|string|max:255',
            'date_naissance' => 'required|date',
        ]);

        try {
            if ($request->hasFile('profile_image')) {
                if ($commercial->profile_image) {
                    Storage::disk('public')->delete($commercial->profile_image);
                }
                $commercial->profile_image = $request->file('profile_image')->store('profile_images', 'public');
            }

            $commercial->name = $request->name;
            $commercial->prenom = $request->prenom;
            $commercial->email = $request->email;
            $commercial->contact = $request->contact;
            $commercial->commune = $request->commune;
            $commercial->date_naissance = $request->date_naissance;
            $commercial->save();

            return redirect()->route('admin.commercial.index')->with('success', 'Commercial mis à jour avec succès.');
        } catch (\Exception $e) {
            Log::error('Error updating commercial: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la mise à jour.')->withInput();
        }
    }

    public function destroy($id)
    {
        try {
            $commercial = Commercial::findOrFail($id);
            if ($commercial->profile_image) {
                Storage::disk('public')->delete($commercial->profile_image);
            }
            $commercial->delete();
            return redirect()->route('admin.commercial.index')->with('success', 'Commercial supprimé avec succès.');
        } catch (\Exception $e) {
            Log::error('Error deleting commercial: ' . $e->getMessage());
            return back()->with('error', 'Erreur lors de la suppression.');
        }
    }

    public function toggleStatus($id)
    {
        try {
            $commercial = Commercial::findOrFail($id);
            $commercial->is_active = !$commercial->is_active;
            $commercial->save();

            $status = $commercial->is_active ? 'activé' : 'désactivé';
            return redirect()->route('admin.commercial.index')->with('success', "Compte commercial $status avec succès.");
        } catch (\Exception $e) {
            Log::error('Error toggling commercial status: ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors du changement de statut.');
        }
    }

    public function statistics(Request $request)
    {
        $commercials = collect();
        $isSearch = false;

        if ($request->has('search') && !empty($request->search)) {
            $isSearch = true;
            $search = $request->search;
            $commercials = Commercial::where(function($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('prenom', 'like', '%' . $search . '%')
                  ->orWhere('code_id', 'like', '%' . $search . '%')
                  ->orWhere('email', 'like', '%' . $search . '%');
            })->get();
        }

        $pendingVisits = $this->getPendingVisitsCount();
        return view('admin.commercial.statistics', compact('commercials', 'pendingVisits', 'isSearch'));
    }

    public function showStatistics($id)
    {
        \Carbon\Carbon::setLocale('fr');
        $commercial = Commercial::findOrFail($id);
        $today = \Carbon\Carbon::today();

        // Statistiques du jour
        $dailyAgences = \App\Models\Agence::where('commercial_id', $commercial->code_id)->whereDate('created_at', $today)->count();
        $dailyProprietaires = \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)->whereDate('created_at', $today)->count();
        $dailyBiens = \App\Models\Bien::where('commercial_id', $commercial->code_id)->whereDate('created_at', $today)->count();

        // Statistiques globales
        $totalAgences = \App\Models\Agence::where('commercial_id', $commercial->code_id)->count();
        $totalProprietaires = \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)->count();
        $totalBiens = \App\Models\Bien::where('commercial_id', $commercial->code_id)->count();

        // Historique journalier (7 derniers jours)
        $history = [];
        for ($i = 0; $i < 7; $i++) {
            $date = \Carbon\Carbon::today()->subDays($i);
            $history[] = [
                'date' => $date->translatedFormat('d F Y'),
                'agences' => \App\Models\Agence::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count(),
                'proprietaires' => \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count(),
                'biens' => \App\Models\Bien::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count(),
            ];
        }

        // Statistiques hebdomadaires (pour le graphique)
        $stats = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = \Carbon\Carbon::today()->subDays($i);
            $stats['labels'][] = $date->translatedFormat('D d M');
            $stats['agences'][] = \App\Models\Agence::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count();
            $stats['proprietaires'][] = \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count();
            $stats['biens'][] = \App\Models\Bien::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count();
        }

        $pendingVisits = $this->getPendingVisitsCount();

        return view('admin.commercial.statistics_show', compact(
            'commercial',
            'dailyAgences',
            'dailyProprietaires',
            'dailyBiens',
            'totalAgences',
            'totalProprietaires',
            'totalBiens',
            'history',
            'stats',
            'pendingVisits'
        ));
    }

    public function exportStatisticsPDF($id)
    {
        \Carbon\Carbon::setLocale('fr');
        $commercial = Commercial::findOrFail($id);
        
        // Données globales
        $totalAgences = \App\Models\Agence::where('commercial_id', $commercial->code_id)->count();
        $totalProprietaires = \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)->count();
        $totalBiens = \App\Models\Bien::where('commercial_id', $commercial->code_id)->count();

        // Historique 30 jours
        $history = [];
        for ($i = 0; $i < 30; $i++) {
            $date = \Carbon\Carbon::today()->subDays($i);
            $agences = \App\Models\Agence::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count();
            $proprietaires = \App\Models\Proprietaire::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count();
            $biens = \App\Models\Bien::where('commercial_id', $commercial->code_id)->whereDate('created_at', $date)->count();

            if ($agences > 0 || $proprietaires > 0 || $biens > 0) {
                $history[] = [
                    'date' => $date->translatedFormat('d/m/Y'),
                    'agences' => $agences,
                    'proprietaires' => $proprietaires,
                    'biens' => $biens,
                ];
            }
        }

        $pdf = \PDF::loadView('commercial.statistics_pdf', compact(
            'commercial',
            'totalAgences',
            'totalProprietaires',
            'totalBiens',
            'history'
        ));

        return $pdf->download('rapport-activite-' . $commercial->name . '-' . now()->format('d-m-Y') . '.pdf');
    }

    private function generateUniqueCodeId()
    {
        do {
            $code = 'COM' . str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Commercial::where('code_id', $code)->exists());

        return $code;
    }

    private function getPendingVisitsCount()
    {
        return Visite::where('statut', 'en attente')
            ->whereHas('bien', function ($query) {
                $query->whereNull('agence_id')
                    ->whereNull('proprietaire_id')
                    ->orWhereHas('proprietaire', function ($q) {
                        $q->where('gestion', 'agence');
                    });
            })
            ->count();
    }
}
