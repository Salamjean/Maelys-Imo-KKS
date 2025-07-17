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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
        
        Log::info('Envoi email à: '.$request->email, [
            'locataire' => $locataire->id,
            'montant' => $nouveauMontant
        ]);
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
    $request->validate([
        'mois_couvert' => 'required|date_format:Y-m',
        'transaction_id' => 'required_if:methode_paiement,mobile_money',
        'proof_file' => 'required_if:methode_paiement,virement|file|mimes:jpg,jpeg,png,pdf|max:2048',
    ]);

    // Générer un transaction_id si absent (pour virement)
    $transaction_id = $request->transaction_id ?? 'VIR_' . Str::random(10);

    // Vérifier si le paiement existe déjà
    $existingPayment = Paiement::where('transaction_id', $transaction_id)->first();
    if ($existingPayment) {
        return redirect()->route('locataire.paiements.index', $locataire)
            ->with('success', 'Paiement déjà enregistré pour ' . Carbon::parse($request->mois_couvert)->translatedFormat('F Y'));
    }

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

    // Gestion du fichier de preuve
    $proofPath = null;
    if ($request->hasFile('proof_file')) {
        $proofPath = $request->file('proof_file')->store('preuves_virements', 'public');
    }

    // Déterminer la méthode et le statut
    $methode = $request->methode_paiement === 'virement' ? 'Virement Bancaire' : 'Mobile Money';
    $statut = $request->methode_paiement === 'virement' ? 'En attente' : 'payé';

    $typePrefix = '';
    switch('methode_paiement') {
        case 'Espèces':
            $typePrefix = 'PAY-';
            break;
        case 'Mobile Money':
            $typePrefix = 'PAY-';
            break;
        case 'Virement Bancaire':
            $typePrefix = 'PAY-';
            break;
        default:
            $typePrefix = 'PAY-'; // Par défaut si aucun cas ne correspond
    }

    do {
        $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $numeroId = $typePrefix . $randomNumber;
    } while (Paiement::where('reference', $numeroId)->exists());

    // Enregistrer le paiement
    $paiement = Paiement::create([
        'montant' => $locataire->bien->montant_majore ?? $locataire->bien->prix,
        'date_paiement' => now(),
        'mois_couvert' => $moisAPayer->format('Y-m'),
        'methode_paiement' => $methode,
        'statut' => $statut,
        'reference' => $numeroId,
        'locataire_id' => $locataire->id,
        'bien_id' => $locataire->bien_id,
        'transaction_id' => $transaction_id,
        'proof_path' => $proofPath,
    ]);

    // Réinitialiser le montant majoré si nécessaire
    if ($locataire->bien->montant_majore) {
        $locataire->bien->update(['montant_majore' => null]);
    }

    return redirect()->route('locataire.paiements.index', $locataire)
        ->with('success', 'Paiement enregistré avec succès pour le mois de '.$moisAPayer->translatedFormat('F Y'));
}

   public function handleCinetPayNotification(Request $request, Locataire $locataire)
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

        // 2. Vérifier si le paiement a réussi
        if ($data['cpm_result'] !== '00') {
            Log::info('Paiement échoué', ['transaction_id' => $data['cpm_trans_id']]);
            return response()->json(['status' => 'error', 'message' => 'Paiement échoué'], 400);
        }

        // 3. Récupérer les données temporaires depuis la session
        $paiementData = session()->get('pending_payment');

        if (!$paiementData || $paiementData['transaction_id'] !== $data['cpm_trans_id']) {
            Log::error('Données de paiement introuvables ou incohérentes');
            return response()->json(['status' => 'error', 'message' => 'Données de paiement introuvables'], 404);
        }

        // 4. Convertir la date de CinetPay
        try {
            $paymentDate = Carbon::createFromFormat('Y-m-d H:i:s', $data['cpm_payment_date']);
        } catch (\Exception $e) {
            $paymentDate = now();
            Log::warning('Format de date invalide, utilisation de la date actuelle', [
                'received_date' => $data['cpm_payment_date'],
                'error' => $e->getMessage()
            ]);
        }
         $typePrefix = '';
    switch('methode_paiement') {
        case 'Espèces':
            $typePrefix = 'PAY-';
            break;
        case 'Mobile Money':
            $typePrefix = 'PAY-';
            break;
        case 'Virement Bancaire':
            $typePrefix = 'PAY-';
            break;
        default:
            $typePrefix = 'PAY-'; // Par défaut si aucun cas ne correspond
    }

    do {
        $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $numeroId = $typePrefix . $randomNumber;
    } while (Paiement::where('reference', $numeroId)->exists());


        // 5. Enregistrer définitivement le paiement
        $paiement = Paiement::create([
            'montant' => $paiementData['montant'],
            'date_paiement' => $paymentDate,
            'mois_couvert' => $paiementData['mois_couvert'],
            'methode_paiement' => 'Mobile Money',
            'statut' => 'payé',
            'reference' => '$numeroId',
            'locataire_id' => $paiementData['locataire_id'],
            'bien_id' => $paiementData['bien_id'],
            'comptable_id' => $paiementData['comptable_id'],
            'transaction_id' => $data['cpm_trans_id']
        ]);

        // 6. Supprimer les données temporaires
        session()->forget('pending_payment');

        // 7. Réinitialiser le montant majoré si nécessaire
        if ($locataire->bien->montant_majore) {
            $locataire->bien->update(['montant_majore' => null]);
        }

        Log::info('Paiement enregistré avec succès', ['paiement_id' => $paiement->id]);

        return response()->json(['status' => 'success', 'message' => 'Paiement enregistré']);

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
        'locataire_id' => 'required|exists:locataires,id',
        'nombre_mois' => 'required|integer|min:1'
    ]);

    $locataire = Locataire::with('bien')->findOrFail($request->locataire_id);
    
    // Générer un code aléatoire de 6 caractères
    $code = Str::upper(Str::random(6));
    
    // Calculer le montant total
    $montantParMois = $locataire->bien->montant_majore ?? $locataire->bien->prix;
    $montantTotal = $montantParMois * $request->nombre_mois;

    // Déterminer les mois couverts
    $moisCouverts = [];
    $dateActuelle = now();
    for ($i = 0; $i < $request->nombre_mois; $i++) {
        $moisCouverts[] = $dateActuelle->copy()->addMonths($i)->format('Y-m');
    }
    $moisCouvertsStr = implode(', ', $moisCouverts);

    // Options du QR Code
    $options = new QROptions([
    'version' => 10, // Version plus grande (1-40, plus le nombre est grand, plus la capacité est grande)
    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel' => QRCode::ECC_L, // Niveau de correction plus bas (L, M, Q, H)
    'scale' => 5,
    'imageBase64' => false,
    'quietzoneSize' => 2,
]);

    // Données à encoder dans le QR code
    $qrData = json_encode([
        'code' => $code,
        'locataire' => $locataire->name, // Utilisez un nom de champ plus court
        'montant' => $montantTotal,     // Montant abrégé
        'exp' => now()->addHours(24)->timestamp // Timestamp au lieu de date complète
    ]);

    // Générer le QR code
    $qrcode = (new QRCode($options))->render($qrData);

    // Sauvegarder le QR code
    $qrCodePath = 'qrcodes/cash_payments/' . $code . '.png';
    Storage::disk('public')->put($qrCodePath, $qrcode);

    // Créer ou mettre à jour le code
    $cashCode = CashVerificationCode::updateOrCreate(
        ['locataire_id' => $locataire->id],
        [
            'code' => $code,
            'expires_at' => now()->addHours(24),
            'nombre_mois' => $request->nombre_mois ?? 1,
            'mois_couverts' => $moisCouvertsStr,
            'montant_total' => $montantTotal,
            'is_archived' => false,
            'used_at' => null,
            'paiement_id' => null,
            'qr_code_path' => $qrCodePath
        ]
    );

    // Envoyer le code et le QR code par email
    try {
        Mail::to($locataire->email)->send(new \App\Mail\CashPaymentCodeMail(
            $code, 
            $locataire,
            $montantTotal,
            $moisCouvertsStr,
            Storage::url($qrCodePath)
        ));
        
        return response()->json([
            'success' => true,
            'message' => 'Le code de vérification a été envoyé par email au locataire.',
            'mois_couverts' => $moisCouvertsStr,
            'montant_total' => $montantTotal,
            'qr_code_url' => Storage::url($qrCodePath),
            'qr_code_base64' => base64_encode($qrcode) // Optionnel: pour affichage immédiat
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Le code a été généré mais l\'envoi par email a échoué.'
        ], 500);
    }
}
    
