<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
// use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if (! $user->can('access admin panel')) {
            abort(403, 'You do not have access to the admin panel.');
        }

        return 'Admin Dashboard';
        // return view('admin.dashboard');
    }
}
