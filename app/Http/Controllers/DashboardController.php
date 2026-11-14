<?php

namespace App\Http\Controllers;

class DashboardController extends Controller
{
    public function index()
    {
        $sites = auth()->user()->sites()->with('connections')->latest()->get();

        return view('dashboard', compact('sites'));
    }
}
