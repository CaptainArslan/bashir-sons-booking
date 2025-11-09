<?php

namespace App\Livewire\Admin;

use App\Enums\ChannelEnum;
use App\Enums\ExpenseTypeEnum;
use App\Enums\PaymentMethodEnum;
use App\Enums\TerminalEnum;
use App\Events\SeatConfirmed;
use App\Events\SeatLocked;
use App\Events\SeatUnlocked;
use App\Models\Booking;
use App\Models\Bus;
use App\Models\Expense;
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

    public $tripDataForJs = null; // JavaScript-accessible trip data

    public $routeData = null;

    public $fromStop = null;

    public $toStop = null;

    public $seatMap = [];

    public $availableSeats = [];

    public $seatCount = 44; // Default seat count, will be updated from bus

    // Seat Selection
    public $selectedSeats = [];

    public $pendingSeat = null;

    // Fare Data
    public $fareData = null;

    public $baseFare = 0;

    public $discountAmount = 0;

    public $taxAmount = 0;

    public $totalFare = 0;

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

    // Bus Assignment Modal
    public $showBusAssignmentModal = false;

    public $availableBuses = [];

    public $selectedBusId = null;

    public $driverName = '';

    public $driverPhone = '';

    public $driverCnic = '';

    public $driverLicense = '';

    public $driverAddress = '';

    public $hostName = '';

    public $hostPhone = '';

    public $expenses = [];

    public $expenseTypes = [];

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
        $this->expenseTypes = ExpenseTypeEnum::options();

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
            // [
            //     'id' => 1,
            //     'name' => '',
            //     'age' => '',
            //     'gender' => '',
            //     'cnic' => '',
            //     'phone' => '',
            //     'email' => '',
            //     'is_required' => true,
            // ],
        ];
    }

    public function updatedFromTerminalId(): void
    {
        $this->toTerminalId = null;
        $this->departureTimeId = null;
        $this->arrivalTime = null;
        $this->toTerminals = [];
        $this->departureTimes = [];
        $this->showTripContent = false;
        $this->tripLoaded = false;
        $this->resetTripData();
        $this->fareError = null;
        $this->fareData = null;
        $this->fareValid = false;

        if ($this->fromTerminalId) {
            $this->loadToTerminals();
        }
    }

    public function updatedToTerminalId(): void
    {
        $this->departureTimeId = null;
        $this->arrivalTime = null;
        $this->departureTimes = [];
        $this->showTripContent = false;
        $this->tripLoaded = false;
        $this->resetTripData();
        $this->fareError = null;
        $this->fareData = null;
        $this->fareValid = false;

        if ($this->fromTerminalId && $this->toTerminalId) {
            $this->loadDepartureTimes();
            $this->loadFare();
        } else {
            $this->departureTimes = [];
        }
    }

    public function updatedDepartureTimeId(): void
    {
        $this->updateArrivalTime();
    }

    public function updatedTravelDate(): void
    {
        // Reset departure time when date changes
        $this->departureTimeId = null;
        $this->arrivalTime = null;
        $this->showTripContent = false;
        $this->tripLoaded = false;
        $this->resetTripData();
        $this->fareError = null;

        if ($this->fromTerminalId && $this->toTerminalId) {
            $this->loadDepartureTimes();
        } else {
            $this->departureTimes = [];
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

    // public function loadDepartureTimes(): void
    // {
    //     if (! $this->fromTerminalId || ! $this->toTerminalId || ! $this->travelDate) {
    //         return;
    //     }

    //     $selectedDate = $this->travelDate;
    //     $now = now();

    //     $timetableStops = [];

    //     $timetableStopsQuery = TimetableStop::where('terminal_id', $this->fromTerminalId)
    //         ->where('is_active', true)
    //         ->with('timetable.route')
    //         ->get();

    //     foreach ($timetableStopsQuery as $ts) {
    //         if (! $ts->timetable || ! $ts->timetable->route) {
    //             continue;
    //         }

    //         $routeStops = RouteStop::where('route_id', $ts->timetable->route->id)
    //             ->orderBy('sequence')
    //             ->get();

    //         $fromStop = $routeStops->firstWhere('terminal_id', $this->fromTerminalId);
    //         $toStop = $routeStops->firstWhere('terminal_id', $this->toTerminalId);

    //         if (! $fromStop || ! $toStop || $fromStop->sequence >= $toStop->sequence) {
    //             continue;
    //         }

    //         if ($ts->departure_time) {
    //             $fullDeparture = Carbon::parse($selectedDate.' '.$ts->departure_time);

    //             // Allow past times for admin users, or if it's today's date
    //             $isToday = Carbon::parse($selectedDate)->isToday();

    //             if ($isToday || $fullDeparture->greaterThanOrEqualTo($now)) {
    //                 $timetableStops[] = [
    //                     'id' => $ts->id,
    //                     'timetable_id' => $ts->timetable_id,
    //                     'departure_at' => $ts->departure_time,
    //                     'arrival_at' => $ts->arrival_time,
    //                     'route_id' => $ts->timetable->route->id,
    //                     'route_name' => $ts->timetable->route->name,
    //                     'full_departure' => $fullDeparture->toDateTimeString(),
    //                 ];
    //             }
    //         }
    //     }

    //     $this->departureTimes = collect($timetableStops)
    //         ->sortBy('full_departure')
    //         ->values()
    //         ->toArray();
    // }

    public function loadDepartureTimes(): void
    {
        if (! $this->fromTerminalId || ! $this->toTerminalId || ! $this->travelDate) {
            return;
        }

        $selectedDate = Carbon::parse($this->travelDate);
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
                $fullDeparture = Carbon::parse($selectedDate->format('Y-m-d') . ' ' . $ts->departure_time);

                // Only include departures greater than or equal to the current moment
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
                ->with(['fromTerminal:id,name,code', 'toTerminal:id,name,code'])
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
            $this->discountAmount = (float) $fare->getDiscountAmount();
            $this->fareValid = true;
            $this->fareError = null;

            // Reset fare-related values
            $this->totalFare = 0;
            $this->taxAmount = 0;
            $this->finalAmount = 0;

            // Recalculate if seats are already selected
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

            $trip->load(['stops.terminal:id,name,code', 'originStop', 'bus.busLayout']);

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

            // Store seat count from bus (if assigned) or use default
            $this->seatCount = $seatCount;

            $seatMap = $this->buildSeatMap($trip, $tripFromStop, $tripToStop, $seatCount, $availableSeats);

            $this->tripId = $trip->id;
            $this->tripData = $trip;
            $this->updateTripDataForJs(); // Update JavaScript-accessible data
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

            // Recalculate fare if seats are already selected
            if (count($this->selectedSeats) > 0) {
                $this->calculateFinal();
            }

            $this->tripLoaded = true;
            $this->showTripContent = true;
            $this->loadTripPassengers();

            // Dispatch trip-loaded event with tripId for Echo subscription
            $this->dispatch('trip-loaded', ['tripId' => $this->tripId]);
        } catch (\Exception $e) {
            $this->dispatch('show-error', message: $this->getUserFriendlyErrorMessage($e));
        }
    }

    public function loadTripPassengers(): void
    {
        if (! $this->tripId || ! $this->fromStop || ! $this->toStop) {
            return;
        }

        // Get the selected from and to terminal sequences for filtering
        $selectedFromSequence = $this->fromStop['sequence'] ?? null;
        $selectedToSequence = $this->toStop['sequence'] ?? null;

        if ($selectedFromSequence === null || $selectedToSequence === null) {
            return;
        }

        // Get all bookings for this trip
        $bookings = Booking::query()
            ->where('trip_id', $this->tripId)
            ->where('status', '!=', 'cancelled')
            ->with([
                'passengers' => fn($q) => $q->orderBy('id'),
                'seats' => fn($q) => $q->whereNull('cancelled_at')->orderBy('seat_number'),
                'fromStop:id,sequence,terminal_id',
                'toStop:id,sequence,terminal_id',
                'fromStop.terminal:id,name,code',
                'toStop.terminal:id,name,code',
            ])
            ->get();

        $passengers = [];
        $totalEarnings = 0;

        foreach ($bookings as $booking) {
            // Filter: Only show bookings that travel within the selected segment
            // Booking's from_stop sequence should be >= selected from sequence
            // AND booking's to_stop sequence should be <= selected to sequence
            $bookingFromSeq = $booking->fromStop?->sequence ?? null;
            $bookingToSeq = $booking->toStop?->sequence ?? null;

            if ($bookingFromSeq === null || $bookingToSeq === null) {
                continue;
            }

            // Skip bookings that start before the selected terminal
            if ($bookingFromSeq < $selectedFromSequence) {
                continue;
            }

            // Skip bookings that end after the selected terminal
            if ($bookingToSeq > $selectedToSequence) {
                continue;
            }

            // Get all active seats for this booking
            $seats = $booking->seats->sortBy('seat_number')->values();
            $passengerList = $booking->passengers->sortBy('id')->values();

            // Create one row per seat and calculate earnings per seat
            foreach ($seats as $seatIndex => $seat) {
                // Map seat to passenger by index (first seat = first passenger, etc.)
                $passenger = $passengerList[$seatIndex] ?? $passengerList[0] ?? null;

                if ($passenger) {
                    $seatAmount = $seat->final_amount ?? 0;

                    $passengers[] = [
                        'id' => $passenger->id,
                        'seat_id' => $seat->id,
                        'booking_id' => $booking->id,
                        'seat_number' => $seat->seat_number,
                        'name' => $passenger->name ?? 'N/A',
                        'gender' => $passenger->gender?->value ?? $passenger->gender,
                        'age' => $passenger->age,
                        'cnic' => $passenger->cnic,
                        'phone' => $passenger->phone,
                        'email' => $passenger->email,
                        'from_stop' => $booking->fromStop?->terminal?->name ?? 'N/A',
                        'from_code' => $booking->fromStop?->terminal?->code ?? 'N/A',
                        'to_stop' => $booking->toStop?->terminal?->name ?? 'N/A',
                        'to_code' => $booking->toStop?->terminal?->code ?? 'N/A',
                        'status' => $booking->status,
                        'payment_status' => $booking->payment_status,
                        'payment_method' => $booking->payment_method,
                        'booking_number' => $booking->booking_number,
                        'channel' => $booking->channel,
                        'final_amount' => $seatAmount, // Per-seat amount
                    ];

                    // Add seat amount to total earnings (sum of all seat amounts in the selected segment)
                    $totalEarnings += $seatAmount;
                }
            }
        }

        // Sort by seat number
        usort($passengers, function ($a, $b) {
            return (int) $a['seat_number'] <=> (int) $b['seat_number'];
        });

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

        // Recalculate fare when seats change
        $this->calculateFinal();
    }

    public function setSeatGender($seatNumber, $gender): void
    {
        if (isset($this->selectedSeats[$seatNumber])) {
            $this->selectedSeats[$seatNumber]['gender'] = $gender;

            // Auto-fill first passenger's gender if not set
            if (! empty($this->passengers) && empty($this->passengers[0]['gender'])) {
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
            if (! empty($this->passengers)) {
                $this->passengers[0]['is_required'] = true;
            }
        }
    }

    public function updatedPaymentMethod(): void
    {
        // Recalculate fare (which will handle tax based on payment method)
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

        // Always ensure baseFare is set from fareData if available
        if ($this->fareData) {
            $this->baseFare = (float) $this->fareData->final_fare;
            $this->discountAmount = (float) $this->fareData->getDiscountAmount();
        }

        // Ensure all values are floats
        $this->baseFare = (float) $this->baseFare;
        $this->discountAmount = (float) $this->discountAmount;
        $this->taxAmount = (float) $this->taxAmount;

        // Calculate total fare (base fare * number of seats)
        $this->totalFare = $this->baseFare * $seatCount;

        // Calculate total discount (discount per seat * number of seats)
        $totalDiscount = $this->discountAmount * $seatCount;

        // Handle tax calculation
        if ($seatCount > 0) {
            // Auto-apply mobile wallet tax if payment method is mobile_wallet
            if ($this->paymentMethod === 'mobile_wallet') {
                // Only auto-set if tax is 0 or matches the auto-calculated value
                $autoTax = 40 * $seatCount;
                if ($this->taxAmount == 0 || $this->taxAmount == $autoTax) {
                    $this->taxAmount = $autoTax;
                }
                // If user manually changed tax, keep their value
            } else {
                // For non-mobile_wallet, only reset if it's the auto-calculated mobile_wallet tax
                $autoMobileWalletTax = 40 * $seatCount;
                if ($this->taxAmount == $autoMobileWalletTax) {
                    $this->taxAmount = 0;
                }
                // Otherwise keep user's manual tax value
            }
        } else {
            // No seats selected, reset everything
            $this->totalFare = 0;
            $this->taxAmount = 0;
            $this->finalAmount = 0;

            return;
        }

        // Calculate final amount: Total Fare - Discount + Tax
        $this->finalAmount = (float) $this->totalFare - (float) $totalDiscount + (float) $this->taxAmount;

        // Ensure final amount is never negative
        if ($this->finalAmount < 0) {
            $this->finalAmount = 0;
        }
    }

    public function updatedTaxAmount(): void
    {
        $this->calculateFinal();
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
            'passengers' => 'required|array|min:1|max:' . $selectedSeatCount,
            'passengers.*.name' => 'required|string|max:100',
            'passengers.*.age' => 'required|integer|min:1|max:120',
            'passengers.*.gender' => 'required|in:male,female',
            'passengers.*.cnic' => 'nullable|string|max:20',
            'passengers.*.phone' => 'nullable|string|max:20',
            'passengers.*.email' => 'nullable|email|max:100',
        ], [
            'passengers.min' => 'Please provide at least one passenger information',
            'passengers.max' => 'You can add up to ' . $selectedSeatCount . ' passenger(s) for ' . $selectedSeatCount . ' selected seat(s)',
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

            // Store last booking data for reprint and modal display
            $seatCount = count($seatNumbers);
            $totalDiscount = $this->discountAmount * $seatCount;

            $this->lastBookingId = $booking->id;
            $this->lastBookingData = [
                'booking_number' => $booking->booking_number,
                'booking_id' => $booking->id,
                'seats' => implode(', ', $seatNumbers),
                'total_fare' => $this->totalFare,
                'discount_amount' => $totalDiscount,
                'tax_amount' => $this->taxAmount,
                'final_amount' => $this->finalAmount,
                'payment_method' => $this->paymentMethod ?? 'cash',
                'status' => $booking->status,
            ];

            // Reset booking form but keep passenger forms ready for next booking
            $this->resetBookingForm();

            // Reload trip to update seat map and passengers
            $this->loadTrip();

            // Dispatch event to show modal - data is already stored in lastBookingData
            $this->dispatch('booking-success');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-error', message: $this->getUserFriendlyErrorMessage($e));
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

        // Reset seat selection
        $this->selectedSeats = [];
        $this->pendingSeat = null;

        // DO NOT reset route-based fare calculations - they should remain until trip/route changes
        // Keep: baseFare, discountAmount, fareData (these are route-based)
        // Reset: taxAmount (user-entered), totalFare and finalAmount will be recalculated

        // Reset payment fields - clear all payment information for next booking
        $this->amountReceived = 0.00; // Reset amount received from customer to zero
        $this->returnAmount = 0.00; // Reset return amount to zero
        $this->transactionId = null; // Clear transaction ID
        $this->paymentMethod = 'cash'; // Reset to default payment method
        $this->bookingType = 'counter'; // Reset to default booking type
        $this->notes = ''; // Clear notes
        $this->taxAmount = 0.00; // Reset tax/charges to zero (user-entered value)

        // dd($this->amountReceived, $this->returnAmount, $this->transactionId, $this->paymentMethod, $this->bookingType, $this->notes, $this->taxAmount);
        // Reset passenger information - completely clear all passengers and create fresh empty form
        // Use a new ID to force Livewire to re-render the passenger forms
        $this->passengerCounter = 1;
        $this->passengers = [
            // [
            //     'id' => $this->passengerCounter,
            //     'name' => '',
            //     'age' => '',
            //     'gender' => '',
            //     'cnic' => '',
            //     'phone' => '',
            //     'email' => '',
            //     'is_required' => true,
            // ],
        ];

        // Recalculate final amount with reset values (no seats selected, taxAmount = 0)
        // This will set totalFare = 0 and finalAmount = 0 automatically
        $this->calculateFinal();

        // Force Livewire to update the view
        $this->dispatch('form-reset');
    }

    public function resetTripData(): void
    {
        $this->tripId = null;
        $this->tripData = null;
        $this->tripDataForJs = null; // Reset JavaScript-accessible data
        $this->routeData = null;
        $this->fromStop = null;
        $this->toStop = null;
        $this->seatMap = [];
        $this->availableSeats = [];
        $this->seatCount = 44; // Reset to default
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
        if (! empty($this->passengers)) {
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

        // Create a set of available seats for faster lookup
        $availableSet = array_flip($available);

        for ($i = 1; $i <= $total; $i++) {
            $seatMap[$i] = [
                'number' => $i,
                'status' => 'available',
            ];

            // If seat is booked for this segment, show booking details
            if (isset($bookedSeats[$i])) {
                $seatMap[$i]['status'] = $bookedSeats[$i]['status'];
                $seatMap[$i]['booking_id'] = $bookedSeats[$i]['booking_id'];
                $seatMap[$i]['gender'] = $bookedSeats[$i]['gender'];
            }
            // If seat is not in available list, it means it's booked for an overlapping segment
            // Mark it as booked (not available for this segment)
            elseif (! isset($availableSet[$i])) {
                $seatMap[$i]['status'] = 'booked';
            }
            // Otherwise, seat is available
        }

        return $seatMap;
    }

    private function getBookedSeats(Trip $trip, TripStop $fromStop, TripStop $toStop): array
    {
        // Use the same scope as AvailabilityService for consistency
        $bookings = Booking::with(['seats' => function ($query) {
            $query->whereNull('cancelled_at');
        }, 'passengers', 'fromStop:id,sequence', 'toStop:id,sequence'])
            ->where('trip_id', $trip->id)
            ->activeForAvailability()
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

    public function openBusAssignmentModal(): void
    {
        if (! $this->tripId) {
            $this->dispatch('show-error', message: 'Please load a trip first');

            return;
        }

        // Check if current stop is the origin
        if (! $this->isOriginStop()) {
            $this->dispatch('show-error', message: 'Bus assignment can only be done at the starting point of the trip');

            return;
        }

        // Load available buses
        $this->availableBuses = Bus::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'registration_number', 'model']);

        // Load existing trip data if bus is already assigned
        if ($this->tripData?->bus_id) {
            $this->selectedBusId = $this->tripData->bus_id;
            $this->driverName = $this->tripData->driver_name ?? '';
            $this->driverPhone = $this->tripData->driver_phone ?? '';
            $this->driverCnic = $this->tripData->driver_cnic ?? '';
            $this->driverLicense = $this->tripData->driver_license ?? '';
            $this->driverAddress = $this->tripData->driver_address ?? '';

            // Extract host information from trip notes
            if ($this->tripData->notes) {
                $hostMatch = preg_match('/Host:\s*([^(]+)(?:\s*\(([^)]+)\))?/i', $this->tripData->notes, $matches);
                if ($hostMatch && isset($matches[1])) {
                    $this->hostName = trim($matches[1]);
                    $this->hostPhone = isset($matches[2]) ? trim($matches[2]) : '';
                }
            }

            // Load existing expenses for this trip from the current stop to next stop
            $nextStop = $this->getNextStop();
            $fromTerminalId = $this->fromStop['terminal_id'] ?? null;
            $toTerminalId = $nextStop ? ($nextStop->terminal_id ?? null) : null;

            // Load expenses that match the current segment
            $existingExpenses = Expense::where('trip_id', $this->tripId)
                ->where('from_terminal_id', $fromTerminalId)
                ->when($toTerminalId, function ($query) use ($toTerminalId) {
                    $query->where('to_terminal_id', $toTerminalId);
                })
                ->orderBy('created_at')
                ->get();

            if ($existingExpenses->isNotEmpty()) {
                $this->expenses = $existingExpenses->map(function ($expense) {
                    return [
                        'expense_type' => $expense->expense_type instanceof \App\Enums\ExpenseTypeEnum
                            ? $expense->expense_type->value
                            : $expense->expense_type,
                        'amount' => (string) $expense->amount,
                        'description' => $expense->description ?? '',
                    ];
                })->toArray();
            } else {
                // Initialize expenses array with one empty expense if no existing expenses
                $this->expenses = [
                    [
                        'expense_type' => '',
                        'amount' => '',
                        'description' => '',
                    ],
                ];
            }
        } else {
            // Initialize expenses array with one empty expense if no bus assigned
            $this->expenses = [
                [
                    'expense_type' => '',
                    'amount' => '',
                    'description' => '',
                ],
            ];
        }

        $this->showBusAssignmentModal = true;

        // Dispatch event to show modal
        $this->dispatch('open-bus-assignment-modal');
    }

    public function closeBusAssignmentModal(): void
    {
        $this->showBusAssignmentModal = false;
        $this->resetBusAssignmentForm();

        // Dispatch event to close modal
        $this->dispatch('close-bus-assignment-modal');
    }

    public function addExpense(): void
    {
        $this->expenses[] = [
            'expense_type' => '',
            'amount' => '',
            'description' => '',
        ];
    }

    public function removeExpense($index): void
    {
        if (isset($this->expenses[$index])) {
            unset($this->expenses[$index]);
            $this->expenses = array_values($this->expenses);
        }
    }

    public function assignBus(): void
    {
        $this->validate([
            'selectedBusId' => 'required|exists:buses,id',
            'driverName' => 'required|string|max:255',
            'driverPhone' => 'required|string|max:20',
            'driverCnic' => 'required|string|max:50',
            'driverLicense' => 'required|string|max:100',
            'driverAddress' => 'nullable|string|max:500',
            'hostName' => 'nullable|string|max:255',
            'hostPhone' => 'nullable|string|max:20',
            'expenses' => 'nullable|array',
            'expenses.*.expense_type' => 'required_with:expenses.*.amount|in:' . implode(',', array_column(ExpenseTypeEnum::cases(), 'value')),
            'expenses.*.amount' => 'required_with:expenses.*.expense_type|numeric|min:0',
            'expenses.*.description' => 'nullable|string|max:500',
        ], [
            'selectedBusId.required' => 'Please select a bus',
            'driverName.required' => 'Driver name is required',
            'driverPhone.required' => 'Driver phone is required',
            'driverCnic.required' => 'Driver CNIC is required',
            'driverLicense.required' => 'Driver license is required',
        ]);

        try {
            DB::beginTransaction();

            $trip = Trip::findOrFail($this->tripId);

            // Update trip with bus and driver information
            $trip->update([
                'bus_id' => $this->selectedBusId,
                'driver_name' => $this->driverName,
                'driver_phone' => $this->driverPhone,
                'driver_cnic' => $this->driverCnic,
                'driver_license' => $this->driverLicense,
                'driver_address' => $this->driverAddress ?? null,
            ]);

            // Store host information in notes (since there's no host field in trips table)
            // Or we could add it to notes as JSON
            if ($this->hostName || $this->hostPhone) {
                $hostInfo = [];
                if ($this->hostName) {
                    $hostInfo['host_name'] = $this->hostName;
                }
                if ($this->hostPhone) {
                    $hostInfo['host_phone'] = $this->hostPhone;
                }
                // Append to existing notes
                $existingNotes = $trip->notes ?? '';
                $hostNotes = 'Host: ' . ($this->hostName ?? 'N/A') . ($this->hostPhone ? ' (' . $this->hostPhone . ')' : '');
                $trip->notes = $existingNotes ? $existingNotes . "\n" . $hostNotes : $hostNotes;
                $trip->save();
            }

            // Get next stop for expenses
            $nextStop = $this->getNextStop();
            $fromTerminalId = $this->fromStop['terminal_id'] ?? null;
            $toTerminalId = $nextStop ? ($nextStop->terminal_id ?? null) : null;

            // Create expenses if provided
            if (! empty($this->expenses)) {
                foreach ($this->expenses as $expenseData) {
                    // Skip empty expenses
                    if (empty($expenseData['expense_type']) || empty($expenseData['amount'])) {
                        continue;
                    }

                    Expense::create([
                        'trip_id' => $trip->id,
                        'user_id' => Auth::id(),
                        'expense_type' => $expenseData['expense_type'],
                        'amount' => $expenseData['amount'],
                        'from_terminal_id' => $fromTerminalId,
                        'to_terminal_id' => $toTerminalId,
                        'description' => $expenseData['description'] ?? null,
                        'expense_date' => $trip->departure_date,
                    ]);
                }
            }

            DB::commit();

            // Reload trip with new bus assignment
            $trip->refresh();
            $trip->load(['stops.terminal:id,name,code', 'originStop', 'bus.busLayout']);

            // Get updated seat count from the newly assigned bus
            $availabilityService = app(AvailabilityService::class);
            $seatCount = $availabilityService->seatCount($trip);

            // Get trip stops for seat map
            $tripFromStop = $trip->stops->firstWhere('terminal_id', $this->fromStop['terminal_id'] ?? null);
            $tripToStop = $trip->stops->firstWhere('terminal_id', $this->toStop['terminal_id'] ?? null);

            if ($tripFromStop && $tripToStop) {
                // Get available seats
                $availableSeats = $availabilityService->availableSeats(
                    $trip->id,
                    $tripFromStop->id,
                    $tripToStop->id
                );

                // Update seat count
                $this->seatCount = $seatCount;

                // Rebuild seat map with new bus seat count
                $this->seatMap = $this->buildSeatMap($trip, $tripFromStop, $tripToStop, $seatCount, $availableSeats);
                $this->availableSeats = $availableSeats;

                // Update trip data
                $this->tripData = $trip;
                $this->updateTripDataForJs(); // Update JavaScript-accessible data
            }

            $this->closeBusAssignmentModal();

            // Dispatch event to refresh the seat map in the view
            $this->dispatch('seat-map-updated');

            $this->dispatch('show-success', message: 'Bus, driver, host, and expenses assigned successfully! Seat map updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            $this->dispatch('show-error', message: $this->getUserFriendlyErrorMessage($e));
        }
    }

    private function resetBusAssignmentForm(): void
    {
        $this->selectedBusId = null;
        $this->driverName = '';
        $this->driverPhone = '';
        $this->driverCnic = '';
        $this->driverLicense = '';
        $this->driverAddress = '';
        $this->hostName = '';
        $this->hostPhone = '';
        $this->expenses = [];
    }

    private function isOriginStop(): bool
    {
        if (! $this->tripData || ! $this->fromStop) {
            return false;
        }

        // Check if current fromStop is the origin stop
        $originStop = $this->tripData->originStop;
        if (! $originStop) {
            return false;
        }

        return $originStop->id === ($this->fromStop['trip_stop_id'] ?? null);
    }

    private function getNextStop(): ?TripStop
    {
        if (! $this->tripData || ! $this->fromStop) {
            return null;
        }

        $currentSequence = $this->fromStop['sequence'] ?? null;
        if ($currentSequence === null) {
            return null;
        }

        // Get the next stop after current stop
        return TripStop::where('trip_id', $this->tripId)
            ->where('sequence', '>', $currentSequence)
            ->orderBy('sequence')
            ->first();
    }

    public function updatedBaseFare(): void
    {
        $this->calculateFinal();
    }

    public function updatedDiscountAmount(): void
    {
        $this->calculateFinal();
    }

    public function updatedSelectedSeats(): void
    {
        $this->calculateFinal();
    }

    /**
     * Update trip data formatted for JavaScript access
     * This ensures all relationships are properly serialized
     * Called whenever tripData changes
     */
    private function updateTripDataForJs(): void
    {
        if (! $this->tripData) {
            $this->tripDataForJs = null;

            return;
        }

        $trip = $this->tripData;

        // Ensure relationships are loaded
        if (! $trip->relationLoaded('bus')) {
            $trip->load('bus.busLayout');
        }
        if (! $trip->relationLoaded('stops')) {
            $trip->load('stops.terminal:id,name,code');
        }

        $this->tripDataForJs = [
            'id' => $trip->id,
            'bus_id' => $trip->bus_id,
            'departure_datetime' => $trip->departure_datetime?->format('Y-m-d H:i:s'),
            'estimated_arrival_datetime' => $trip->estimated_arrival_datetime?->format('Y-m-d H:i:s'),
            'driver_name' => $trip->driver_name,
            'driver_phone' => $trip->driver_phone,
            'driver_cnic' => $trip->driver_cnic,
            'driver_license' => $trip->driver_license,
            'driver_address' => $trip->driver_address,
            'notes' => $trip->notes,
            'bus' => $trip->bus ? [
                'id' => $trip->bus->id,
                'name' => $trip->bus->name,
                'registration_number' => $trip->bus->registration_number,
                'model' => $trip->bus->model,
                'bus_layout' => $trip->bus->busLayout ? [
                    'id' => $trip->bus->busLayout->id,
                    'total_seats' => $trip->bus->busLayout->total_seats ?? null,
                ] : null,
            ] : null,
            'stops' => $trip->stops ? $trip->stops->map(function ($stop) {
                return [
                    'id' => $stop->id,
                    'terminal_id' => $stop->terminal_id,
                    'sequence' => $stop->sequence,
                    'arrival_at' => $stop->arrival_at?->format('Y-m-d H:i:s'),
                    'departure_at' => $stop->departure_at?->format('Y-m-d H:i:s'),
                    'terminal' => $stop->terminal ? [
                        'id' => $stop->terminal->id,
                        'name' => $stop->terminal->name,
                        'code' => $stop->terminal->code,
                    ] : null,
                ];
            })->toArray() : [],
        ];
    }

    /**
     * Convert database/technical errors to user-friendly messages
     */
    private function getUserFriendlyErrorMessage(\Exception $e): string
    {
        $message = $e->getMessage();

        // Database connection errors
        if (str_contains($message, 'SQLSTATE') || str_contains($message, 'connection') || str_contains($message, 'Connection refused')) {
            return 'Unable to connect to the database. Please try again or contact support if the problem persists.';
        }

        // Foreign key constraint errors
        if (str_contains($message, 'foreign key constraint') || str_contains($message, 'FOREIGN KEY')) {
            return 'This operation cannot be completed because it is linked to other records. Please check related data and try again.';
        }

        // Unique constraint errors
        if (str_contains($message, 'Duplicate entry') || str_contains($message, 'UNIQUE constraint')) {
            return 'This record already exists. Please use a different value.';
        }

        // Integrity constraint errors
        if (str_contains($message, 'integrity constraint') || str_contains($message, 'Integrity constraint')) {
            return 'The data you entered conflicts with existing records. Please check your input and try again.';
        }

        // General database errors
        if (str_contains($message, 'SQLSTATE') || str_contains($message, 'SQL')) {
            return 'An error occurred while processing your request. Please try again or contact support if the problem persists.';
        }

        // Validation errors (already user-friendly)
        if (str_contains($message, 'required') || str_contains($message, 'invalid') || str_contains($message, 'must be')) {
            return $message;
        }

        // Return a generic friendly message for unknown errors
        return 'An unexpected error occurred. Please try again or contact support if the problem persists.';
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
