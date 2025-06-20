<?php

namespace App\Http\Controllers;

use App\Models\Abonnement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class HomePageController extends Controller
{
    public function about(){
        return view('home.about');
    }
    public function service(){
        return view('home.service');
    }

   
}
