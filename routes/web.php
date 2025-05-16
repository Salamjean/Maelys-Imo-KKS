<?php

use Illuminate\Http\Request;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgenceController;
use App\Http\Controllers\BienController;
use App\Http\Controllers\ComptableController;
use App\Http\Controllers\ContratController;
use App\Http\Controllers\Controller;
use App\Http\Controllers\HomePageController;
use App\Http\Controllers\LocataireController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VisiteController;
use App\Models\Bien;
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
    $biens = $query->orderBy('created_at', 'desc')->paginate(6);
    
    // Compteurs par type (sans les filtres pour garder les totaux)
    $appartements = Bien::where('status', 'Disponible')
                        ->where('type', 'Appartement')->count();
    $maisons = Bien::where('status', 'Disponible')
                  ->where('type', 'Maison')->count();
    $terrains = Bien::where('status', 'Disponible')
                   ->where('type', 'Bureau')->count();
    
    return view('home.accueil', compact('biens', 'appartements', 'maisons', 'terrains'));
});

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


    //routes de gestion des agences partenaires par l'administrateur
    Route::get('/agences/index',[AgenceController::class, 'index'])->name('agence.index');
    Route::get('/agencescreate',[AgenceController::class, 'create'])->name('agence.create');
    Route::post('/agences/store',[AgenceController::class, 'store'])->name('agence.store');
    Route::get('/agences/{agence}/edit', [AgenceController::class, 'edit'])->name('agence.edit');
    Route::put('/agences/{agence}', [AgenceController::class, 'update'])->name('agence.update');
    Route::delete('/agences/{id}/destroy',[AgenceController::class, 'destroy'])->name('agence.destroy');

     //routes de gestion des locataires par l'administrateur
     Route::get('/locataires',[LocataireController::class, 'indexAdmin'])->name('locataire.admin.index');
     Route::get('/locataires/not/serieux',[LocataireController::class, 'indexSerieuxAdmin'])->name('locataire.admin.indexSerieux');
     Route::get('/locatairescreate',[LocataireController::class, 'createAdmin'])->name('locataire.admin.create');
     Route::post('/locataires/store',[LocataireController::class, 'storeAdmin'])->name('locataire.admin.store');
     Route::put('/locataires/{locataire}/status', [LocataireController::class, 'updateStatusAdmin'])->name('locataire.admin.updateStatus');
     Route::get('/locataires/{locataire}/edit', [LocataireController::class, 'editAdmin'])->name('locataire.admin.edit');
    Route::put('/locataires/{locataire}', [LocataireController::class, 'updateAdmin'])->name('locataire.admin.update');
    

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
    });





    Route::middleware('guest:admin')->prefix('admin')->group(function () {
        Route::get('/register',[AdminController::class, 'register'])->name('admin.register');
        Route::post('/register',[AdminController::class, 'store'])->name('admin.store');
        Route::get('/login',[AdminController::class, 'login'])->name('admin.login');
        Route::post('/login',[AdminController::class, 'authenticate'])->name('admin.authenticate');
    });

    //routes de gestions des onglets du menus 
    Route::get('/maelys/about',[HomePageController::class,'about'])->name('maelys.about');
    Route::get('/maelys/service',[HomePageController::class,'service'])->name('maelys.service');

    Route::get('/biens/appartemnets',[BienController::class, 'appartements'])->name('bien.appartement');
    Route::get('/biens/maisons',[BienController::class, 'maisons'])->name('bien.maison');
    Route::get('/biens/terrains',[BienController::class, 'terrains'])->name('bien.terrain');
    Route::get('/visiter-bien/{id}', [BienController::class, 'visiter'])->name('bien.visiter');

    //Les routes de gestion des visites
    Route::post('/visite', [VisiteController::class, 'store'])->name('visite.store');

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


    Route::prefix('accounting')->group(function(){
        Route::get('/',[ComptableController::class,'index'])->name('accounting.index');
        Route::get('/accountingcreate',[ComptableController::class,'create'])->name('accounting.create');
        Route::post('/create',[ComptableController::class,'store'])->name('accounting.store');
        Route::get('/edit/{comptable}',[ComptableController::class,'edit'])->name('accounting.edit');
        Route::put('/{id}', [ComptableController::class, 'update'])->name('accounting.update');
    });
});

