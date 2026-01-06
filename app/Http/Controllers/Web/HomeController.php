<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Item;
use Illuminate\Support\Facades\DB;

class HomeController extends Controller
{
    public function index()
    {
        // Get stats for the homepage
        $stats = [
            'total_users' => User::count(),
            'total_items' => Item::count(),
            'total_transactions' => DB::table('transactions')->count(),
            'money_saved' => DB::table('transactions')->sum('amount') ?? 2500000
        ];
        
        return view('frontend.home.index', compact('stats'));
    }
}
