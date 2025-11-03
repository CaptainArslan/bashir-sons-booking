<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Route;
use App\Models\Enquiry;
use App\Models\Terminal;
use App\Models\RouteStop;
use App\Enums\TerminalEnum;
use Illuminate\Http\Request;
use App\Models\TimetableStop;
use App\Models\GeneralSetting;
use Illuminate\Http\JsonResponse;
use App\Mail\EnquiryFormSubmitted;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

// use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard');
    }

    public function home(): View
    {
        $terminals = Terminal::where('status', TerminalEnum::ACTIVE->value)
            ->with('city')
            ->get();

        $generalSettings = GeneralSetting::first();
        $minDate = Carbon::today()->format('Y-m-d');
        $maxDate = $minDate;

        if ($generalSettings->advance_booking_enable ) {
            $maxDate = Carbon::today()->addDays($generalSettings->advance_booking_days ?? 7)->format('Y-m-d');
        }

        return view('frontend.home', compact('terminals', 'minDate', 'maxDate'));
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

    public function submitEnquiry(Request $request)
    {
        // Step 1: Validate input
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'service' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        // Step 2: Save to database
        $enquiry = Enquiry::create($validated);

        // Step 3: Send email notification to admin
        try {
            Mail::to(config('mail.from.address')) // Or replace with your admin email
                ->send(new EnquiryFormSubmitted($enquiry));
        } catch (\Exception $e) {
            // You can log error if email fails
            Log::error('Enquiry form email failed: ' . $e->getMessage());
        }

        // Step 4: Redirect with success
        return redirect()
            ->route('contact')
            ->with('success', 'Thank you! Your message has been sent successfully.');
    }

    public function booking(): View
    {
        return view('frontend.booking');
    }

    public function getRouteStops(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
        ]);

        try {
            $routes = Route::query()
                ->whereHas('routeStops', fn($q) => $q->where('terminal_id', $validated['from_terminal_id']))
                ->where('status', 'active')
                ->get();

            $routeStops = collect();

            foreach ($routes as $route) {
                $stops = RouteStop::where('route_id', $route->id)
                    ->with('terminal:id,name,code')
                    ->orderBy('sequence')
                    ->get();

                // Get stops after the from terminal
                $fromStopIndex = $stops->search(fn($stop) => $stop->terminal_id == $validated['from_terminal_id']);
                if ($fromStopIndex !== false) {
                    $filteredStops = $stops->slice($fromStopIndex + 1);
                    $routeStops = $routeStops->merge($filteredStops);
                }
            }

            // Remove duplicates using terminal_id
            $uniqueStops = $routeStops
                ->unique('terminal_id')
                ->values()
                ->map(function ($stop) {
                    return [
                        'id' => $stop->id,
                        'terminal_id' => $stop->terminal_id,
                        'terminal' => [
                            'id' => $stop->terminal->id,
                            'name' => $stop->terminal->name,
                            'code' => $stop->terminal->code,
                        ],
                    ];
                })
                ->all();

            return response()->json(['route_stops' => $uniqueStops]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function getDepartureTimes(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'from_terminal_id' => 'required|exists:terminals,id',
            'to_terminal_id' => 'required|exists:terminals,id',
            'date' => 'required|date_format:Y-m-d|after_or_equal:today',
        ]);

        try {
            if ($validated['from_terminal_id'] === $validated['to_terminal_id']) {
                throw new \Exception('From and To terminals must be different');
            }

            $selectedDate = $validated['date'];
            $now = now();

            $timetableStops = [];

            $timetableStopsQuery = TimetableStop::where('terminal_id', $validated['from_terminal_id'])
                ->where('is_active', true)
                ->with('timetable.route')
                ->get();

            foreach ($timetableStopsQuery as $ts) {
                if (!$ts->timetable || !$ts->timetable->route) {
                    continue;
                }

                $routeStops = RouteStop::where('route_id', $ts->timetable->route->id)
                    ->orderBy('sequence')
                    ->get();

                $fromStop = $routeStops->firstWhere('terminal_id', $validated['from_terminal_id']);
                $toStop = $routeStops->firstWhere('terminal_id', $validated['to_terminal_id']);

                if (!$fromStop || !$toStop || $fromStop->sequence >= $toStop->sequence) {
                    continue;
                }

                if ($ts->departure_time) {
                    $fullDeparture = Carbon::parse(
                        $selectedDate . ' ' . $ts->departure_time
                    );

                    if ($fullDeparture->greaterThanOrEqualTo($now)) {
                        $timetableStops[] = [
                            'id' => $ts->id,
                            'departure_at' => $ts->departure_time,
                            'arrival_at' => $ts->arrival_time,
                            'terminal_id' => $ts->terminal_id,
                            'timetable_id' => $ts->timetable_id,
                            'route_id' => $ts->timetable->route->id,
                            'route_name' => $ts->timetable->route->name,
                            'full_departure' => $fullDeparture->toDateTimeString(),
                        ];
                    }
                }
            }

            $timetableStops = collect($timetableStops)
                ->sortBy('full_departure')
                ->values();

            return response()->json(['timetable_stops' => $timetableStops]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
