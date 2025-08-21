<?php

use App\Http\Controllers\AbonnementController;
use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\Agence\AgencePasswordResetController;
use App\Http\Controllers\Agence\AgenceController;
use App\Http\Controllers\Agence\AgenceReversementController;
use App\Http\Controllers\Agent\AgentRecouvrementController;
use App\Http\Controllers\BienController;
use App\Http\Controllers\Agent\ComptableController;
use App\Http\Controllers\Agent\ComptablePasswordResetController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\EtatAgentLocataire;
use App\Http\Controllers\EtatLieuController;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\Locataire\EtatLieuController as LocataireEtatLieuController;
use App\Http\Controllers\Locataire\EtatLieuLocataireController;
use App\Http\Controllers\Locataire\LocataireController;
use App\Http\Controllers\Locataire\LocatairePasswordResetController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PaymentManagementController;
use App\Http\Controllers\Proprietaire\AddBienOwnerController;
use App\Http\Controllers\Proprietaire\LocataireOwnerController;
use App\Http\Controllers\Proprietaire\OwnerComptableController;
use App\Http\Controllers\Proprietaire\OwnerPasswordResetController;
use App\Http\Controllers\Proprietaire\ProprietaireController;
use App\Http\Controllers\Proprietaire\ReversementController;
use App\Http\Controllers\RibController;
use App\Http\Controllers\PaymentPartnerController;
use App\Http\Controllers\VerificationCodeController;
use App\Http\Controllers\VersementController;
use App\Http\Controllers\VisiteController;
use App\Models\Agence;
use App\Models\Bien;
use App\Models\Proprietaire;
use Illuminate\Support\Facades\Route;

Route::get('/', function (Request $request) {
    // Initialisation de la requête
    $query = Bien::where('status', 'Disponible');
    
    // Filtres de recherche
    if ($request->has('type') && $request->type != '') {
        $query->where('type', $request->type);
    }
    
    if ($request->has('commune') && $request->commune != '') {
        $query->where('commune', 'like', '%'.$request->commune.'%');
    }
    
    if ($request->has('prix_max') && $request->prix_max != '') {
        $query->where('prix', '<=', $request->prix_max);
    }
    
    // Pagination et tri
    $biens = $query->orderBy('created_at', 'desc')->paginate(30);
    
    // Compteurs par type (sans les filtres pour garder les totaux)
    $appartements = Bien::where('status', 'Disponible')
                        ->where('type', 'Appartement')->count();
    $maisons = Bien::where('status', 'Disponible')
                  ->where('type', 'Maison')->count();
    $terrains = Bien::where('status', 'Disponible')
                   ->where('type', 'Bureau')->count();

     // Récupération des 10 derniers partenaires (propriétaires + agences)
    $derniersPartenaires = collect()
        ->merge(
            Proprietaire::orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($item) {
                    $item->type = 'Propriétaire';
                    return $item;
                })
        )
        ->merge(
            Agence::orderBy('created_at', 'desc')
                ->take(10)
                ->get()
                ->map(function ($item) {
                    $item->type = 'Agence';
                    return $item;
                })
        )
        ->sortByDesc('created_at')
        ->take(6);
    return view('home.accueil', compact('biens', 'appartements', 'maisons', 'terrains','derniersPartenaires'));
})->name('login');

