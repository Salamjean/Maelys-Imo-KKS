<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomePageController extends Controller
{
    public function about(){
        return view('home.about');
    }
    public function service(){
        return view('home.service');
    }
}
