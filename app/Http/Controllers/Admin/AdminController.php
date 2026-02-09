<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MarketPulseSnapshot;
use App\Models\User;
use App\Models\WaitlistSignup;
use Illuminate\View\View;

class AdminController extends Controller
{
    /**
     * Admin dashboard overview.
     */
    public function index(): View
    {
        return view('admin.index', [
            'userCount' => User::count(),
            'snapshotCount' => MarketPulseSnapshot::count(),
            'publishedSnapshotCount' => MarketPulseSnapshot::published()->count(),
            'waitlistCount' => WaitlistSignup::count(),
        ]);
    }
}
