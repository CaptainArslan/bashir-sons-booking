<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

// use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Show the general dashboard for all authenticated users
        return view('dashboard');
    }
}