public function verifyCashCode(Request $request)
{
    $request->validate([
        'locataire_id' => 'required|exists:locataires,id',
        'code' => 'required|string|size:6',
        'nombre_mois' => 'sometimes|integer|min:1'
    ]);

    DB::beginTransaction();

    try {
        $locataire = Locataire::with('bien')->findOrFail($request->locataire_id);
        $nombreMois = $request->nombre_mois ?? 1;

        // Vérifier le code
        $codeValide = CashVerificationCode::where('locataire_id', $locataire->id)
            ->where('code', $request->code)
            ->where('expires_at', '>', now())
            ->whereNull('used_at')
            ->first();

        if (!$codeValide) {
            return response()->json([
                'success' => false,
                'message' => 'Code invalide ou expiré'
            ], 400);
        }

        // Utiliser le nombre de mois du code si disponible
        if ($codeValide->nombre_mois) {
            $nombreMois = $codeValide->nombre_mois;
        }

        // Déterminer le mois de départ
        $dernierPaiement = Paiement::where('locataire_id', $locataire->id)
            ->where('statut', 'payé')
            ->orderBy('mois_couvert', 'desc')
            ->first();

        $dateDebut = $dernierPaiement 
            ? Carbon::parse($dernierPaiement->mois_couvert)->addMonth()
            : now();

        // Préparer les mois à payer
        $moisAPayer = [];
        $moisDejaPayes = [];
        $currentDate = $dateDebut->copy();

        for ($i = 0; $i < $nombreMois; $i++) {
            $moisFormat = $currentDate->format('Y-m');
            
            $paiementExistant = Paiement::where('locataire_id', $locataire->id)
                ->where('mois_couvert', $moisFormat)
                ->where('statut', 'payé')
                ->exists();

            if ($paiementExistant) {
                $moisDejaPayes[] = $currentDate->translatedFormat('F Y');
            } else {
                $moisAPayer[] = [
                    'mois' => $moisFormat,
                    'libelle' => $currentDate->translatedFormat('F Y')
                ];
            }

            $currentDate->addMonth();
        }

        if (empty($moisAPayer)) {
            return response()->json([
                'success' => false,
                'message' => 'Tous les mois sélectionnés ont déjà été payés: ' . implode(', ', $moisDejaPayes)
            ], 400);
        }

        // Montant par mois
        $montantParMois = $locataire->bien->montant_majore ?? $locataire->bien->prix;
        $montantTotal = $montantParMois * count($moisAPayer);

        // Générer une référence de base
        $typePrefix = 'PAY-';
        do {
            $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
            $referenceBase = $typePrefix . $randomNumber;
        } while (Paiement::where('reference', 'like', $referenceBase . '%')->exists());

        // Enregistrement des paiements
        foreach ($moisAPayer as $mois) {
            Paiement::create([
                'montant' => $montantParMois,
                'date_paiement' => now(),
                'mois_couvert' => $mois['mois'],
                'methode_paiement' => 'Espèces',
                'statut' => 'payé',
                'reference' => $referenceBase . '-' . $mois['mois'],
                'locataire_id' => $locataire->id,
                'bien_id' => $locataire->bien_id,
                'verif_espece' => $request->code,
                'nombre_mois' => $nombreMois
            ]);
        }

        // Marquer le code comme utilisé
        $codeValide->update([
            'used_at' => now(),
            'paiement_id' => null, // ou l'ID du premier paiement si vous voulez faire le lien
            'is_archived' => true
        ]);

        // Réinitialiser le montant majoré si nécessaire
        if ($locataire->bien->montant_majore) {
            $locataire->bien->update(['montant_majore' => null]);
        }

        DB::commit();

        // Message de confirmation
        $message = 'Paiement enregistré avec succès pour ' . count($moisAPayer) . ' mois: ' . 
                  implode(', ', array_column($moisAPayer, 'libelle'));
        
        if (!empty($moisDejaPayes)) {
            $message .= ' (Mois déjà payés: ' . implode(', ', $moisDejaPayes) . ')';
        }

        return response()->json([
            'success' => true,
            'message' => $message,
            'montant_total' => $montantTotal,
            'mois_payes' => implode(', ', array_column($moisAPayer, 'libelle')),
            'redirect_url' => redirect()->back()->getTargetUrl()
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        Log::error("Erreur lors de la vérification du code: " . $e->getMessage());
        
        return response()->json([
            'success' => false,
            'message' => 'Une erreur est survenue lors du traitement du paiement'
        ], 500);
    }
}
public function verifyCashCodeComptable(Request $request)
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

     $typePrefix = '';
    switch('methode_paiement') {
        case 'Espèces':
            $typePrefix = 'PAY-';
            break;
        case 'Mobile Money':
            $typePrefix = 'PAY-';
            break;
        case 'Virement Bancaire':
            $typePrefix = 'PAY-';
            break;
        default:
            $typePrefix = 'PAY-'; // Par défaut si aucun cas ne correspond
    }

    do {
        $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $numeroId = $typePrefix . $randomNumber;
    } while (Paiement::where('reference', $numeroId)->exists());


    // Enregistrement du paiement
    $paiement = Paiement::create([
        'montant' => $locataire->bien->montant_majore ?? $locataire->bien->prix,
        'date_paiement' => now(),
        'mois_couvert' => $moisAPayer->format('Y-m'),
        'methode_paiement' => 'Espèces',
        'statut' => 'payé',
        'reference' => $numeroId ,
        'locataire_id' => $locataire->id,
        'bien_id' => $locataire->bien_id,
        'comptable_id' => Auth::guard('comptable')->user()->id, // Assurez-vous que l'agent comptable est authentifié
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
        'redirect_url' => route('accounting.payment', $locataire->id)
    ]);
}
public function verifyCashCodeAgent(Request $request)
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

     $typePrefix = '';
    switch('methode_paiement') {
        case 'Espèces':
            $typePrefix = 'PAY-';
            break;
        case 'Mobile Money':
            $typePrefix = 'PAY-';
            break;
        case 'Virement Bancaire':
            $typePrefix = 'PAY-';
            break;
        default:
            $typePrefix = 'PAY-'; // Par défaut si aucun cas ne correspond
    }

    do {
        $randomNumber = str_pad(mt_rand(0, 99999), 5, '0', STR_PAD_LEFT);
        $numeroId = $typePrefix . $randomNumber;
    } while (Paiement::where('reference', $numeroId)->exists());


    // Enregistrement du paiement
    $paiement = Paiement::create([
        'montant' => $locataire->bien->montant_majore ?? $locataire->bien->prix,
        'date_paiement' => now(),
        'mois_couvert' => $moisAPayer->format('Y-m'),
        'methode_paiement' => 'Espèces',
        'statut' => 'payé',
        'reference' => $numeroId,
        'locataire_id' => $locataire->id,
        'bien_id' => $locataire->bien_id,
        'comptable_id' => Auth::guard('comptable')->user()->id, // Assurez-vous que l'agent comptable est authentifié
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
        'redirect_url' => route('accounting.agent.paid')
    ]);
}

