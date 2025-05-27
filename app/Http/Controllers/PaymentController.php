<?php

namespace App\Http\Controllers;

use App\Mail\CashPaymentCodeMail;
use App\Mail\PaymentReminderMail;
use App\Models\Bien;
use App\Models\CashVerificationCode;
use App\Models\Locataire;
use App\Models\Paiement;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
        public function sendPaymentReminder(Request $request)
    {
        $request->validate([
            'locataire_id' => 'required|exists:locataires,id',
            'email' => 'required|email',
            'taux_majoration' => 'nullable|numeric|min:0|max:100'
        ]);

        $locataire = Locataire::with('bien')->findOrFail($request->locataire_id);
        
        // Calcul du nouveau montant avec majoration
        $montantLoyer = $locataire->bien->prix ?? 0;
        $tauxMajoration = $request->taux_majoration ?? 0;
        $nouveauMontant = $montantLoyer * (1 + $tauxMajoration / 100);
        
        // Mise à jour du montant majoré dans la table biens avec save()
        $bien = $locataire->bien->fresh();
        $bien->montant_majore = $nouveauMontant;
        $bien->save();
        
        // Envoi de l'email avec les nouvelles informations
        Mail::to($request->email)->send(new PaymentReminderMail($locataire, $nouveauMontant, $tauxMajoration));
        
        return response()->json([
            'success' => true,
            'message' => 'Le rappel de paiement a été envoyé avec succès',
            'nouveau_montant' => $nouveauMontant
        ]);
    }

    public function index($locataireId)
    {
        Carbon::setLocale('fr');
        $locataire = Locataire::with(['paiements', 'bien'])->findOrFail($locataireId);
        return view('locataire.paiements.index', compact('locataire'));
    }

    public function create(Locataire $locataire)
    {
        Carbon::setLocale('fr');
        // Trouver le dernier mois payé
        $dernierPaiement = Paiement::where('locataire_id', $locataire->id)
            ->where('statut', 'payé')
            ->orderBy('mois_couvert', 'desc')
            ->first();

        // Déterminer le mois à payer
        $moisAPayer = $dernierPaiement 
            ? Carbon::parse($dernierPaiement->mois_couvert)->addMonth()
            : now();

        // Vérifier si le mois à payer n'a pas déjà été payé
        $paiementExistant = Paiement::where('locataire_id', $locataire->id)
            ->where('mois_couvert', $moisAPayer->format('Y-m'))
            ->where('statut', 'payé')
            ->exists();

        if ($paiementExistant) {
            return redirect()->route('locataire.paiements.index', $locataire)
                ->with('error', 'Le loyer pour '.$moisAPayer->translatedFormat('F Y').' a déjà été payé.');
        }

        return view('locataire.paiements.create', [
            'locataire' => $locataire->load('bien'),
            'montant' => $locataire->bien->montant_majore ?? $locataire->bien->prix ,
            'mois_couvert' => $moisAPayer->format('Y-m'),
            'mois_couvert_display' => $moisAPayer->translatedFormat('F Y')
        ]);
    }

 public function store(Request $request, Locataire $locataire)
{
    $rules = [
        'methode_paiement' => 'required|in:Espèces,Mobile Money',
        'mois_couvert' => 'required|date_format:Y-m',
    ];

    // Ajout conditionnel des règles pour verif_espece
    if ($request->methode_paiement === 'Espèces') {
        $rules['verif_espece'] = 'required|string|size:6';
    }

    $request->validate($rules);

    // Déterminer automatiquement le mois à payer
    $dernierPaiement = Paiement::where('locataire_id', $locataire->id)
        ->where('statut', 'payé')
        ->orderBy('mois_couvert', 'desc')
        ->first();

    $moisAPayer = $dernierPaiement 
        ? Carbon::parse($dernierPaiement->mois_couvert)->addMonth()
        : now();

    // Vérifier si ce mois n'a pas déjà été payé
    $paiementExistant = Paiement::where('locataire_id', $locataire->id)
        ->where('mois_couvert', $moisAPayer->format('Y-m'))
        ->where('statut', 'payé')
        ->exists();

    if ($paiementExistant) {
        return back()->with('error', 'Le loyer pour '.$moisAPayer->translatedFormat('F Y').' a déjà été payé.');
    }

    // Traitement spécifique pour les paiements en espèces
    if ($request->methode_paiement === 'Espèces') {
        // Vérifier le code de vérification
        $codeValide = CashVerificationCode::where('locataire_id', $locataire->id)
            ->where('code', $request->verif_espece)
            ->where('expires_at', '>', now())
            ->exists();

        if (!$codeValide) {
            return back()
                ->withErrors(['verif_espece' => 'Code de vérification invalide ou expiré'])
                ->withInput();
        }
    }

    // Création du paiement
    $paiementData = [
        'montant' => $locataire->bien->montant_majore ?? $locataire->bien->prix,
        'date_paiement' => now(),
        'mois_couvert' => $moisAPayer->format('Y-m'),
        'methode_paiement' => $request->methode_paiement,
        'statut' => $request->methode_paiement === 'Espèces' ? 'payé' : 'En attente',
        'locataire_id' => $locataire->id,
        'bien_id' => $locataire->bien_id,
    ];

    // Ajout conditionnel du code de vérification
    if ($request->methode_paiement === 'Espèces') {
        $paiementData['verif_espece'] = $request->verif_espece;
    }

    $paiement = Paiement::create($paiementData);

    // Si paiement en espèces, supprimer le code utilisé
    if ($request->methode_paiement === 'Espèces') {
        CashVerificationCode::where('locataire_id', $locataire->id)
            ->where('code', $request->verif_espece)
            ->delete();
    }

    // Réinitialiser le montant majoré après paiement
    if ($paiement->statut === 'payé') {
        $locataire->bien->update([
            'montant_majore' => null
        ]);
    }

    // Redirection selon la méthode de paiement
    if ($request->methode_paiement === 'Mobile Money') {
        return $this->initierPaiementCinetPay($paiement);
    }

    return redirect()->route('locataire.paiements.index', $locataire)
        ->with('success', 'Paiement en espèces enregistré avec succès pour le mois de '.$moisAPayer->translatedFormat('F Y'));
}

    private function initierPaiementCinetPay(Paiement $paiement)
    {
        $transactionId = 'PAY_' . $paiement->id . '_' . now()->timestamp;
        
        $paiement->update([
            'transaction_id' => $transactionId
        ]);
        
        return view('locataire.paiements.cinetpay', [
            'paiement' => $paiement,
            'transactionId' => $transactionId,
            'apiKey' => config('services.cinetpay.api_key'),
            'siteId' => config('services.cinetpay.site_id'),
            'notify_url' => route('cinetpay.notify'),
            'return_url' => route('locataire.paiements.index', $paiement->locataire_id),
            'cancel_url' => route('locataire.paiements.create', $paiement->locataire_id)
        ]);
    }

    public function handleCinetPayNotification(Request $request)
    {
        Log::info('Notification CinetPay reçue:', $request->all());
    
        try {
            $data = $request->validate([
                'cpm_trans_id' => 'required',
                'cpm_amount' => 'required|numeric',
                'cpm_currency' => 'required',
                'signature' => 'required',
                'cpm_result' => 'required|string',
                'cpm_payment_date' => 'required'
            ]);
    
            // 1. Vérification signature
            $signature = hash_hmac('sha256', 
                $data['cpm_trans_id'].$data['cpm_amount'].$data['cpm_currency'], 
                config('services.cinetpay.api_key')
            );
    
            if (!hash_equals($signature, $data['signature'])) {
                Log::error('Signature invalide', [
                    'received' => $data['signature'],
                    'calculated' => $signature,
                    'data' => $data
                ]);
                return response()->json(['status' => 'error', 'message' => 'Signature invalide'], 400);
            }
    
            // 2. Trouver le paiement avec logging détaillé
            $paiement = Paiement::where('transaction_id', $data['cpm_trans_id'])->first();
    
            if (!$paiement) {
                Log::error('Paiement non trouvé', ['transaction_id' => $data['cpm_trans_id']]);
                return response()->json(['status' => 'error', 'message' => 'Transaction introuvable'], 404);
            }
    
            Log::info('Paiement trouvé:', ['paiement_id' => $paiement->id, 'current_status' => $paiement->statut]);
    
            // 3. Convertir la date de CinetPay en format MySQL
            try {
                $paymentDate = Carbon::createFromFormat('Y-m-d H:i:s', $data['cpm_payment_date']);
            } catch (\Exception $e) {
                $paymentDate = now();
                Log::warning('Format de date invalide, utilisation de la date actuelle', [
                    'received_date' => $data['cpm_payment_date'],
                    'error' => $e->getMessage()
                ]);
            }
    
            // 4. Mise à jour selon le résultat
            $newStatus = ($data['cpm_result'] === '00') ? 'payé' : 'échoué';
            
            $updated = $paiement->update([
                'statut' => $newStatus,
                'date_paiement' => $paymentDate
            ]);
    
            if (!$updated) {
                Log::error('Échec de la mise à jour du paiement', ['paiement_id' => $paiement->id]);
                return response()->json(['status' => 'error', 'message' => 'Échec de la mise à jour'], 500);
            }
    
            Log::info('Paiement mis à jour avec succès', [
                'paiement_id' => $paiement->id,
                'new_status' => $newStatus,
                'payment_date' => $paymentDate
            ]);
    
            return response()->json(['status' => 'success', 'message' => 'Statut mis à jour']);
    
        } catch (\Exception $e) {
            Log::error('Erreur dans handleCinetPayNotification: ' . $e->getMessage(), [
                'exception' => $e,
                'request_data' => $request->all()
            ]);
            return response()->json(['status' => 'error', 'message' => 'Erreur interne'], 500);
        }
    }

    public function checkPaymentStatus(Request $request)
    {
        $request->validate([
            'transaction_id' => 'required'
        ]);

        $paiement = Paiement::where('transaction_id', $request->transaction_id)
                          ->firstOrFail();

        return response()->json([
            'status' => $paiement->statut,
            'paid' => $paiement->statut === 'payé',
            'redirect_url' => route('locataire.paiements.index', $paiement->locataire_id)
        ]);
    }

