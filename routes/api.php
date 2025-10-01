<?php

use App\Http\Controllers\Api\Agent\ApiAgentCodeEtatLieu;
use App\Http\Controllers\Api\Agent\ApiAgentDashboard;
use App\Http\Controllers\Api\Agent\ApiAgentEtatLieu;
use App\Http\Controllers\Api\Agent\ApiAgentPaiement;
use App\Http\Controllers\Api\Authenticate\UserAuthentucateController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\Locataire\ApiLocataireController;
use App\Http\Controllers\Api\Locataire\ContacterAgenceController;
use App\Http\Controllers\Api\Locataire\EtatLieuController;
use App\Http\Controllers\Api\Paiement\Paiementcontroller;
use App\Http\Controllers\Api\Visite\ApiVisiteController;
use Illuminate\Support\Facades\Route;


Route::post('/paiement/cinetpay/notify', [PaiementController::class, 'handleCinetPayNotification'])->name('api.cinetpay.notify');
Route::get('/paiement/check/{transactionId}', [PaiementController::class, 'checkPaymentStatus']);
Route::match(['get', 'post'], '/paiement/success', [PaiementController::class, 'paymentSuccess']);
Route::match(['get', 'post'], '/paiement/cancel', [PaiementController::class, 'paymentCancel']);
//Les routes de gestion des locataires
Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('tenant')->group(function(){
    Route::get('/dashboard', [ApiLocataireController::class, 'dashboard']);
    Route::post('contact/agency',[ContacterAgenceController::class,'sendEmailToAgency']);
    Route::get('profil/edit',[ApiLocataireController::class,'edit']);
    Route::post('/profile/photo', [ApiLocataireController::class, 'updateProfilePhoto']);
    Route::post('/profile/update-password', [ApiLocataireController::class, 'updatePassword']);
    Route::post('/profile/update-email', [ApiLocataireController::class, 'updateEmail']);
    Route::get('/contrat', [ApiLocataireController::class, 'getContratLink']);
    Route::get('/{locataire}/contrat', [ApiLocataireController::class, 'downloadContrat']);
    
    
    //paiements locataire
    Route::post('/{locataire}/paiements', [Paiementcontroller::class, 'store']);
    Route::get('/{locataireId}/paiements', [PaiementController::class, 'index']);
    Route::get('/paiements/{id}', [PaiementController::class, 'show']);
    Route::get('/paiement/mon-qr-code', [PaiementController::class, 'getMyQrCode']);

   Route::prefix('etat-lieu')->group(function () {
        Route::get('/entree', [EtatLieuController::class, 'getEtatsLieuEntree']);
        Route::get('/sortie', [EtatLieuController::class, 'getEtatsLieuSortie']);
        Route::get('/all', [EtatLieuController::class, 'getAllEtatsLieu']);
        Route::get('/entree/{id}', [EtatLieuController::class, 'getEtatLieuEntreeById']);
    });
});

Route::get('/tenant/qr-code', [ApiAgentCodeEtatLieu::class, 'getQrCode']);
Route::get('/etat-lieux/{id}/download', [ApiAgentEtatLieu::class, 'downloadApi']);
Route::post('/paiement/verifier-code-especes', [ApiAgentPaiement::class, 'verifyCashCodeAgent']);
//Les routes de gestion des locataires 
Route::middleware(['auth:sanctum', 'throttle:60,1'])->prefix('agent')->group(function(){
      Route::get('/dashboard', [ApiAgentDashboard::class, 'dashboard']);
      Route::get('/etats-lieu/warning', [ApiAgentEtatLieu::class, 'getLocataireAvecEtatsLieuEnAttente']);
      // routes/api.php
      Route::get('/etats-lieu/{locataireId}/details', [ApiAgentEtatLieu::class, 'getLocataireAvecBienEtEtatsLieu']);
      Route::get('/etats-lieu/effectues', [ApiAgentEtatLieu::class, 'getAllEtatsLieuEffectues']);
      Route::get('/etats/end', [ApiAgentEtatLieu::class, 'getEtatsLieuFin']);
      Route::post('/etat-lieux', [ApiAgentEtatLieu::class, 'store']);
      

      //Les codes de verification des etats des lieux 
      Route::post('/generate', [ApiAgentCodeEtatLieu::class, 'generateCode']);
      Route::post('/verify', [ApiAgentCodeEtatLieu::class, 'verifyCode']);

      //Les routes de paiements 
      Route::get('/paiements/history', [ApiAgentPaiement::class, 'historyApi']);
      Route::get('/locataires/retard', [ApiAgentPaiement::class, 'getLocatairesRetard']);
      Route::get('/locataires/a-jour', [ApiAgentPaiement::class, 'getLocatairesAJour']);
      Route::get('/locataires/en-attente', [ApiAgentPaiement::class, 'getLocatairesEnAttente']);
      Route::get('/locataire/{id}/details', [ApiAgentPaiement::class, 'getLocataireDetails']);
      Route::post('/paiement/generer-code-especes', [ApiAgentPaiement::class, 'generateCashCode']);
});

//Les routes d'authentification 
Route::post('/login', [UserAuthentucateController::class, 'login']);

Route::prefix('password')->group(function(){
    Route::post('/forgot', [UserAuthentucateController::class, 'sendResetOTP']);
    Route::post('/reset', [UserAuthentucateController::class, 'resetPassword']);
});

//Les routes de la pages d'accueil
Route::get('/', [HomeController::class, 'index'])->name('api.home');
Route::get('/biens/appartements', [HomeController::class, 'appartementsApi'])->name('api.bien.appartements');
Route::get('/biens/available', [HomeController::class, 'availableApi'])->name('api.bien.available');
Route::get('/biens/all', [HomeController::class, 'typesDeBiensApi'])->name('api.bien.available');
Route::get('/biens/maisons', [HomeController::class, 'maisonsApi'])->name('api.bien.maisons');
Route::get('/biens/bureaux', [HomeController::class, 'terrainsApi'])->name('api.bien.terrains');
Route::get('/biens/{id}', [HomeController::class, 'show']);
Route::post('/contact', [HomeController::class, 'send'])->name('api.contact.send');

//Les routes de visite
Route::prefix('visit')->group(function(){
    Route::post('store',[ApiVisiteController::class,'store'])->name('api.visite.store');
});