Route::middleware('auth:admin')->prefix('admin')->group(function () {
    Route::get('/dashboard',[AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/logout',[AdminController::class, 'logout'])->name('admin.logout');

    //routes de gestion des biens par l'administrateur
    Route::get('/biens',[BienController::class, 'index'])->name('bien.index');
    Route::get('/biens/rended',[BienController::class, 'rentedAdmin'])->name('bien.rentedAdmin');
    Route::get('/bienscreate',[BienController::class, 'create'])->name('bien.create');
    Route::post('/biens/store',[BienController::class, 'store'])->name('bien.store');
    Route::get('/biens/{bien}/edit', [BienController::class, 'edit'])->name('bien.edit');
    Route::put('/biens/{bien}', [BienController::class, 'update'])->name('bien.update');
    Route::delete('/biens/{bien}', [BienController::class, 'destroy'])->name('bien.destroy');
    Route::put('/biens/{bien}/republier', [BienController::class, 'republier'])->name('bien.republier.admin');
    Route::get('/biens-disponibles', [BienController::class, 'getBiensDisponibles'])->name('biens.disponibles');
    Route::post('/admin/locataires/{locataire}/attribuer-bien', [BienController::class, 'attribuerBien'])->name('locataire.attribuer-bien');


    //routes de gestion des agences partenaires par l'administrateur
    Route::get('/agences/index',[AgenceController::class, 'index'])->name('agence.index');
    Route::get('/agencescreate',[AgenceController::class, 'create'])->name('agence.create');
    Route::post('/agences/store',[AgenceController::class, 'store'])->name('agence.store');
    Route::get('/agences/{agence}/edit', [AgenceController::class, 'edit'])->name('agence.edit');
    Route::put('/agences/{agence}', [AgenceController::class, 'update'])->name('agence.update');
    Route::delete('/admin/agences/{id}', [AgenceController::class, 'destroy'])->name('agence.destroy');

     //routes de gestion des locataires par l'administrateur
     Route::get('/locataires',[LocataireController::class, 'indexAdmin'])->name('locataire.admin.index');
     Route::get('/locataires/not/serieux',[LocataireController::class, 'indexSerieuxAdmin'])->name('locataire.admin.indexSerieux');
     Route::get('/locatairescreate',[LocataireController::class, 'createAdmin'])->name('locataire.admin.create');
     Route::post('/locataires/store',[LocataireController::class, 'storeAdmin'])->name('locataire.admin.store');
     Route::put('/locataires/{locataire}/status', [LocataireController::class, 'updateStatusAdmin'])->name('locataire.admin.updateStatus');
     Route::get('/locataires/{locataire}/edit', [LocataireController::class, 'editAdmin'])->name('locataire.admin.edit');
    Route::put('/locataires/{locataire}', [LocataireController::class, 'updateAdmin'])->name('locataire.admin.update');

    // Routes pour la gestion des états des lieux par l'administrateur
    Route::get('/locataires/{locataire}/etat', [EtatLieuController::class, 'etatAdmin'])->name('locataire.admin.etat');
    Route::post('/locataires/{locataire}/etat', [EtatLieuController::class, 'storeAdmin'])->name('locataire.admin.etatstore');
    
    // Route pour récupérer les agents de recouvrement
    Route::get('/comptables/recouvrement', [EtatLieuController::class, 'getAgentsRecouvrementAdmin'])->name('comptables.recouvrement.admin');
    // Route pour attribuer un agent à un locataire
    Route::post('/locataire/assign-comptable', [EtatLieuController::class, 'assignComptableAdmin'])->name('locataire.assign.comptable.admin');

    // Routes pour la gestion des visites par l'administrateur
    Route::prefix('visit')->group(function(){
            Route::get('/visites',[VisiteController::class, 'adminIndex'])->name('visite.index');
            Route::get('/visites/done',[VisiteController::class, 'doneAdmin'])->name('visite.done');
            Route::post('/{visite}/confirm', [VisiteController::class, 'adminConfirm'])->name('visites.confirm');
            Route::post('/{visite}/done', [VisiteController::class, 'adminMarkAsDone'])->name('visites.done');
            Route::post('/{visite}/cancel', [VisiteController::class, 'adminCancel'])->name('visites.cancel');
            Route::get('/{visite}', [VisiteController::class, 'adminShow'])->name('visites.show');
            Route::post('/visites/{visite}/update-date', [VisiteController::class, 'updateDateAdmin'])->name('visites.updateDate.admin');
            Route::get('/visites/list',[VisiteController::class, 'allVisit'])->name('visit.list');
        });

    //routes de gestion des comptables par l'administrateur
    Route::prefix('accounting')->group(function(){
        Route::get('/',[ComptableController::class,'indexAdmin'])->name('accounting.index.admin');
        Route::get('/accountingcreate',[ComptableController::class,'createAdmin'])->name('accounting.create.admin');
        Route::post('/create',[ComptableController::class,'storeAdmin'])->name('accounting.store.admin');
        Route::get('/edit/{comptable}',[ComptableController::class,'editAdmin'])->name('accounting.edit.admin');
        Route::put('/{id}', [ComptableController::class, 'updateAdmin'])->name('accounting.update.admin');
        Route::delete('/admin/accounting/{id}', [ComptableController::class, 'destroy'])->name('accounting.destroy');
    });

    //Les routes de gestion de paiments 
    Route::prefix('payment')->group(function(){
        Route::get('/management',[PaymentManagementController::class, 'indexAdmin'])->name('payment.management.admin');
        Route::post('/validate', [PaymentManagementController::class, 'validatePaymentAdmin'])->name('paiements.validate.admin');
    });

    //routes de gestion des propriétaires par l'administrateur
    Route::prefix('owner')->group(function(){
        Route::get('/',[ProprietaireController::class,'indexAdmin'])->name('owner.index.admin');
        Route::get('/ownercreate',[ProprietaireController::class,'createAdmin'])->name('owner.create.admin');
        Route::post('/create',[ProprietaireController::class,'storeAdmin'])->name('owner.store.admin');
        Route::get('/edit/{proprietaire}/owner',[ProprietaireController::class,'editAdmin'])->name('owner.edit.admin');
        Route::delete('/admin/owner/{id}', [ProprietaireController::class, 'destroyAdmin'])->name('owner.destroy.admin');
        Route::put('/{id}', [ProprietaireController::class, 'updateAdmin'])->name('owner.update.admin');
        Route::get('/reversement',[ReversementController::class, 'reversementAdmin'])->name('reversement.index.admin');
        Route::get('/reversement/completed',[ReversementController::class, 'reversementEffectue'])->name('reversement.completed.admin');
        Route::post('/reversements/{id}/upload-recu', [ReversementController::class, 'uploadRecu'])->name('reversement.upload-recu');
    });

    //routes de gestion des abonnements par l'administrateur
    Route::get('/abonnement/actif',[AbonnementController::class, 'abonneActif'])->name('admin.abonnement.actif');
    Route::get('/abonnement/inactif', [AbonnementController::class, 'abonneInactif'])->name('admin.abonnement.inactif');
    Route::post('/abonnements/activate', [AbonnementController::class, 'activate'])->name('abonnements.activate');
    Route::post('/abonnements/deactivate', [AbonnementController::class, 'deactivate'])->name('abonnements.deactivate');
    Route::post('/abonnements/extend', [AbonnementController::class, 'extend'])->name('abonnements.extend');
    Route::post('/abonnements/reduce', [AbonnementController::class, 'reduce'])->name('abonnements.reduce');

    //Locataire qui a demenager 
    Route::get('/move/out',[AdminController::class, 'move'])->name('admin.tenant.move');
    
});

Route::get('/abonnements/pdf/{id}', [AbonnementController::class, 'generatePDF'])->name('abonnements.pdf');
//routes de gestion des agences
Route::middleware('auth:agence')->prefix('agence')->group(function () {
    Route::get('/dashboard',[AgenceController::class, 'dashboard'])->name('agence.dashboard');
    Route::get('/logout',[AgenceController::class, 'logout'])->name('agence.logout');
    Route::get('/agences/profile/edit', [AgenceController::class, 'editProfile'])->name('agence.edit.profile');
    Route::put('/agences/profile/edit', [AgenceController::class, 'updateProfile'])->name('agence.update.profile');
    Route::get('/visites',[VisiteController::class, 'index'])->name('visite.index');

    //routes de gestion des biens par l'agence
    Route::get('/biens',[BienController::class, 'indexAgence'])->name('bien.index.agence');
    Route::get('/bienscreate',[BienController::class, 'createAgence'])->name('bien.create.agence');
    Route::post('/biens/store',[BienController::class, 'storeAgence'])->name('bien.store.agence');
    Route::get('/biens/rented',[BienController::class, 'rented'])->name('bien.rented');
    Route::get('/biens/{bien}/edit', [BienController::class, 'editAgence'])->name('bien.edit.agence');
    Route::put('/biens/{bien}', [BienController::class, 'updateAgence'])->name('bien.update.agence');
    Route::delete('/biens/{bien}', [BienController::class, 'destroyAgence'])->name('bien.destroy.agence');
    Route::put('/biens/{bien}/republier', [BienController::class, 'republierAgence'])->name('bien.republier.agence');
     Route::get('/biens-disponibles', [BienController::class, 'getBiensDisponiblesAgence'])->name('biens.disponibles.agence');
    Route::post('/admin/locataires/{locataire}/attribuer-bien', [BienController::class, 'attribuerBienAgence'])->name('locataire.attribuer-bien.agence');

    //routes de gestion des visites par l'agence
    Route::prefix('visites')->group(function() {
        Route::get('/',[VisiteController::class, 'indexAgence'])->name('visite.index.agence');
        Route::post('/{visite}/confirm', [VisiteController::class, 'confirm'])->name('visites.confirm.agence');
        Route::post('/{visite}/done', [VisiteController::class, 'markAsDone'])->name('visites.done.agence');
        Route::post('/{visite}/cancel', [VisiteController::class, 'cancel'])->name('visites.cancel.agence');
        Route::get('/{visite}', [VisiteController::class, 'show'])->name('visites.show.agence');
        Route::get('/done/end', [VisiteController::class, 'done'])->name('visite.done.agence');
        Route::post('/visites/{visite}/update-date', [VisiteController::class, 'updateDate'])->name('visites.updateDate');
    });

    //routes de gestion des locataires par l'agence
    Route::get('/locataires',[LocataireController::class, 'index'])->name('locataire.index');
    Route::get('/locataires/not/serieux',[LocataireController::class, 'indexSerieux'])->name('locataire.indexSerieux');
    Route::get('/locatairescreate',[LocataireController::class, 'create'])->name('locataire.create');
    Route::post('/locataires/store',[LocataireController::class, 'store'])->name('locataire.store');
    Route::put('/locataires/{locataire}/status', [LocataireController::class, 'updateStatus'])->name('locataires.updateStatus');
    Route::get('/locataires/{locataire}/edit', [LocataireController::class, 'edit'])->name('locataire.edit');
    Route::put('/locataires/{locataire}', [LocataireController::class, 'update'])->name('locataire.update');

    // Route pour récupérer les agents de recouvrement
    Route::get('/comptables/recouvrement', [EtatLieuController::class, 'getAgentsRecouvrement'])->name('comptables.recouvrement');
    // Route pour attribuer un agent à un locataire
    Route::post('/locataire/assign-comptable', [EtatLieuController::class, 'assignComptable'])->name('locataire.assign.comptable');

    // Routes pour la gestion des états des lieux par l'agence 
    Route::get('/locataires/{locataire}/etat', [EtatLieuController::class, 'etat'])->name('locataire.etat');
    Route::post('/locataires/{locataire}/etat', [EtatLieuController::class, 'store'])->name('locataire.etatstore');


    Route::prefix('accounting')->group(function(){
        Route::get('/',[ComptableController::class,'index'])->name('accounting.index');
        Route::get('/accountingcreate',[ComptableController::class,'create'])->name('accounting.create');
        Route::post('/create',[ComptableController::class,'store'])->name('accounting.store');
        Route::get('/edit/{comptable}',[ComptableController::class,'edit'])->name('accounting.edit');
        Route::put('/{id}', [ComptableController::class, 'update'])->name('accounting.update');
        Route::delete('/agence/accounting/{id}', [ComptableController::class, 'destroyAgence'])->name('accounting.destroy.agence');
    });

    Route::prefix('owner')->group(function(){
        Route::get('/',[ProprietaireController::class,'index'])->name('owner.index');
        Route::get('/ownercreate',[ProprietaireController::class,'create'])->name('owner.create');
        Route::post('/create',[ProprietaireController::class,'store'])->name('owner.store');
        Route::get('/{proprietaire}/editowner',[ProprietaireController::class,'edit'])->name('owner.edit');
        Route::put('/{id}/edit', [ProprietaireController::class, 'update'])->name('owner.update.owner');
        Route::delete('/admin/owner/{id}', [ProprietaireController::class, 'destroy'])->name('owner.destroy');
    });

    //routes de gestion des abonnements par l'agence
    Route::get('/abonnement/show',[AbonnementController::class, 'abonneShowAgence'])->name('agence.abonnement.show');
    Route::post('/abonnements/renew', [AbonnementController::class, 'renewAgence'])->name('abonnements.renew.agence');

    //routes de paiements de proprietaires par l'agence 
    Route::prefix('partner')->group(function () {
    Route::get('/payment', [PaymentPartnerController::class, 'indexPaymentPartner'])->name('partner.payment.index');
    Route::get('/payment/createpartner', [PaymentPartnerController::class, 'createPaymentPartner'])->name('partner.payment.create');
    Route::get('/payment/form/{proprietaire}', [PaymentPartnerController::class, 'showPaymentForm'])->name('partner.payment.form');
    Route::post('/payment/store', [PaymentPartnerController::class, 'storePayment'])->name('partner.payment.store');
    
    // Routes pour la validation
    Route::get('/payment/validate/{payment}', [PaymentPartnerController::class, 'showValidationForm'])
        ->name('partner.payment.validate.form');
    Route::post('/payment/validate/{payment}', [PaymentPartnerController::class, 'validatePaymentCode'])
        ->name('partner.payment.validate');
});
    //Les routes pour la gestion des ribs du proprietaire 
    Route::prefix('rib')->group(function(){
        Route::get('/createrib',[RibController::class, 'createAgence'])->name('rib.create.agence');
        Route::post('/storerib',[RibController::class, 'storeAgence'])->name('rib.store.agence');
        Route::delete('/ribs/{id}', [RibController::class, 'destroyAgence'])->name('rib.destroy.agence');
    });
    
    //Les routes de gestion de paiments 
    Route::prefix('payment')->group(function(){
        Route::get('/management',[PaymentManagementController::class, 'indexAgence'])->name('payment.management.agence');
        Route::post('/validate', [PaymentManagementController::class, 'validatePayment'])->name('paiements.validate');
    });

    // Routes pour la gestion des reversements du propriétaire
    Route::prefix('reversement')->group(function(){
        Route::get('/', [AgenceReversementController::class, 'index'])->name('reversement.index.agence');
        Route::get('/reversement', [AgenceReversementController::class, 'create'])->name('reversement.create.agence');
        Route::post('/reversement', [AgenceReversementController::class, 'store'])->name('reversement.store.agence');
        Route::get('/reversement/solde', [AgenceReversementController::class, 'getSolde'])->name('reversement.solde.agence');
    });

    Route::get('/move/out',[AgencePasswordResetController::class, 'move'])->name('agence.tenant.move');
});
// Routes pour la gestion des paiements
Route::post('/locataires/send-payment-reminder', [PaymentController::class, 'sendPaymentReminder'])->name('locataires.sendPaymentReminder');
Route::prefix('locataire/{locataire}/paiements')->group(function() {
    Route::get('/', [PaymentController::class, 'index'])->name('locataire.paiements.index');
    Route::get('create', [PaymentController::class, 'create'])->name('locataire.paiements.create');
    Route::post('/', [PaymentController::class, 'store'])->name('locataire.paiements.store');
});

Route::post('paiements/check-status', [PaymentController::class, 'checkPaymentStatus'])
        ->name('locataire.paiements.check-status');
Route::post('/cinetpay/notify', [PaymentController::class, 'handleCinetPayNotification'])
     ->name('cinetpay.notify');

Route::post('/cinetpay/return', [PaymentController::class, 'handleCinetPayReturn'])
     ->name('cinetpay.return');

Route::post('/paiements/verify-cash-code', [PaymentController::class, 'verifyCashCode'])->name('paiements.verifyCashCode');
Route::post('/paiements/verify-cash-code/comptable', [PaymentController::class, 'verifyCashCodeComptable'])->name('paiements.verifyCashCodeComptable');
Route::post('/paiements/verify-cash-code/agent', [PaymentController::class, 'verifyCashCodeAgent'])->name('paiements.verifyCashCodeAgent');
    
// Routes pour la gestion des locataires
Route::middleware('auth:locataire')->prefix('locataire')->group(function () {
    Route::get('/dashboard',[LocataireController::class, 'dashboard'])->name('locataire.dashboard');
    Route::get('/logout',[LocataireController::class, 'logout'])->name('locataire.logout');
    Route::get('locataire/bien/show/{id}', [LocataireController::class, 'show'])->name('locataire.bien.show');
    Route::get('/profile/edit', [LocataireController::class, 'editProfile'])->name('locataire.edit.profile');
    Route::put('/profile/edit', [LocataireController::class, 'updateProfile'])->name('locataire.update.profile');

    Route::get('/etat-lieu', [EtatLieuLocataireController::class, 'etat_lieu'])->name('locataire.etat_lieu');
    Route::get('/etat-lieux/{id}/download', [EtatAgentLocataire::class, 'download'])->name('etat-lieu.agent.download');
    Route::post('/etat-lieu/{id}/confirm-entree', [EtatLieuLocataireController::class, 'confirmEntree'])->name('etat-lieu.confirm-entree');
    Route::post('/etat-lieu/{id}/confirm-sortie', [EtatLieuLocataireController::class, 'confirmSortie'])->name('etat-lieu.confirm-sortie');

});

Route::post('/locataire/envoyer-email-agence', [LocataireController::class, 'sendEmailToAgency'])->name('locataire.sendEmailToAgency');

//routes pour la gestion des comptables 
Route::middleware('auth:comptable')->prefix('accounting')->group(function () {
    Route::get('/dashboard',[ComptableController::class,'dashboard'])->name('accounting.dashboard');
    Route::get('/logout',[ComptableController::class, 'logout'])->name('accounting.logout');
    Route::get('/listes/tenant',[ComptableController::class,'tenant'])->name('accounting.tenant');
    Route::get('/rappel/payment',[ComptableController::class,'payment'])->name('accounting.payment');
    Route::get('/paid',[VersementController::class,'paid'])->name('accounting.paid');
    Route::post('/versements', [VersementController::class, 'store'])->name('versement.store');
    Route::get('/agent/dashboard',[AgentRecouvrementController::class,'dashboard'])->name('accounting.agent.dashboard');
    Route::get('/agent/rappel/payment',[AgentRecouvrementController::class,'payment'])->name('accounting.agent.payment');
    Route::get('/agent/listes/tenant',[AgentRecouvrementController::class,'tenant'])->name('accounting.agent.tenant');
    Route::get('/agent/paid',[AgentRecouvrementController::class,'paid'])->name('accounting.agent.paid');
    Route::get('/agent/history',[AgentRecouvrementController::class,'history'])->name('accounting.agent.history');
    Route::get('/versement/history',[VersementController::class,'history'])->name('accounting.versement.history');
    Route::get('/locataires/{locataire}/generate-code', [AgentRecouvrementController::class, 'showGenerateCodePage'])->name('locataires.generateCodePage');

    //Les routes des etats des lieux 
    Route::get('/current/situation',[EtatAgentLocataire::class,'currentSituation'])->name('accounting.current');
    Route::get('/etat-lieux/create/{locataire_id}', [EtatAgentLocataire::class, 'create'])->name('etat.entree');
    Route::post('/etat-entree', [EtatAgentLocataire::class, 'store'])->name('etat.entree.store');
    Route::get('/etat-lieux/{id}/download', [EtatAgentLocataire::class, 'download'])->name('etat-lieux.download');
    Route::post('/generate-verification-code', [VerificationCodeController::class, 'generateCode'])->name('generate.verification.code');
    Route::post('/verify-code', [VerificationCodeController::class, 'verifyCode'])->name('verify.code');

    Route::get('/etat-lieux/end/{locataire_id}', [EtatAgentLocataire::class, 'sortie'])->name('etat.sortie');
    Route::post('/etat-sortie', [EtatAgentLocataire::class, 'storeSortie'])->name('etat.sortie.store');
    Route::get('/etat-lieux/sortie/{id}/download', [EtatAgentLocataire::class, 'downloadSortie'])->name('etat-lieux.sortie.download');
});
// Routes pour la gestion des propriétaires
Route::middleware('auth:owner')->prefix('owner')->group(function () {
    Route::get('/dashboard',[ProprietaireController::class,'dashboard'])->name('owner.dashboard');
    Route::get('/logout',[ProprietaireController::class, 'logout'])->name('owner.logout');
    Route::get('/profile/edit', [OwnerPasswordResetController::class, 'editProfile'])->name('owner.edit.profile');
    Route::put('/profile/edit', [OwnerPasswordResetController::class, 'updateProfile'])->name('owner.update.profile');

    // Routes pour la gestion des biens par le propriétaire
    Route::prefix('bien')->group(function(){
        Route::get('/bienscreate',[AddBienOwnerController::class, 'create'])->name('bien.create.owner');
        Route::post('/biens/store',[AddBienOwnerController::class, 'store'])->name('bien.store.owner');
        Route::get('/biens',[AddBienOwnerController::class,'bienList'])->name('owner.bienList');
        Route::get('/biens/list/loué',[AddBienOwnerController::class,'bienListLoue'])->name('owner.bienList.loue');
        Route::get('/biens/{bien}/edit', [AddBienOwnerController::class, 'edit'])->name('bien.edit.owner');
        Route::put('/biens/{bien}', [AddBienOwnerController::class, 'update'])->name('bien.update.owner');
        Route::delete('/biens/{bien}', [AddBienOwnerController::class, 'destroy'])->name('bien.destroy.owner');
        Route::put('/biens/{bien}/republier', [AddBienOwnerController::class, 'republier'])->name('bien.republier.owner');
        Route::get('/biens-disponibles', [AddBienOwnerController::class, 'getBiensDisponibles'])->name('biens.disponibles.owner');
        Route::post('/locataires/{locataire}/attribuer-bien', [AddBienOwnerController::class, 'attribuerBien'])->name('locataire.attribuer-bien.owner');
    });

    Route::prefix('manager')->group(function(){
         //routes de gestion des locataires par l'agence
        Route::get('/locataires',[LocataireOwnerController::class, 'index'])->name('locataire.index.owner');
        Route::get('/locataires/not/serieux',[LocataireOwnerController::class, 'indexSerieux'])->name('locataire.indexSerieux.owner');
        Route::get('/locatairescreate',[LocataireOwnerController::class, 'create'])->name('locataire.create.owner');
        Route::post('/locataires/store',[LocataireOwnerController::class, 'store'])->name('locataire.store.owner');
        Route::put('/locataires/{locataire}/status', [LocataireOwnerController::class, 'updateStatus'])->name('locataires.updateStatus.owner');
        Route::get('/locataires/{locataire}/edit', [LocataireOwnerController::class, 'edit'])->name('locataire.edit.owner');
        Route::put('/locataires/{locataire}', [LocataireOwnerController::class, 'update'])->name('locataire.update.owner');
    });

    // Route pour récupérer les agents de recouvrement
    Route::get('/comptables/recouvrement', [EtatLieuController::class, 'getAgentsRecouvrementOwner'])->name('comptables.recouvrement.owner');
    // Route pour attribuer un agent à un locataire
    Route::post('/locataire/assign-comptable', [EtatLieuController::class, 'assignComptableOwner'])->name('locataire.assign.comptable.owner');

    //Les routes de gestion de paiments 
    Route::prefix('payment')->group(function(){
        Route::get('/management',[PaymentManagementController::class, 'indexOwner'])->name('payment.management.owner');
        Route::post('/validate', [PaymentManagementController::class, 'validatePaymentOwner'])->name('paiements.validate.owner');
    });

    // routes de gestiond des abonnements par le propriétaire
    Route::get('/abonnement/show',[AbonnementController::class, 'abonneShow'])->name('owner.abonnement.show');
    Route::post('/abonnements/renew', [AbonnementController::class, 'renew'])->name('abonnements.renew');
     // Routes pour la gestion des états des lieux par l'agence 
    Route::get('/locataires/{locataire}/etat', [EtatLieuController::class, 'etatOwner'])->name('locataire.etat.owner');
    Route::post('/locataires/{locataire}/etat', [EtatLieuController::class, 'storeOwner'])->name('locataire.etatstore.owner');
    

    Route::prefix('visit')->group(function(){
            Route::get('/visites',[VisiteController::class, 'ownerIndex'])->name('visite.index.owner');
            Route::get('/visites/done',[VisiteController::class, 'ownerDone'])->name('visite.done.owner');
            Route::post('/{visite}/confirm', [VisiteController::class, 'ownerConfirm'])->name('visites.confirm.owner');
            Route::post('/{visite}/done', [VisiteController::class, 'ownerMarkAsDone'])->name('visites.done.owner');
            Route::post('/{visite}/cancel', [VisiteController::class, 'ownerCancel'])->name('visites.cancel.owner');
            Route::get('/{visite}', [VisiteController::class, 'ownerShow'])->name('visites.show.owner');
            Route::post('/visites/{visite}/update-date', [VisiteController::class, 'updateDateOwner'])->name('visites.updateDate.owner');
    });

    // Routes pour la gestion des agents par le propriétaire
     Route::prefix('accounting')->group(function(){
        Route::get('/',[OwnerComptableController::class,'index'])->name('accounting.index.owner');
        Route::get('/accountingcreate',[OwnerComptableController::class,'create'])->name('accounting.create.owner');
        Route::post('/create',[OwnerComptableController::class,'store'])->name('accounting.store.owner');
        Route::get('/edit/{comptable}',[OwnerComptableController::class,'edit'])->name('accounting.edit.owner');
        Route::put('/{id}', [OwnerComptableController::class, 'update'])->name('accounting.update.owner');
        Route::delete('/agence/accounting/{id}', [OwnerComptableController::class, 'destroy'])->name('accounting.destroy.owner');
    });

    //Les routes pour la gestion des ribs du proprietaire 
    Route::prefix('rib')->group(function(){
        Route::get('/createrib',[RibController::class, 'create'])->name('rib.create');
        Route::post('/storerib',[RibController::class, 'store'])->name('rib.store');
        Route::delete('/ribs/{id}', [RibController::class, 'destroy'])->name('rib.destroy');
    });

    // Routes pour la gestion des reversements du propriétaire
    Route::prefix('reversement')->group(function(){
        Route::get('/', [ReversementController::class, 'index'])->name('reversement.index');
        Route::get('/reversement', [ReversementController::class, 'create'])->name('reversement.create');
        Route::post('/reversement', [ReversementController::class, 'store'])->name('reversement.store');
        Route::get('/reversement/solde', [ReversementController::class, 'getSolde'])->name('reversement.solde');
    });

    Route::get('/move/out',[LocataireOwnerController::class, 'move'])->name('owner.tenant.move');
});


// toutes les routes d'authentification pour les différents rôles
//Les routes d'inscription des agences et propriétaires par eux mêmes
Route::prefix('agence/home/register')->group(function () {
    Route::get('/', [HomePageController::class, 'RegisterAgence'])->name('agence.home.register');
    Route::post('/', [HomePageController::class, 'storeAgence'])->name('agence.store.home.register');
});
Route::prefix('owner/home/register')->group(function () {
    Route::get('/', [HomePageController::class, 'RegisterOwner'])->name('owner.home.register');
    Route::post('/', [HomePageController::class, 'storeOwner'])->name('owner.store.home.register');
});



// Routes pour l'authentification de l'administrateur
Route::prefix('admin')->group(function () {
    Route::get('/login',[AdminController::class, 'login'])->name('admin.login');
    Route::post('/login',[AdminController::class, 'authenticate'])->name('admin.authenticate');
});
// Routes pour l'authentification de l'agence
Route::prefix('agence')->group(function () {
    Route::get('/login',[AgenceController::class, 'login'])->name('agence.login');
    Route::post('/login',[AgenceController::class, 'authenticate'])->name('agence.authenticate');

    //Souscription abonnement 
    Route::get('suscribe/login',[ReversementController::class, 'subscribeAgence'])->name('agence.subscribe');
    Route::post('suscribe/login',[ReversementController::class, 'subscribeAuthenticateAgence'])->name('agence.suscribe.authenticate');
});
// Routes pour l'authentification du comptable
Route::prefix('accounting')->group(function () {
    Route::get('/login',[ComptableController::class, 'login'])->name('comptable.login');
    Route::post('/login',[ComptableController::class, 'authenticate'])->name('comptable.authenticate');
});
// Routes pour l'authentification du locataire
Route::prefix('locataire')->group(function () {
    Route::get('/login',[LocataireController::class, 'login'])->name('locataire.login');
    Route::post('/login',[LocataireController::class, 'authenticate'])->name('locataire.authenticate');
});
// Routes pour l'authentification du propriétaire
Route::prefix('owner')->group(function () {
    Route::get('/login',[ProprietaireController::class, 'login'])->name('owner.login');
    Route::post('/login',[ProprietaireController::class, 'authenticate'])->name('owner.authenticate');

    //Souscription abonnement 
    Route::get('suscribe/login',[ReversementController::class, 'subscribe'])->name('owner.subscribe');
    Route::post('suscribe/login',[ReversementController::class, 'subscribeAuthenticate'])->name('owner.suscribe.authenticate');

});


//routes de paiement
Route::post('/paiements/generate-cash-code', [PaymentController::class, 'generateCashCode'])->name('paiements.generateCashCode');
Route::post('/paiements/verify-cash-code', [PaymentController::class, 'verifyCashCode'])->name('paiements.verifyCashCode');
Route::post('/payment/check-status', [PaymentController::class, 'checkPaymentStatus'])->name('payment.check-status');
Route::get('/paiements/{paiement}/receipt', [PaymentController::class, 'generateReceipt'])->name('locataire.paiements.receipt');
Route::get('/locataires/get-montant-loyer', [PaymentController::class, 'getMontantLoyer'])->name('locataires.getMontantLoyer');


//routes de gestions des definitions des accès email
Route::get('/validate-agence-account/{email}', [AgenceController::class, 'defineAccess']);
Route::post('/validate-agence-account/{email}', [AgenceController::class, 'submitDefineAccess'])->name('agence.validate');
Route::get('/validate-locataire-account/{email}', [LocataireController::class, 'defineAccess']);
Route::post('/validate-locataire-account/{email}', [LocataireController::class, 'submitDefineAccess'])->name('locataire.validate');
Route::get('/validate-comptable-account/{email}', [ComptableController::class, 'defineAccess']);
Route::post('/validate-comptable-account/{email}', [ComptableController::class, 'submitDefineAccess'])->name('accounting.validate');
Route::get('/validate-owner-account/{email}', [ProprietaireController::class, 'defineAccess']);
Route::post('/validate-owner-account/{email}', [ProprietaireController::class, 'submitDefineAccess'])->name('owner.validate');


 //routes de gestions des onglets du menus 
Route::get('/maelys/about',[HomePageController::class,'about'])->name('maelys.about');
Route::get('/privacy-policy',[HomePageController::class,'privacy'])->name('maelys.privacy');
Route::get('/contact',[HomePageController::class,'contact'])->name('maelys.contact');
Route::post('/contact', [HomePageController::class, 'send'])->name('contact.send');
Route::get('/maelys/service',[HomePageController::class,'service'])->name('maelys.service');
Route::get('/maelys/abonnement',[HomePageController::class,'abonnement'])->name('maelys.abonnement');
Route::get('/biens/appartemnets',[BienController::class, 'appartements'])->name('bien.appartement');
Route::get('/biens/maisons',[BienController::class, 'maisons'])->name('bien.maison');
Route::get('/biens/terrains',[BienController::class, 'terrains'])->name('bien.terrain');
Route::get('/visiter-bien/{id}', [BienController::class, 'visiter'])->name('bien.visiter');
Route::post('/visite', [VisiteController::class, 'store'])->name('visite.store');


//routes pour la gestion des contrats
// Route::get('/locataires/{locataire}/infos-contrat', [ContratController::class, 'getInfosContrat'])->name('locataires.infos-contrat');
// Route::post('/locataires/{locataire}/generate-contrat', [ContratController::class, 'generateAndAssociateContrat'])->name('locataires.generateContrat') ;
Route::get('/contrats/{contrat}/show', [ContratController::class, 'show'])->name('contrats.show');
Route::get('/locataires/{locataire}/download-contrat', [ContratController::class, 'downloadContrat'])->name('locataires.downloadContrat');
Route::delete('/contrats/{contrat}', [ContratController::class, 'destroy'])->name('contrats.destroy');



// Routes pour la réinitialisation du mot de passe

Route::prefix('agence')->group(function(){ //agence password reset routes
    Route::get('/mot-de-passe/oublie', [AgencePasswordResetController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/mot-de-passe/email', [AgencePasswordResetController::class, 'sendResetLinkEmail'])->name('password.email');
    Route::get('/mot-de-passe/reinitialiser/{email}/{token}', [AgencePasswordResetController::class, 'showResetForm'])->name('password.reset');
    Route::post('/mot-de-passe/reinitialiser', [AgencePasswordResetController::class, 'reset'])->name('password.update');
});

Route::prefix('owner')->group(function(){ //proprietaire password reset routes
    Route::get('/mot-de-passe/oublie', [OwnerPasswordResetController::class, 'showLinkRequestForm'])->name('owner.request');
    Route::post('/mot-de-passe/email', [OwnerPasswordResetController::class, 'sendResetLinkEmail'])->name('owner.email');
    Route::get('/mot-de-passe/reinitialiser/{email}/{token}', [OwnerPasswordResetController::class, 'showResetForm'])->name('owner.reset');
    Route::post('/mot-de-passe/reinitialiser', [OwnerPasswordResetController::class, 'reset'])->name('owner.update');
});

Route::prefix('locataire')->group(function(){ //locataire password reset routes
    Route::get('/mot-de-passe/oublie', [LocatairePasswordResetController::class, 'showLinkRequestForm'])->name('locataire.request');
    Route::post('/mot-de-passe/email', [LocatairePasswordResetController::class, 'sendResetLinkEmail'])->name('locataire.email');
    Route::get('/mot-de-passe/reinitialiser/{email}/{token}', [LocatairePasswordResetController::class, 'showResetForm'])->name('locataire.reset');
    Route::post('/mot-de-passe/reinitialiser', [LocatairePasswordResetController::class, 'reset'])->name('locataire.password.update');
});

Route::prefix('accounting')->group(function(){ //agent password reset routes
    Route::get('/mot-de-passe/oublie', [ComptablePasswordResetController::class, 'showLinkRequestForm'])->name('comptable.request');
    Route::post('/mot-de-passe/email', [ComptablePasswordResetController::class, 'sendResetLinkEmail'])->name('comptable.email');
    Route::get('/mot-de-passe/reinitialiser/{email}/{token}', [ComptablePasswordResetController::class, 'showResetForm'])->name('comptable.reset');
    Route::post('/mot-de-passe/reinitialiser', [ComptablePasswordResetController::class, 'reset'])->name('comptable.password.update');
});

// Routes pour la gestion des abonnements du proprietaire
Route::prefix('owner')->group(function () {
   Route::get('/abonnement', [AbonnementController::class, 'abonnement'])->name('page.abonnement');
   Route::post('/owner/activate', [AbonnementController::class, 'activateAccount'])->name('owner.activate');
   Route::post('/notify', [AbonnementController::class, 'handleCinetPayNotification'])->name('cinetpay.notify');
});

// Routes pour la gestion des abonnements de l'agence
Route::prefix('agence')->group(function () {
   Route::get('/abonnement', [AbonnementController::class, 'abonnementAgence'])->name('page.abonnement.agence');
   Route::post('/agence/activate', [AbonnementController::class, 'activateAccountAgence'])->name('agence.activate');
   Route::post('/notify', [AbonnementController::class, 'handleCinetPayNotificationAgence'])->name('cinetpay.notify.agence');
});





