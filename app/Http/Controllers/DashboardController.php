<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Contracts\View\View;

// use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard');
    }

    public function home(): View
    {
        return view('frontend.home');
    }

    public function services(): View
    {
        return view('frontend.services');
    }

    public function bookings(): View
    {
        return view('frontend.bookings');
    }

    public function aboutUs(): View
    {
        return view('frontend.about');
    }

    public function contact(): View
    {
        return view('frontend.contact');
    }

    public function booking(): View
    {
        return view('frontend.booking');
    }
}
