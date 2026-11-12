<?php

namespace App\Http\Controllers;

use App\Models\Site;
use App\Models\User;

class OperatorController extends Controller
{
    public function index()
    {
        $users = User::withCount('sites')->latest()->get();
        $sites = Site::with('user')->latest()->get();

        return view('operator.index', compact('users', 'sites'));
    }

    public function toggle(Site $site)
    {
        $site->update([
            'status' => $site->status === 'active' ? 'suspended' : 'active',
        ]);

        return back()->with('ok', 'সাইটের অবস্থা বদলানো হয়েছে।');
    }
}