//routes de gestions des definitions des accès email
Route::get('/validate-agence-account/{email}', [AgenceController::class, 'defineAccess']);
Route::post('/validate-agence-account/{email}', [AgenceController::class, 'submitDefineAccess'])->name('agence.validate');
Route::get('/validate-locataire-account/{email}', [LocataireController::class, 'defineAccess']);
Route::post('/validate-locataire-account/{email}', [LocataireController::class, 'submitDefineAccess'])->name('locataire.validate');
Route::get('/validate-comptable-account/{email}', [ComptableController::class, 'defineAccess']);
Route::post('/validate-comptable-account/{email}', [ComptableController::class, 'submitDefineAccess'])->name('accounting.validate');


// Routes pour la gestion des paiements
Route::post('/locataires/send-payment-reminder', [PaymentController::class, 'sendPaymentReminder'])->name('locataires.sendPaymentReminder');
Route::prefix('locataire/{locataire}/paiements')->group(function() {
    Route::get('/', [PaymentController::class, 'index'])->name('locataire.paiements.index');
    Route::get('create', [PaymentController::class, 'create'])->name('locataire.paiements.create');
    Route::post('/', [PaymentController::class, 'store'])->name('locataire.paiements.store');
});

//routes de paiement
Route::post('/paiements/generate-cash-code', [PaymentController::class, 'generateCashCode'])->name('paiements.generateCashCode');
Route::post('/paiements/verify-cash-code', [PaymentController::class, 'verifyCashCode'])->name('paiements.verifyCashCode');
Route::post('/cinetpay/notify', [PaymentController::class, 'handleCinetPayNotification'])->name('cinetpay.notify');
Route::post('/payment/check-status', [PaymentController::class, 'checkPaymentStatus'])->name('payment.check-status');
Route::get('/paiements/{paiement}/receipt', [PaymentController::class, 'generateReceipt'])->name('locataire.paiements.receipt');
Route::get('/locataires/get-montant-loyer', [PaymentController::class, 'getMontantLoyer'])->name('locataires.getMontantLoyer');


//les routes de connexion de l'agence
Route::middleware('guest:agence')->prefix('agence')->group(function () {
    Route::get('/login',[AgenceController::class, 'login'])->name('agence.login');
    Route::post('/login',[AgenceController::class, 'authenticate'])->name('agence.authenticate');
});

//les routes de connexion du comptable
Route::middleware('guest:comptable')->prefix('accounting')->group(function () {
    Route::get('/login',[ComptableController::class, 'login'])->name('comptable.login');
    Route::post('/login',[ComptableController::class, 'authenticate'])->name('comptable.authenticate');
});



// Routes pour la gestion des locataires
Route::middleware('auth:locataire')->prefix('locataire')->group(function () {
    Route::get('/dashboard',[LocataireController::class, 'dashboard'])->name('locataire.dashboard');
    Route::get('/logout',[LocataireController::class, 'logout'])->name('locataire.logout');
    Route::get('locataire/bien/show/{id}', [LocataireController::class, 'show'])->name('locataire.bien.show');
    Route::get('/profile/edit', [LocataireController::class, 'editProfile'])->name('locataire.edit.profile');
    Route::put('/profile/edit', [LocataireController::class, 'updateProfile'])->name('locataire.update.profile');
});


//routes pour la gestion des comptables 
Route::middleware('auth:comptable')->prefix('accounting')->group(function () {
    Route::get('/dashboard',[ComptableController::class,'dashboard'])->name('accounting.dashboard');
    Route::get('/logout',[ComptableController::class, 'logout'])->name('accounting.logout');
    Route::get('/listes/tenant',[ComptableController::class,'tenant'])->name('accounting.tenant');
    Route::get('/rappel/payment',[ComptableController::class,'payment'])->name('accounting.payment');
});

Route::prefix('locataire')->group(function () {
    Route::get('/login',[LocataireController::class, 'login'])->name('locataire.login');
    Route::post('/login',[LocataireController::class, 'authenticate'])->name('locataire.authenticate');
});
Route::get('/locataires/{locataire}/infos-contrat', [ContratController::class, 'getInfosContrat'])->name('locataires.infos-contrat');
Route::post('/locataires/{locataire}/generate-contrat', [ContratController::class, 'generateAndAssociateContrat'])->name('locataires.generateContrat') ;
Route::get('/contrats/{contrat}/show', [ContratController::class, 'show'])->name('contrats.show');
Route::get('/locataires/{locataire}/download-contrat', [ContratController::class, 'downloadContrat'])->name('locataires.downloadContrat');
Route::delete('/contrats/{contrat}', [ContratController::class, 'destroy'])->name('contrats.destroy');






