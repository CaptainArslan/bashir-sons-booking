<?php

namespace App\Livewire\Admin;

use App\Enums\ChannelEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\TerminalEnum;
use App\Events\SeatConfirmed;
use App\Events\SeatLocked;
use App\Events\SeatUnlocked;
use App\Models\Booking;
use App\Models\Fare;
use App\Models\GeneralSetting;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Terminal;
use App\Models\Timetable;
use App\Models\TimetableStop;
use App\Models\Trip;
use App\Models\TripStop;
use App\Services\AvailabilityService;
use App\Services\BookingService;
use App\Services\TripFactoryService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class BookingConsole extends Component
{
    protected BookingService $bookingService;

    protected TripFactoryService $tripFactory;

    protected AvailabilityService $availabilityService;

    // Date & Terminal Selection
    public $travelDate;

    public $fromTerminalId;

    public $toTerminalId;

    public $departureTimeId;

    public $arrivalTime;

    // Trip Data
    public $tripId = null;

    public $tripData = null;

    public $routeData = null;

    public $fromStop = null;

    public $toStop = null;

    public $seatMap = [];

    public $availableSeats = [];

    public $busAssignments = [];

    // Seat Selection
    public $selectedSeats = [];

    public $pendingSeat = null;

    // Fare Data
    public $fareData = null;

    public $baseFare = 0;

    public $discountAmount = 0;

    public $taxAmount = 0;

    public $finalAmount = 0;

    public $fareValid = false;

    public $fareError = null;

    // Booking Form
    public $bookingType = 'counter';

    public $paymentMethod = 'cash';

    public $transactionId = null;

    public $amountReceived = 0;

    public $returnAmount = 0;

    public $notes = '';

    // Passengers
    public $passengers = [];

    public $passengerCounter = 1;

    // Trip Passengers List
    public $tripPassengers = [];

    public $totalEarnings = 0;

    // Last Booking Data (for reprint)
    public $lastBookingId = null;

    public $lastBookingData = null;

    // UI State
    public $tripLoaded = false;

    public $showTripContent = false;

    public $lockedSeats = [];

    // Options
    public $terminals = [];

    public $toTerminals = [];

    public $departureTimes = [];

    public $paymentMethods = [];

    protected $listeners = [
        'seatLocked' => 'handleSeatLocked',
        'seatUnlocked' => 'handleSeatUnlocked',
        'seatConfirmed' => 'handleSeatConfirmed',
    ];

    public function mount(): void
    {
        $generalSettings = GeneralSetting::first();
        $this->travelDate = Carbon::today()->format('Y-m-d');

        if ($generalSettings && $generalSettings->advance_booking_enable) {
            $maxDate = Carbon::today()->addDays($generalSettings->advance_booking_days ?? 7);
        } else {
            $maxDate = Carbon::today();
        }

        $this->paymentMethods = PaymentMethodEnum::options();

        $user = Auth::user();
        $this->terminals = Terminal::query()
            ->where('status', TerminalEnum::ACTIVE)
            ->when($user->terminal_id, function ($query) use ($user) {
                $query->where('id', $user->terminal_id);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'code', 'city_id']);

        // Set default terminal for employees
        if ($user->terminal_id && ! $user->hasRole('admin')) {
            $this->fromTerminalId = $user->terminal_id;
            $this->updatedFromTerminalId();
        }

        // Initialize first passenger
        $this->passengers = [
            [
                'id' => 1,
                'name' => '',
                'age' => '',
                'gender' => '',
                'cnic' => '',
                'phone' => '',
                'email' => '',
                'is_required' => true,
            ],
        ];
    }

    public function updatedFromTerminalId(): void
    {
        $this->toTerminalId = null;
        $this->departureTimeId = null;
        $this->arrivalTime = null;
        $this->showTripContent = false;
        $this->tripLoaded = false;
        $this->resetTripData();
        $this->fareError = null;

        if ($this->fromTerminalId) {
            $this->loadToTerminals();
        }
    }

    public function updatedToTerminalId(): void
    {
        $this->departureTimeId = null;
        $this->arrivalTime = null;
        $this->showTripContent = false;
        $this->tripLoaded = false;
        $this->resetTripData();
        $this->fareError = null;

        if ($this->fromTerminalId && $this->toTerminalId) {
            $this->loadDepartureTimes();
            $this->loadFare();
        }
    }

    public function updatedDepartureTimeId(): void
    {
        $this->updateArrivalTime();
    }

    public function updatedTravelDate(): void
    {
        if ($this->fromTerminalId && $this->toTerminalId) {
            $this->loadDepartureTimes();
        }
    }

    public function loadToTerminals(): void
    {
        $fromTerminalId = $this->fromTerminalId;
        $user = Auth::user();

        if ($user->routes()->exists()) {
            $routes = $user->routes()->where('status', 'active')->get();
        } else {
            $routes = Route::whereHas('routeStops', function ($q) use ($fromTerminalId) {
                $q->where('terminal_id', $fromTerminalId);
            })
                ->where('status', 'active')
                ->get();
        }

        $terminals = collect();

        foreach ($routes as $route) {
            $stops = RouteStop::where('route_id', $route->id)
                ->with('terminal:id,name,code')
                ->orderBy('sequence')
                ->get();

            $origin = $stops->firstWhere('terminal_id', $fromTerminalId);

            if (! $origin) {
                continue;
            }

            $filtered = $stops->filter(function ($stop) use ($origin) {
                return $stop->sequence > $origin->sequence;
            });

            foreach ($filtered as $stop) {
                $terminals->push([
                    'terminal_id' => $stop->terminal_id,
                    'name' => $stop->terminal->name,
                    'code' => $stop->terminal->code,
                    'sequence' => $stop->sequence,
                    'route_id' => $route->id,
                ]);
            }
        }

        $this->toTerminals = $terminals->unique('terminal_id')->values()->toArray();
    }

    public function loadDepartureTimes(): void
    {
        if (! $this->fromTerminalId || ! $this->toTerminalId || ! $this->travelDate) {
            return;
        }

        $selectedDate = $this->travelDate;
        $now = now();

        $timetableStops = [];

        $timetableStopsQuery = TimetableStop::where('terminal_id', $this->fromTerminalId)
            ->where('is_active', true)
            ->with('timetable.route')
            ->get();

        foreach ($timetableStopsQuery as $ts) {
            if (! $ts->timetable || ! $ts->timetable->route) {
                continue;
            }

            $routeStops = RouteStop::where('route_id', $ts->timetable->route->id)
                ->orderBy('sequence')
                ->get();

            $fromStop = $routeStops->firstWhere('terminal_id', $this->fromTerminalId);
            $toStop = $routeStops->firstWhere('terminal_id', $this->toTerminalId);

            if (! $fromStop || ! $toStop || $fromStop->sequence >= $toStop->sequence) {
                continue;
            }

            if ($ts->departure_time) {
                $fullDeparture = Carbon::parse($selectedDate.' '.$ts->departure_time);

                if ($fullDeparture->greaterThanOrEqualTo($now)) {
                    $timetableStops[] = [
                        'id' => $ts->id,
                        'timetable_id' => $ts->timetable_id,
                        'departure_at' => $ts->departure_time,
                        'arrival_at' => $ts->arrival_time,
                        'route_id' => $ts->timetable->route->id,
                        'route_name' => $ts->timetable->route->name,
                        'full_departure' => $fullDeparture->toDateTimeString(),
                    ];
                }
            }
        }

        $this->departureTimes = collect($timetableStops)
            ->sortBy('full_departure')
            ->values()
            ->toArray();
    }

    public function loadFare(): void
    {
        if (! $this->fromTerminalId || ! $this->toTerminalId || $this->fromTerminalId === $this->toTerminalId) {
            $this->fareError = 'From and To terminals must be different';
            $this->fareValid = false;

            return;
        }

        try {
            $fare = Fare::active()
                ->where('from_terminal_id', $this->fromTerminalId)
                ->where('to_terminal_id', $this->toTerminalId)
                ->first();

            if (! $fare) {
                $this->fareError = 'No fare found for this route segment';
                $this->fareValid = false;

                return;
            }

            $this->fareData = $fare;
            $this->baseFare = (float) $fare->final_fare;
            $this->discountAmount = $fare->getDiscountAmount();
            $this->fareValid = true;
            $this->fareError = null;
            $this->calculateFinal();
        } catch (\Exception $e) {
            $this->fareError = $e->getMessage();
            $this->fareValid = false;
        }
    }

    public function updateArrivalTime(): void
    {
        if ($this->departureTimeId) {
            $time = collect($this->departureTimes)->firstWhere('id', $this->departureTimeId);
            $this->arrivalTime = $time['arrival_at'] ?? null;
        } else {
            $this->arrivalTime = null;
        }
    }

    public function loadTrip(): void
    {
        if (! $this->fromTerminalId || ! $this->toTerminalId || ! $this->departureTimeId || ! $this->travelDate) {
            $this->dispatch('show-error', message: 'Please select all required fields');

            return;
        }

        $time = collect($this->departureTimes)->firstWhere('id', $this->departureTimeId);
        if (! $time) {
            $this->dispatch('show-error', message: 'Invalid departure time selected');

            return;
        }

        try {
            $timetable = Timetable::findOrFail($time['timetable_id']);
            $route = $timetable->route;

            if (! $route) {
                throw new \Exception('Route not found for selected timetable');
            }

            $trip = Trip::where('timetable_id', $timetable->id)
                ->whereDate('departure_date', $this->travelDate)
                ->first();

            if (! $trip) {
                $tripFactory = app(TripFactoryService::class);
                $trip = $tripFactory->createFromTimetable($timetable->id, $this->travelDate);
            }

            $trip->load('stops');

            $routeStops = RouteStop::where('route_id', $route->id)
                ->with('terminal:id,name,code')
                ->orderBy('sequence')
                ->get();

            $fromRouteStop = $routeStops->firstWhere('terminal_id', $this->fromTerminalId);
            $toRouteStop = $routeStops->firstWhere('terminal_id', $this->toTerminalId);

            if (! $fromRouteStop || ! $toRouteStop || $fromRouteStop->sequence >= $toRouteStop->sequence) {
                throw new \Exception('Invalid segment selection');
            }

            $tripFromStop = $trip->stops->firstWhere('terminal_id', $this->fromTerminalId);
            $tripToStop = $trip->stops->firstWhere('terminal_id', $this->toTerminalId);

            if (! $tripFromStop || ! $tripToStop) {
                throw new \Exception('Selected terminals not found in trip');
            }

            $tripFromStop->load('terminal:id,name,code');
            $tripToStop->load('terminal:id,name,code');

            $availabilityService = app(AvailabilityService::class);
            $seatCount = $availabilityService->seatCount($trip);
            $availableSeats = $availabilityService->availableSeats(
                $trip->id,
                $tripFromStop->id,
                $tripToStop->id
            );

            $seatMap = $this->buildSeatMap($trip, $tripFromStop, $tripToStop, $seatCount, $availableSeats);

            $busAssignments = $trip->busAssignments()
                ->with(['fromTripStop.terminal', 'toTripStop.terminal', 'bus', 'assignedBy'])
                ->get();

            $this->tripId = $trip->id;
            $this->tripData = $trip;
            $this->routeData = [
                'id' => $route->id,
                'name' => $route->name,
                'code' => $route->code,
            ];
            $this->fromStop = [
                'trip_stop_id' => $tripFromStop->id,
                'route_stop_id' => $fromRouteStop->id,
                'terminal_id' => $tripFromStop->terminal_id,
                'terminal_name' => $tripFromStop->terminal->name,
                'terminal_code' => $tripFromStop->terminal->code,
                'departure_at' => $tripFromStop->departure_at?->format('Y-m-d H:i:s'),
                'sequence' => $tripFromStop->sequence,
            ];
            $this->toStop = [
                'trip_stop_id' => $tripToStop->id,
                'route_stop_id' => $toRouteStop->id,
                'terminal_id' => $tripToStop->terminal_id,
                'terminal_name' => $tripToStop->terminal->name,
                'terminal_code' => $tripToStop->terminal->code,
                'arrival_at' => $tripToStop->arrival_at?->format('Y-m-d H:i:s'),
                'sequence' => $tripToStop->sequence,
            ];
            $this->seatMap = $seatMap;
            $this->availableSeats = $availableSeats;
            $this->busAssignments = $busAssignments->map(function ($assignment) {
                $fromTerminal = $assignment->fromTripStop?->terminal;
                $toTerminal = $assignment->toTripStop?->terminal;
                $segmentLabel = ($fromTerminal?->code ?? 'N/A').' → '.($toTerminal?->code ?? 'N/A');
                
                return [
                    'id' => $assignment->id,
                    'from_trip_stop_id' => $assignment->from_trip_stop_id,
                    'to_trip_stop_id' => $assignment->to_trip_stop_id,
                    'from_terminal' => $fromTerminal?->name ?? 'N/A',
                    'from_code' => $fromTerminal?->code ?? 'N/A',
                    'to_terminal' => $toTerminal?->name ?? 'N/A',
                    'to_code' => $toTerminal?->code ?? 'N/A',
                    'segment_label' => $segmentLabel,
                    'bus' => $assignment->bus ? [
                        'id' => $assignment->bus->id,
                        'name' => $assignment->bus->name,
                        'registration_number' => $assignment->bus->registration_number,
                    ] : null,
                    'driver_name' => $assignment->driver_name,
                    'driver_phone' => $assignment->driver_phone,
                    'host_name' => $assignment->host_name,
                ];
            })->toArray();

            $this->tripLoaded = true;
            $this->showTripContent = true;
            $this->loadTripPassengers();
            
            // Dispatch trip-loaded event with tripId for Echo subscription
            $this->dispatch('trip-loaded', ['tripId' => $this->tripId]);
        } catch (\Exception $e) {
            $this->dispatch('show-error', message: $e->getMessage());
        }
    }

    public function loadTripPassengers(): void
    {
        if (! $this->tripId) {
            return;
        }

        $bookings = Booking::query()
            ->where('trip_id', $this->tripId)
            ->where('status', '!=', 'cancelled')
            ->with([
                'passengers' => fn ($q) => $q->orderBy('id'),
                'seats' => fn ($q) => $q->whereNull('cancelled_at')->orderBy('seat_number'),
                'fromStop.terminal',
                'toStop.terminal',
            ])
            ->get();

        $passengers = [];
        $totalEarnings = 0;

        foreach ($bookings as $booking) {
            foreach ($booking->passengers as $passenger) {
                $seatNumbers = $booking->seats
                    ->pluck('seat_number')
                    ->sort()
                    ->values()
                    ->toArray();

                $passengers[] = [
                    'id' => $passenger->id,
                    'booking_id' => $booking->id,
                    'name' => $passenger->name ?? 'N/A',
                    'gender' => $passenger->gender?->value ?? $passenger->gender,
                    'age' => $passenger->age,
                    'cnic' => $passenger->cnic,
                    'phone' => $passenger->phone,
                    'email' => $passenger->email,
                    'seat_numbers' => $seatNumbers,
                    'seats_display' => implode(', ', $seatNumbers),
                    'from_stop' => $booking->fromStop?->terminal?->name ?? 'N/A',
                    'from_code' => $booking->fromStop?->terminal?->code ?? 'N/A',
                    'to_stop' => $booking->toStop?->terminal?->name ?? 'N/A',
                    'to_code' => $booking->toStop?->terminal?->code ?? 'N/A',
                    'status' => $booking->status,
                    'payment_status' => $booking->payment_status,
                    'payment_method' => $booking->payment_method,
                    'booking_number' => $booking->booking_number,
                    'channel' => $booking->channel,
                    'final_amount' => $booking->final_amount,
                ];

                $totalEarnings += $booking->final_amount;
            }
        }

        $this->tripPassengers = $passengers;
        $this->totalEarnings = $totalEarnings;
    }

    public function selectSeat($seatNumber): void
    {
        if ($this->isSeatBooked($seatNumber) || $this->isSeatHeld($seatNumber)) {
            return;
        }

        if (isset($this->selectedSeats[$seatNumber])) {
            // Deselecting seat
            unset($this->selectedSeats[$seatNumber]);
            
            // Broadcast seat unlocked event for other users
            if ($this->tripId) {
                SeatUnlocked::dispatch($this->tripId, [$seatNumber], Auth::user());
            }
            
            // Clear passengers if no seats selected
            if (count($this->selectedSeats) === 0) {
                $this->passengers = [];
            }
        } else {
            // Selecting seat
            $this->selectedSeats[$seatNumber] = [
                'seat_number' => $seatNumber,
                'gender' => null,
            ];
            $this->pendingSeat = $seatNumber;
            
            // Broadcast seat locked event for other users
            if ($this->tripId) {
                SeatLocked::dispatch($this->tripId, [$seatNumber], Auth::user());
            }
            
            $this->dispatch('show-gender-modal', seatNumber: $seatNumber);
        }

        // Ensure at least 1 passenger form exists if seats are selected
        if (count($this->selectedSeats) > 0 && count($this->passengers) === 0) {
            $this->passengers = [
                [
                    'id' => ++$this->passengerCounter,
                    'name' => '',
                    'age' => '',
                    'gender' => '',
                    'cnic' => '',
                    'phone' => '',
                    'email' => '',
                    'is_required' => true,
                ],
            ];
        }

        $this->calculateFinal();
    }

    public function setSeatGender($seatNumber, $gender): void
    {
        if (isset($this->selectedSeats[$seatNumber])) {
            $this->selectedSeats[$seatNumber]['gender'] = $gender;
            
            // Auto-fill first passenger's gender if not set
            if (!empty($this->passengers) && empty($this->passengers[0]['gender'])) {
                $this->passengers[0]['gender'] = $gender;
            }
        }
        $this->pendingSeat = null;
        
        // Dispatch event to close modal
        $this->dispatch('gender-selected');
    }

    public function addPassenger(): void
    {
        // Limit to number of selected seats
        $selectedSeatCount = count($this->selectedSeats);
        $currentPassengerCount = count($this->passengers);
        
        if ($selectedSeatCount === 0) {
            $this->dispatch('show-error', message: 'Please select at least one seat first');
            return;
        }
        
        if ($currentPassengerCount >= $selectedSeatCount) {
            $this->dispatch('show-error', message: "You can add up to {$selectedSeatCount} passenger(s) for {$selectedSeatCount} selected seat(s)");
            return;
        }
        
        $this->passengerCounter++;
        $this->passengers[] = [
            'id' => $this->passengerCounter,
            'name' => '',
            'age' => '',
            'gender' => '',
            'cnic' => '',
            'phone' => '',
            'email' => '',
            'is_required' => false,
        ];
    }

    public function removePassenger($index): void
    {
        if (isset($this->passengers[$index])) {
            // Don't allow removing if it's the only passenger and seats are selected
            if ($this->passengers[$index]['is_required'] && count($this->passengers) === 1 && count($this->selectedSeats) > 0) {
                $this->dispatch('show-error', message: 'At least one passenger is required');
                return;
            }
            
            unset($this->passengers[$index]);
            $this->passengers = array_values($this->passengers);
            
            // Ensure first passenger is marked as required
            if (!empty($this->passengers)) {
                $this->passengers[0]['is_required'] = true;
            }
        }
    }

    public function updatedPaymentMethod(): void
    {
        $this->calculateFinal();
        if ($this->paymentMethod === 'cash') {
            $this->transactionId = null;
        } else {
            $this->amountReceived = 0;
            $this->returnAmount = 0;
        }
    }

    public function updatedAmountReceived(): void
    {
        $this->calculateReturn();
    }

    public function calculateFinal(): void
    {
        $seatCount = count($this->selectedSeats);
        $this->baseFare = $this->fareData ? (float) $this->fareData->final_fare : 0;
        $totalFare = $this->baseFare * $seatCount;
        $discount = $this->discountAmount * $seatCount;

        // Apply mobile wallet tax if payment method is mobile_wallet
        $this->taxAmount = ($this->paymentMethod === 'mobile_wallet') ? 40 * $seatCount : 0;

        $this->finalAmount = $totalFare - $discount + $this->taxAmount;
    }

    public function calculateReturn(): void
    {
        if ($this->paymentMethod === 'cash' && $this->amountReceived > 0) {
            $this->returnAmount = max(0, $this->amountReceived - $this->finalAmount);
        } else {
            $this->returnAmount = 0;
        }
    }

    public function confirmBooking(): void
    {
        $selectedSeatCount = count($this->selectedSeats);
        
        // Validate seats are selected
        if ($selectedSeatCount === 0) {
            $this->dispatch('show-error', message: 'Please select at least one seat');
            return;
        }

        // Ensure at least 1 passenger is provided
        if (count($this->passengers) === 0) {
            $this->updatePassengerForms();
            if (count($this->passengers) === 0) {
                $this->dispatch('show-error', message: 'Please provide at least one passenger information');
                return;
            }
        }

        // Validate all seats have gender set
        foreach ($this->selectedSeats as $seatNumber => $seatData) {
            if (empty($seatData['gender'])) {
                $this->dispatch('show-error', message: "Please select gender for seat {$seatNumber}");
                return;
            }
        }
        
        $this->validate([
            'passengers' => 'required|array|min:1|max:'.$selectedSeatCount,
            'passengers.*.name' => 'required|string|max:100',
            'passengers.*.age' => 'required|integer|min:1|max:120',
            'passengers.*.gender' => 'required|in:male,female',
            'passengers.*.cnic' => 'nullable|string|max:20',
            'passengers.*.phone' => 'nullable|string|max:20',
            'passengers.*.email' => 'nullable|email|max:100',
        ], [
            'passengers.min' => 'Please provide at least one passenger information',
            'passengers.max' => 'You can add up to '.$selectedSeatCount.' passenger(s) for '.$selectedSeatCount.' selected seat(s)',
            'passengers.*.name.required' => 'Passenger name is required',
            'passengers.*.age.required' => 'Passenger age is required',
            'passengers.*.gender.required' => 'Passenger gender is required',
        ]);

        if ($this->bookingType === 'counter' && $this->paymentMethod !== 'cash' && empty($this->transactionId)) {
            $this->dispatch('show-error', message: 'Transaction ID is required for non-cash payments');
            return;
        }

        try {
            DB::beginTransaction();

            $seatNumbers = array_keys($this->selectedSeats);
            $seatsData = [];
            foreach ($this->selectedSeats as $seatNumber => $seatData) {
                $seatsData[] = [
                    'seat_number' => $seatNumber,
                    'gender' => $seatData['gender'],
                ];
            }

            $data = [
                'trip_id' => $this->tripId,
                'from_terminal_id' => $this->fromTerminalId,
                'to_terminal_id' => $this->toTerminalId,
                'from_stop_id' => $this->fromStop['route_stop_id'],
                'to_stop_id' => $this->toStop['route_stop_id'],
                'from_trip_stop_id' => $this->fromStop['trip_stop_id'],
                'to_trip_stop_id' => $this->toStop['trip_stop_id'],
                'terminal_id' => Auth::user()->terminal_id ?? $this->fromTerminalId,
                'seat_numbers' => $seatNumbers,
                'seats_data' => $seatsData,
                'passengers' => $this->passengers,
                'channel' => $this->bookingType === 'counter' ? ChannelEnum::COUNTER->value : ChannelEnum::PHONE->value,
                'payment_method' => $this->paymentMethod ?? 'cash',
                'online_transaction_id' => $this->transactionId ?? null,
                'total_fare' => $this->baseFare * count($seatNumbers),
                'discount_amount' => $this->discountAmount * count($seatNumbers),
                'tax_amount' => $this->taxAmount,
                'final_amount' => $this->finalAmount,
                'notes' => $this->notes,
            ];

            // Set payment fields - for counter bookings use actual values, for phone bookings use 0
            if ($this->bookingType === 'counter') {
                $data['payment_received_from_customer'] = $this->amountReceived ?? 0;
                $data['return_after_deduction_from_customer'] = $this->returnAmount ?? 0;
            } else {
                // Phone bookings don't have payment at booking time
                $data['payment_received_from_customer'] = 0;
                $data['return_after_deduction_from_customer'] = 0;
            }

            // Create booking
            $bookingService = app(BookingService::class);
            $booking = $bookingService->create($data, Auth::user());

            foreach ($seatNumbers as $seat) {
                SeatConfirmed::dispatch($this->tripId, [$seat], Auth::user());
            }

            DB::commit();

            // Unlock seats after successful booking (they're now booked, not just locked)
            foreach ($seatNumbers as $seat) {
                if (isset($this->selectedSeats[$seat])) {
                    SeatUnlocked::dispatch($this->tripId, [$seat], Auth::user());
                }
            }

            // Store last booking data for reprint
            $this->lastBookingId = $booking->id;
            $this->lastBookingData = [
                'booking_number' => $booking->booking_number,
                'booking_id' => $booking->id,
                'seats' => implode(', ', $seatNumbers),
                'total_fare' => $this->baseFare * count($seatNumbers),
                'discount_amount' => $this->discountAmount * count($seatNumbers),
                'tax_amount' => $this->taxAmount,
                'final_amount' => $this->finalAmount,
                'payment_method' => $this->paymentMethod ?? 'cash',
                'status' => $booking->status,
            ];

            // Store booking data before resetting
            $bookingData = [
                'bookingNumber' => $booking->booking_number,
                'bookingId' => $booking->id,
                'seats' => implode(', ', $seatNumbers),
                'totalFare' => $this->baseFare * count($seatNumbers),
                'discountAmount' => $this->discountAmount * count($seatNumbers),
                'taxAmount' => $this->taxAmount,
                'finalAmount' => $this->finalAmount,
                'paymentMethod' => $this->paymentMethod ?? 'cash',
                'status' => $booking->status,
            ];

            $this->resetBookingForm();
            $this->loadTrip(); // Reload trip to update seat map and passengers
            
            // Dispatch event with booking data
            $this->dispatch('booking-success', $bookingData);

        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-error', message: $e->getMessage());
        }
    }

    public function resetBookingForm(): void
    {
        // Unlock any selected seats before clearing
        if ($this->tripId && count($this->selectedSeats) > 0) {
            $seatNumbers = array_keys($this->selectedSeats);
            foreach ($seatNumbers as $seat) {
                SeatUnlocked::dispatch($this->tripId, [$seat], Auth::user());
            }
        }
        
        $this->selectedSeats = [];
        $this->passengers = [
            [
                'id' => 1,
                'name' => '',
                'age' => '',
                'gender' => '',
                'cnic' => '',
                'phone' => '',
                'email' => '',
                'is_required' => true,
            ],
        ];
        $this->amountReceived = 0;
        $this->returnAmount = 0;
        $this->transactionId = null;
        $this->notes = '';
        $this->pendingSeat = null;
    }

    public function resetTripData(): void
    {
        $this->tripId = null;
        $this->tripData = null;
        $this->routeData = null;
        $this->fromStop = null;
        $this->toStop = null;
        $this->seatMap = [];
        $this->availableSeats = [];
        $this->busAssignments = [];
        $this->selectedSeats = [];
        $this->tripPassengers = [];
        $this->totalEarnings = 0;
    }

    private function updatePassengerForms(): void
    {
        // If no seats selected, clear all passengers
        if (count($this->selectedSeats) === 0) {
            $this->passengers = [];
            return;
        }

        // Ensure at least 1 passenger form exists
        if (count($this->passengers) === 0) {
            $this->passengers = [
                [
                    'id' => ++$this->passengerCounter,
                    'name' => '',
                    'age' => '',
                    'gender' => '',
                    'cnic' => '',
                    'phone' => '',
                    'email' => '',
                    'is_required' => true,
                ],
            ];
        }

        // Ensure first passenger is marked as required
        if (!empty($this->passengers)) {
            $this->passengers[0]['is_required'] = true;
        }
    }

    private function removePassengerForSeat($seatNumber): void
    {
        // Find the index of this seat in selectedSeats array
        $seatIndex = array_search($seatNumber, array_keys($this->selectedSeats));
        
        // If seat was found and removed, remove corresponding passenger
        // Note: updatePassengerForms() will handle the actual removal by matching count
        // This method is called before updatePassengerForms(), so the seat is already removed
        // from selectedSeats, so we need to find which passenger index corresponds to removed seat
        
        // Since seats are removed before this is called, we need to find it differently
        // The updatePassengerForms() will handle the synchronization
    }

    private function buildSeatMap(Trip $trip, TripStop $fromStop, TripStop $toStop, int $total, array $available): array
    {
        $seatMap = [];
        $bookedSeats = $this->getBookedSeats($trip, $fromStop, $toStop);

        for ($i = 1; $i <= $total; $i++) {
            $seatMap[$i] = [
                'number' => $i,
                'status' => 'available',
            ];

            if (isset($bookedSeats[$i])) {
                $seatMap[$i]['status'] = $bookedSeats[$i]['status'];
                $seatMap[$i]['booking_id'] = $bookedSeats[$i]['booking_id'];
                $seatMap[$i]['gender'] = $bookedSeats[$i]['gender'];
            } elseif (! in_array($i, $available)) {
                $seatMap[$i]['status'] = 'held';
            }
        }

        return $seatMap;
    }

    private function getBookedSeats(Trip $trip, TripStop $fromStop, TripStop $toStop): array
    {
        $bookings = Booking::with(['seats', 'passengers', 'fromStop:id,sequence', 'toStop:id,sequence'])
            ->where('trip_id', $trip->id)
            ->whereIn('status', array_merge(
                array_column(\App\Enums\BookingStatusEnum::cases(), 'value'),
                ['checked_in', 'boarded']
            ))
            ->get();

        $bookedSeats = [];
        $queryFrom = $fromStop->sequence ?? null;
        $queryTo = $toStop->sequence ?? null;

        if ($queryFrom === null || $queryTo === null) {
            return $bookedSeats;
        }

        foreach ($bookings as $booking) {
            if (! $booking->fromStop || ! $booking->toStop) {
                continue;
            }

            $bookingFrom = $booking->fromStop->sequence ?? null;
            $bookingTo = $booking->toStop->sequence ?? null;

            if ($bookingFrom === null || $bookingTo === null) {
                continue;
            }

            if ($bookingFrom < $queryTo && $queryFrom < $bookingTo) {
                foreach ($booking->seats->whereNull('cancelled_at') as $seat) {
                    $gender = null;

                    if ($seat->gender) {
                        if ($seat->gender instanceof \App\Enums\GenderEnum) {
                            $gender = $seat->gender->value;
                        } elseif (is_string($seat->gender)) {
                            $gender = $seat->gender;
                        }
                    }

                    if (! $gender && $booking->passengers->isNotEmpty()) {
                        $passenger = $booking->passengers->first();
                        if ($passenger && $passenger->gender) {
                            if ($passenger->gender instanceof \App\Enums\GenderEnum) {
                                $gender = $passenger->gender->value;
                            } elseif (is_string($passenger->gender)) {
                                $gender = $passenger->gender;
                            }
                        }
                    }

                    $bookedSeats[$seat->seat_number] = [
                        'status' => $booking->status === 'hold' ? 'held' : 'booked',
                        'booking_id' => $booking->id,
                        'gender' => $gender,
                    ];
                }
            }
        }

        return $bookedSeats;
    }

    private function isSeatBooked($seatNumber): bool
    {
        return isset($this->seatMap[$seatNumber]) && $this->seatMap[$seatNumber]['status'] === 'booked';
    }

    private function isSeatHeld($seatNumber): bool
    {
        // Check if seat is held (from database/bookings) or locked by another user
        $isHeldInMap = isset($this->seatMap[$seatNumber]) && $this->seatMap[$seatNumber]['status'] === 'held';
        $isLockedByOtherUser = isset($this->lockedSeats[$seatNumber]) && $this->lockedSeats[$seatNumber] != Auth::id();
        
        return $isHeldInMap || $isLockedByOtherUser;
    }

    public function handleSeatLocked($tripId, $seatNumbers, $userId): void
    {
        if ($tripId != $this->tripId) {
            return;
        }

        // Don't update locked seats if it's the current user (we already know about our own selections)
        if ($userId == Auth::id()) {
            return;
        }

        foreach ($seatNumbers as $seat) {
            $this->lockedSeats[$seat] = $userId;
        }
        
        // Force Livewire to update the UI
        $this->dispatch('seat-locked-updated');
    }

    public function handleSeatUnlocked($tripId, $seatNumbers, $userId): void
    {
        if ($tripId != $this->tripId) {
            return;
        }

        // Don't update locked seats if it's the current user (we already know about our own selections)
        if ($userId == Auth::id()) {
            return;
        }

        foreach ($seatNumbers as $seat) {
            unset($this->lockedSeats[$seat]);
        }
        
        // Force Livewire to update the UI
        $this->dispatch('seat-unlocked-updated');
    }

    public function handleSeatConfirmed($tripId, $seatNumbers, $userId): void
    {
        if ($tripId != $this->tripId) {
            return;
        }

        $this->loadTrip();
    }

    public function render()
    {
        $user = Auth::user();
        $isAdmin = $user->hasRole('admin');
        $generalSettings = GeneralSetting::first();
        $minDate = Carbon::today();
        $maxDate = $generalSettings && $generalSettings->advance_booking_enable
            ? Carbon::today()->addDays($generalSettings->advance_booking_days ?? 7)
            : Carbon::today();

        return view('livewire.admin.booking-console', [
            'isAdmin' => $isAdmin,
            'minDate' => $minDate->format('Y-m-d'),
            'maxDate' => $maxDate->format('Y-m-d'),
        ]);
    }
}
