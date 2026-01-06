<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Item;
use App\Models\UserViolation;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'active_today' => User::whereDate('last_active_at', today())->count(),
            'total_items' => Item::count(),
            'pending_violations' => UserViolation::where('status', 'pending')->count(),
        ];

        return view('admin.dashboard.index', compact('stats'));
    }
}
