<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $sites = auth()->user()->sites()->with('connections')->latest()->get();

        // একটাই সাইট থাকলে ড্যাশবোর্ড না দেখিয়ে সরাসরি কাজের পাতায় নিয়ে যাই
        if ($sites->count() === 1 && !$request->boolean('all')) {
            return redirect('/sites/' . $sites->first()->id);
        }

        return view('dashboard', compact('sites'));
    }
}
