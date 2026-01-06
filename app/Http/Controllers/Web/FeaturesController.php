<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class FeaturesController extends Controller
{
    public function index()
    {
        return view('frontend.features.index');
    }
}
