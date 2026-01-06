<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class HowItWorksController extends Controller
{
    public function index()
    {
        return view('frontend.how-it-works.index');
    }
}
