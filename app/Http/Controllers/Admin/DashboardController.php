<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bus;
use App\Models\Fare;
use App\Models\User;
use App\Models\Route;
use App\Models\Enquiry;
use App\Models\Terminal;
use App\Models\RouteStop;
use App\Enums\RouteStatusEnum;
use App\Enums\EnquiryStatusEnum;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Get dashboard statistics
        $stats = $this->getDashboardStats();
        
        // Get recent data
        $recentData = $this->getRecentData();
        
        // Get chart data
        $chartData = $this->getChartData();

        return view('admin.dashboard', compact('stats', 'recentData', 'chartData'));
    }

    private function getDashboardStats()
    {
        return [
            'total_routes' => Route::count(),
            'active_routes' => Route::where('status', 'active')->count(),
            'total_buses' => Bus::count(),
            'active_buses' => Bus::where('status', 'active')->count(),
            'total_terminals' => Terminal::count(),
            'active_terminals' => Terminal::where('status', 'active')->count(),
            'total_users' => User::count(),
            'total_enquiries' => Enquiry::count(),
            'pending_enquiries' => Enquiry::where('status', EnquiryStatusEnum::PENDING->value)->count(),
            'total_fares' => Fare::count(),
            'active_fares' => Fare::where('status', 'active')->count(),
            'total_stops' => RouteStop::count(),
        ];
    }

    private function getRecentData()
    {
        return [
            'recent_routes' => Route::with('routeStops.terminal.city')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'recent_enquiries' => Enquiry::orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'recent_users' => User::orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
        ];
    }

    private function getChartData()
    {
        // Routes by status
        $routesByStatus = Route::selectRaw('CAST(status AS CHAR) as status_value, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status_value')
            ->toArray();

        // Buses by status
        $busesByStatus = Bus::selectRaw('CAST(status AS CHAR) as status_value, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status_value')
            ->toArray();

        // Enquiries by status
        $enquiriesByStatus = Enquiry::selectRaw('CAST(status AS CHAR) as status_value, count(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status_value')
            ->toArray();

        // Monthly route creation
        $monthlyRoutes = Route::select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('count(*) as count')
            )
            ->where('created_at', '>=', now()->subMonths(6))
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        return [
            'routes_by_status' => $routesByStatus,
            'buses_by_status' => $busesByStatus,
            'enquiries_by_status' => $enquiriesByStatus,
            'monthly_routes' => $monthlyRoutes,
        ];
    }
}
