<?php

use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\Visite\VisiteController;
use Illuminate\Support\Facades\Route;


//Les routes de la pages d'accueil
Route::get('/', [HomeController::class, 'index'])->name('api.home');
Route::get('/biens/appartements', [HomeController::class, 'appartementsApi'])->name('api.bien.appartements');
Route::get('/biens/maisons', [HomeController::class, 'maisonsApi'])->name('api.bien.maisons');
Route::get('/biens/bureaux', [HomeController::class, 'terrainsApi'])->name('api.bien.terrains');
Route::post('/contact', [HomeController::class, 'send'])->name('api.contact.send');

//Les routes de visite
Route::get('/biens/{id}/visite', [VisiteController::class, 'visiter'])->name('api.bien.visiter');
