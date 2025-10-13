<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

// use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard');
    }

    public function home()
    {
        return view('frontend.home');
    }

    public function services()
    {
        return view('frontend.services');
    }

    public function bookings()
    {
        return view('frontend.bookings');
    }

    public function aboutUs()
    {
        return view('frontend.about-us');
    }

    public function contact()
    {
        return view('frontend.contact');
    }

    public function booking()
    {
        return view('frontend.booking');
    }
}