public function generateReceipt( Paiement $paiement)
{
    $paiement = Paiement::with(['bien.locataire', 'bien.agence', 'bien.proprietaire'])->find($paiement->id);
    \Carbon\Carbon::setLocale('fr');

    $locataire = Auth::guard('locataire')->user() ?? $paiement->locataire;
    // 1. Contenu du QR Code formaté de manière lisible
    $qrContent = "QUITTANCE DE LOYER\n";
    $qrContent .= "---------------\n";
    $qrContent .= "Locataire: {$paiement->bien->locataire->name} {$paiement->bien->locataire->prenom}\n";
    $qrContent .= "Loyer : ".number_format($paiement->montant, 0, ',', ' ')." FCFA\n";
    $qrContent .= "Mois couvert: ".\Carbon\Carbon::parse($paiement->mois_couvert)->format('m/Y')."\n";
    $qrContent .= "Date de paiement : ".\Carbon\Carbon::parse($paiement->date_paiement)->format('d/m/Y')."\n";
    $qrContent .= "Méthode de paiement: {$paiement->methode_paiement}\n";
    if ($paiement->bien->agence_id) {
    $qrContent .= "Agence: ".($paiement->bien->agence->name ?? 'Maelys-imo')."\n";
    } elseif ($paiement->bien->proprietaire_id) {
        if ($paiement->bien->proprietaire->gestion == 'agence') {
            $qrContent .= "Agence: Maelys-imo\n";
        } else {
            $qrContent .= "Propriétaire: ".($paiement->bien->proprietaire->name.' '.$paiement->bien->proprietaire->prenom ?? 'Maelys-imo')."\n";
        }
    } else {
        $qrContent .= "Agence: Maelys-imo\n";
    }
    $qrContent .= "Date d'emission : ".\Carbon\Carbon::parse($paiement->create_at)->format('d/m/Y');

    // 2. Configuration du QR Code
    $options = new QROptions([
        'version'    => 10,
        'outputType' => QRCode::OUTPUT_IMAGE_PNG,
        'eccLevel'   => QRCode::ECC_L,
        'scale'      => 5,
    ]);

    // ... le reste de votre méthode reste identique ...
    $qrCode = (new QRCode($options))->render($qrContent);

    // Sauvegarde et génération du PDF
    $qrCodeFileName = 'qrcode_paiement_' . $paiement->id . '.png';
    $qrCodePath = 'public/paiements/qrcodes/' . $qrCodeFileName;
    Storage::put($qrCodePath, $qrCode);

    $data = [
        'paiement'      => $paiement,
        'locataire'     => $locataire,
        'bien'          => $paiement->bien,
        'date_emission' => now()->format('d/m/Y'),
        'reference'     => 'REC-' . strtoupper(Str::random(8)) . '-' . $paiement->id,
        'qrCode'        => $qrCode,
        'qrCodePath'    => Storage::url($qrCodePath),
    ];

    return Pdf::loadView('locataire.paiements.receipt', $data)
              ->stream('Quittance de loyer-' . $paiement->id . '.pdf');
}

public function getMontantLoyer(Request $request)
{
    $locataire = Locataire::findOrFail($request->locataire_id);
    // Supposons que le montant du loyer est stocké dans une relation Contrat
    $montant = $locataire->contrat->montant_loyer ?? 0;
    
    return response()->json(['montant' => $montant]);
}
}