public function generateCashCode(Request $request)
{
    $request->validate([
        'locataire_id' => 'required|exists:locataires,id'
    ]);

    $locataire = Locataire::with('bien')->findOrFail($request->locataire_id);
    
    // Générer un code aléatoire de 6 caractères
    $code = Str::upper(Str::random(6));
    
    // Créer ou mettre à jour le code
    CashVerificationCode::updateOrCreate(
        ['locataire_id' => $locataire->id],
        [
            'code' => $code,
            'expires_at' => now()->addHours(24)
        ]
    );

    // Envoyer le code par email au locataire
    try {
        Mail::to($locataire->email)->send(new \App\Mail\CashPaymentCodeMail(
            $code, 
            $locataire,
            $montant = $locataire->bien->montant_majore ?? $locataire->bien->prix
        ));
        
        Log::info("Code espèces envoyé à {$locataire->email}: {$code}");
        
        return response()->json([
            'success' => true,
            'message' => 'Le code de vérification a été envoyé par email au locataire.'
        ]);
    } catch (\Exception $e) {
        Log::error("Erreur envoi email code espèces: " . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Le code a été généré mais l\'envoi par email a échoué. Veuillez vérifier l\'email du locataire.'
        ], 500);
    }
}
    
public function verifyCashCode(Request $request)
{
    $request->validate([
        'locataire_id' => 'required|exists:locataires,id',
        'code' => 'required|string|size:6'
    ]);

    $locataire = Locataire::with('bien')->findOrFail($request->locataire_id);

    // Vérifier le code
    $codeValide = CashVerificationCode::where('locataire_id', $locataire->id)
        ->where('code', $request->code)
        ->where('expires_at', '>', now())
        ->first();

    if (!$codeValide) {
        return response()->json([
            'success' => false,
            'message' => 'Code invalide ou expiré'
        ], 400);
    }

    // Déterminer le mois à payer
    $dernierPaiement = Paiement::where('locataire_id', $locataire->id)
        ->where('statut', 'payé')
        ->orderBy('mois_couvert', 'desc')
        ->first();

    $moisAPayer = $dernierPaiement 
        ? Carbon::parse($dernierPaiement->mois_couvert)->addMonth()
        : now();

    // Vérifier si le mois n'a pas déjà été payé
    $paiementExistant = Paiement::where('locataire_id', $locataire->id)
        ->where('mois_couvert', $moisAPayer->format('Y-m'))
        ->where('statut', 'payé')
        ->exists();

    if ($paiementExistant) {
        return response()->json([
            'success' => false,
            'message' => 'Le loyer pour ce mois a déjà été payé'
        ], 400);
    }

    // Enregistrement du paiement
    $paiement = Paiement::create([
        'montant' => $locataire->bien->montant_majore ?? $locataire->bien->prix,
        'date_paiement' => now(),
        'mois_couvert' => $moisAPayer->format('Y-m'),
        'methode_paiement' => 'Espèces',
        'statut' => 'payé',
        'locataire_id' => $locataire->id,
        'bien_id' => $locataire->bien_id,
        'verif_espece' => $request->code
    ]);

    // Supprimer le code utilisé
    $codeValide->delete();

    // Réinitialiser le montant majoré si nécessaire
    if ($locataire->bien->montant_majore) {
        $locataire->bien->update(['montant_majore' => null]);
    }

    return response()->json([
        'success' => true,
        'message' => 'Paiement enregistré avec succès pour ' . $moisAPayer->translatedFormat('F Y'),
        'redirect_url' => route('locataire.index', $locataire->id)
    ]);
}

    public function generateReceipt(Locataire $locataire, Paiement $paiement)
{
    // Vérifier que l'utilisateur a le droit d'accéder à ce reçu
   
    $data = [
        'paiement' => $paiement,
        'locataire' => $paiement->locataire,
        'bien' => $paiement->bien,
        'date_emission' => Carbon::now()->format('d/m/Y'),
        'reference' => 'REC-' . strtoupper(Str::random(8)) . '-' . $paiement->id
    ];

    $pdf = Pdf::loadView('locataire.paiements.receipt', $data);
    
    return $pdf->stream('recu-paiement-' . $paiement->id . '.pdf');
}

public function getMontantLoyer(Request $request)
{
    $locataire = Locataire::findOrFail($request->locataire_id);
    // Supposons que le montant du loyer est stocké dans une relation Contrat
    $montant = $locataire->contrat->montant_loyer ?? 0;
    
    return response()->json(['montant' => $montant]);
}
}