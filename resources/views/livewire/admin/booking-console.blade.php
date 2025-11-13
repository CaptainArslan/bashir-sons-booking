<div>
    @include('admin.bookings.console._styles')

    <div class="container-fluid p-4">
        <!-- Header Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">
                    <i class="fas fa-ticket-alt"></i>
                    Booking Console - Real-Time Seat Booking
                    @if ($isAdmin)
                        <span class="badge bg-info ms-2">Admin Mode</span>
                    @else
                        <span class="badge bg-warning ms-2">Employee Mode - Terminal:
                            {{ auth()->user()->terminal?->name ?? 'N/A' }}</span>
                    @endif
                </h5>
                @if ($lastBookingId)
                    <button type="button" class="btn btn-light btn-sm fw-bold"
                        onclick="printBothTickets({{ $lastBookingId }})" title="Print Both Customer and Host Tickets">
                        <i class="fas fa-print"></i> Print Last Ticket
                    </button>
                @endif
            </div>
            <div class="card-body bg-light">
                <div class="row g-3">
                    <!-- Date -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Travel Date</label>
                        <input type="date" class="form-control form-control-lg" wire:model.live="travelDate"
                            min="{{ $minDate }}" max="{{ $maxDate }}" />
                    </div>

                    <!-- From Terminal -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">From Terminal</label>
                        <select class="form-select form-select-lg" wire:model.live="fromTerminalId"
                            @if (!$isAdmin && auth()->user()->terminal_id) disabled @endif>
                            <option value="">Select Terminal</option>
                            @foreach ($terminals as $terminal)
                                <option value="{{ $terminal->id }}">
                                    {{ $terminal->name }} ({{ $terminal->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- To Terminal -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">To Terminal</label>
                        <select class="form-select form-select-lg" wire:model.live="toTerminalId"
                            wire:key="to-terminal-select-{{ count($toTerminals) }}"
                            @if (!$fromTerminalId) disabled @endif>
                            <option value="">Select Destination</option>
                            @foreach ($toTerminals as $terminal)
                                <option value="{{ $terminal['terminal_id'] }}">
                                    {{ $terminal['name'] }} ({{ $terminal['code'] }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Departure Time -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Departure Time</label>
                        <select class="form-select form-select-lg" wire:model.live="departureTimeId"
                            wire:key="departure-time-select-{{ $toTerminalId }}-{{ count($departureTimes) }}-{{ $travelDate }}"
                            @if (!$toTerminalId) disabled @endif>
                            <option value="">Select Departure Time</option>
                            @foreach ($departureTimes as $time)
                                <option value="{{ $time['id'] }}">
                                    {{ $time['departure_at'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Arrival Time -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Arrival Time</label>
                        <input type="text" class="form-control form-control-lg" 
                            value="{{ $arrivalTime ?? '' }}" 
                            placeholder="{{ $departureTimeId ? 'Calculating...' : 'Select departure time' }}"
                            disabled readonly>
                    </div>

                    <!-- Load Trip Button -->
                    <div class="col-md-1 d-flex align-items-end gap-2">
                        <button class="btn btn-primary btn-lg flex-grow-1 fw-bold" wire:click="loadTrip"
                            @if (!$departureTimeId) disabled @endif>
                            <i class="fas fa-play"></i> Load
                        </button>
                    </div>
                </div>

                <!-- Fare Error Display -->
                @if ($fareError)
                    <div class="mt-3">
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Fare Error:</strong> {{ $fareError }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                wire:click="$set('fareError', null)"></button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Trip Content (shown when trip loaded) -->
        @if ($showTripContent && $tripLoaded)
            <!-- Trip Details Card -->
            <div class="card mb-3 shadow-sm border">
                <div class="card-body p-3">
                    <div class="row g-3 align-items-center">
                        <div class="col-lg-3 col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <div class="text-primary" style="font-size: 1.25rem;">
                                    <i class="fas fa-route"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block" style="font-size: 0.7rem; font-weight: 500;">Route</small>
                                    <div class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $routeData['name'] ?? '-' }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <div class="text-primary" style="font-size: 1.25rem;">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block" style="font-size: 0.7rem; font-weight: 500;">Date</small>
                                    <div class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ \Carbon\Carbon::parse($travelDate)->format('d M Y') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="d-flex align-items-center gap-2">
                                <div class="text-primary" style="font-size: 1.25rem;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <small class="text-muted d-block" style="font-size: 0.7rem; font-weight: 500;">Time</small>
                                    <div class="fw-semibold text-dark" style="font-size: 0.9rem;">
                                        @if ($departureTimeId)
                                            {{ \Carbon\Carbon::parse(collect($departureTimes)->firstWhere('id', $departureTimeId)['departure_at'] ?? '')->format('H:i A') }}
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            @php
                                $isOrigin =
                                    $tripData?->originStop &&
                                    $fromStop &&
                                    $tripData->originStop->id === $fromStop['trip_stop_id'];
                            @endphp
                            @if ($tripData?->bus_id && $tripData?->bus)
                                {{-- Bus assigned --}}
                                <div class="d-flex align-items-center gap-2">
                                    <div class="text-primary" style="font-size: 1.25rem;">
                                        <i class="fas fa-bus"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block" style="font-size: 0.7rem; font-weight: 500;">Bus & Driver</small>
                                        <div class="fw-semibold text-dark" style="font-size: 0.9rem;">{{ $tripData->bus->name ?? 'N/A' }}</div>
                                        @if ($tripData->driver_name || $tripData->driver_phone)
                                            <small class="text-muted" style="font-size: 0.75rem;">
                                                @if ($tripData->driver_name)
                                                    <i class="fas fa-user-tie"></i> {{ $tripData->driver_name }}
                                                @endif
                                                @if ($tripData->driver_phone)
                                                    @if ($tripData->driver_name) | @endif
                                                    <i class="fas fa-phone"></i> {{ $tripData->driver_phone }}
                                                @endif
                                            </small>
                                        @endif
                                        <div class="mt-1 d-flex gap-1">
                                            @if ($isOrigin)
                                                <button type="button" class="btn btn-sm btn-outline-primary"
                                                    wire:click="openBusAssignmentModal" title="Edit Bus Assignment">
                                                    <i class="fas fa-edit"></i> Edit
                                                </button>
                                            @endif
                                            @if (!$isOrigin)
                                                <button type="button" class="btn btn-sm btn-outline-success"
                                                    wire:click="openExpenseModal" title="Manage Expenses">
                                                    <i class="fas fa-receipt"></i> Expenses
                                                </button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @elseif ($isOrigin)
                                {{-- No bus assigned - show assign button at origin --}}
                                <div class="d-flex align-items-center gap-2">
                                    <div class="text-muted" style="font-size: 1.25rem;">
                                        <i class="fas fa-bus"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block" style="font-size: 0.7rem; font-weight: 500;">Bus & Driver</small>
                                        <div class="text-muted mb-1" style="font-size: 0.85rem;">Not Assigned</div>
                                        <button type="button" class="btn btn-sm btn-primary"
                                            wire:click="openBusAssignmentModal">
                                            <i class="fas fa-bus"></i> Assign
                                        </button>
                                    </div>
                                </div>
                            @else
                                {{-- No bus assigned - not at origin --}}
                                <div class="d-flex align-items-center gap-2">
                                    <div class="text-muted" style="font-size: 1.25rem;">
                                        <i class="fas fa-bus"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <small class="text-muted d-block" style="font-size: 0.7rem; font-weight: 500;">Bus & Driver</small>
                                        <div class="text-muted mb-1" style="font-size: 0.85rem;">Not Assigned</div>
                                        <small class="text-muted d-block mb-1" style="font-size: 0.7rem;">Assign at origin terminal</small>
                                        <button type="button" class="btn btn-sm btn-outline-success"
                                            wire:click="openExpenseModal" title="Manage Expenses">
                                            <i class="fas fa-receipt"></i> Expenses
                                        </button>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3 booking-console-row">
                <!-- Left Column: Seat Map (3 columns) -->
                <div class="col-xxl-3 col-xl-6 col-lg-6 col-md-6">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header bg-white border-bottom">
                            <h6 class="mb-0 fw-semibold text-dark">
                                <i class="fas fa-chair"></i> Seat Map
                            </h6>
                        </div>
                        <div class="card-body p-2 scrollable-content">
                            <!-- Seat Grid -->
                            <div class="seat-map-container">
                                <h6 class="text-center mb-2"
                                    style="color: #334155; font-weight: 600; font-size: 0.875rem;">
                                    Select Your Seat</h6>
                                <div class="seat-grid">
                                    @php
                                        // Use seatCount from bus (if assigned) or calculate from seatMap
                                        $totalSeats =
                                            $seatCount ?? (count($seatMap) > 0 ? max(array_keys($seatMap)) : 44);
                                        // Calculate number of full rows (4 seats per row: 2 left + 2 right)
                                        $seatsPerRow = 4;
                                        $fullRows = floor(($totalSeats - 1) / $seatsPerRow);
                                        $lastRowSeats = $totalSeats - $fullRows * $seatsPerRow;
                                    @endphp
                                    @for ($row = 1; $row <= $fullRows; $row++)
                                        <div class="seat-row-container">
                                            <!-- Left Pair -->
                                            <div class="seat-pair-left">
                                                @for ($seat = ($row - 1) * 4 + 1; $seat <= ($row - 1) * 4 + 2; $seat++)
                                                    @if ($seat <= $totalSeats)
                                                        @php
                                                            $seatData = $seatMap[$seat] ?? [
                                                                'number' => $seat,
                                                                'status' => 'available',
                                                            ];
                                                            $isSelected = isset($selectedSeats[$seat]);
                                                            $seatGender = $isSelected
                                                                ? $selectedSeats[$seat]['gender'] ?? null
                                                                : $seatData['gender'] ?? null;
                                                            $status = $seatData['status'] ?? 'available';
                                                            $isLockedByOtherUser =
                                                                isset($lockedSeats[$seat]) &&
                                                                $lockedSeats[$seat] != auth()->id();

                                                            // Override status if locked by another user
                                                            if ($isLockedByOtherUser) {
                                                                $status = 'held';
                                                            }
                                                        @endphp
                                                        <button type="button"
                                                            wire:click="selectSeat({{ $seat }})"
                                                            class="seat-btn 
                                                                    @if ($status === 'booked') @if ($seatGender === 'male') seat-booked-male
                                                                        @elseif($seatGender === 'female') seat-booked-female
                                                                        @else seat-booked-male @endif
                                @elseif($status === 'held' || $isLockedByOtherUser)
                                seat-held
                                @elseif($isSelected)
                                seat-selected
                                @else
                                seat-available
                                                                    @endif"
                                                            @if ($status === 'booked' || $status === 'held' || $isLockedByOtherUser) disabled @endif>
                                                            {{ $seat }}
                                                            @if ($isSelected && $seatGender)
                                                                <span
                                                                    class="seat-gender-badge {{ $seatGender === 'male' ? 'male-badge' : 'female-badge' }}">
                                                                    {{ $seatGender === 'male' ? '👨' : '👩' }}
                                                                </span>
                                                            @endif
                                                            @if ($isLockedByOtherUser)
                                                                <span class="seat-locked-badge"
                                                                    title="Locked by another user">
                                                                    🔒
                                                                </span>
                                                            @endif
                                                        </button>
                                                    @endif
                                                @endfor
                                            </div>

                                            <!-- Aisle -->
                                            <div class="seat-aisle">{{ $row }}</div>

                                            <!-- Right Pair -->
                                            <div class="seat-pair-right">
                                                @for ($seat = ($row - 1) * 4 + 3; $seat <= ($row - 1) * 4 + 4; $seat++)
                                                    @if ($seat <= $totalSeats)
                                                        @php
                                                            $seatData = $seatMap[$seat] ?? [
                                                                'number' => $seat,
                                                                'status' => 'available',
                                                            ];
                                                            $isSelected = isset($selectedSeats[$seat]);
                                                            $seatGender = $isSelected
                                                                ? $selectedSeats[$seat]['gender'] ?? null
                                                                : $seatData['gender'] ?? null;
                                                            $status = $seatData['status'] ?? 'available';
                                                            $isLockedByOtherUser =
                                                                isset($lockedSeats[$seat]) &&
                                                                $lockedSeats[$seat] != auth()->id();

                                                            // Override status if locked by another user
                                                            if ($isLockedByOtherUser) {
                                                                $status = 'held';
                                                            }
                                                        @endphp
                                                        <button type="button"
                                                            wire:click="selectSeat({{ $seat }})"
                                                            class="seat-btn 
                                                                    @if ($status === 'booked') @if ($seatGender === 'male') seat-booked-male
                                                                        @elseif($seatGender === 'female') seat-booked-female
                                                                        @else seat-booked-male @endif
@elseif($status === 'held' || $isLockedByOtherUser)
seat-held
@elseif($isSelected)
seat-selected
@else
seat-available
                                                                    @endif"
                                                            @if ($status === 'booked' || $status === 'held' || $isLockedByOtherUser) disabled @endif>
                                                            {{ $seat }}
                                                            @if ($isSelected && $seatGender)
                                                                <span
                                                                    class="seat-gender-badge {{ $seatGender === 'male' ? 'male-badge' : 'female-badge' }}">
                                                                    {{ $seatGender === 'male' ? '👨' : '👩' }}
                                                                </span>
                                                            @endif
                                                            @if ($isLockedByOtherUser)
                                                                <span class="seat-locked-badge"
                                                                    title="Locked by another user">
                                                                    🔒
                                                                </span>
                                                            @endif
                                                        </button>
                                                    @endif
                                                @endfor
                                            </div>
                                        </div>
                                    @endfor

                                    {{-- Last row with remaining seats (if any) --}}
                                    @if ($lastRowSeats > 0)
                                        <div class="seat-row-container" style="gap: 0.5rem;">
                                            @for ($seat = $fullRows * 4 + 1; $seat <= $totalSeats; $seat++)
                                                @php
                                                    $seatData = $seatMap[$seat] ?? [
                                                        'number' => $seat,
                                                        'status' => 'available',
                                                    ];
                                                    $isSelected = isset($selectedSeats[$seat]);
                                                    $seatGender = $isSelected
                                                        ? $selectedSeats[$seat]['gender'] ?? null
                                                        : $seatData['gender'] ?? null;
                                                    $status = $seatData['status'] ?? 'available';
                                                    $isLockedByOtherUser =
                                                        isset($lockedSeats[$seat]) &&
                                                        $lockedSeats[$seat] != auth()->id();

                                                    // Override status if locked by another user
                                                    if ($isLockedByOtherUser) {
                                                        $status = 'held';
                                                    }
                                                @endphp
                                                <button type="button" wire:click="selectSeat({{ $seat }})"
                                                    class="seat-btn 
                                                            @if ($status === 'booked') @if ($seatGender === 'male') seat-booked-male
                                                                @elseif($seatGender === 'female') seat-booked-female
                                                                @else seat-booked-male @endif
@elseif($status === 'held' || $isLockedByOtherUser)
seat-held
@elseif($isSelected)
seat-selected
@else
seat-available
                                                            @endif"
                                                    @if ($status === 'booked' || $status === 'held' || $isLockedByOtherUser) disabled @endif>
                                                    {{ $seat }}
                                                    @if ($isSelected && $seatGender)
                                                        <span
                                                            class="seat-gender-badge {{ $seatGender === 'male' ? 'male-badge' : 'female-badge' }}">
                                                            {{ $seatGender === 'male' ? '👨' : '👩' }}
                                                        </span>
                                                    @endif
                                                    @if ($isLockedByOtherUser)
                                                        <span class="seat-locked-badge"
                                                            title="Locked by another user">
                                                            🔒
                                                        </span>
                                                    @endif
                                                </button>
                                            @endfor
                                        </div>
                                    @endif
                                </div>

                                <!-- Legend at bottom of seat map -->
                                <div class="seat-legend">
                                    <div class="seat-legend-item">
                                        <div class="seat-legend-indicator available"></div>
                                        <span>Available</span>
                                    </div>
                                    <div class="seat-legend-item">
                                        <div class="seat-legend-indicator selected"></div>
                                        <span>Selected</span>
                                    </div>
                                    <div class="seat-legend-item">
                                        <div class="seat-legend-indicator booked-male"></div>
                                        <span>Male Booked</span>
                                    </div>
                                    <div class="seat-legend-item">
                                        <div class="seat-legend-indicator booked-female"></div>
                                        <span>Female Booked</span>
                                    </div>
                                    <div class="seat-legend-item">
                                        <div class="seat-legend-indicator held"></div>
                                        <span>Held</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Middle Column: Booking Form (5 columns) -->
                <div class="col-xxl-5 col-xl-6 col-lg-6 col-md-6">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-info text-white">
                            <h6 class="mb-0">
                                <i class="fas fa-clipboard-list"></i> Booking Summary
                            </h6>
                        </div>
                        <div class="card-body scrollable-content" style="padding: 1rem;">
                            <!-- Selected Seats -->
                            <div class="mb-2">
                                <label class="form-label fw-bold small mb-1">
                                    <i class="fas fa-list"></i> Selected Seats
                                    <span class="badge bg-primary ms-2">({{ count($selectedSeats) }})</span>
                                </label>
                                <div class="d-flex flex-wrap gap-2 mb-0" style="min-height: 40px;">
                                    @forelse($selectedSeats as $seatNumber => $seatData)
                                        <span class="badge bg-primary">
                                            Seat {{ $seatNumber }}
                                            @if ($seatData['gender'])
                                                ({{ ucfirst($seatData['gender']) }})
                                            @endif
                                        </span>
                                    @empty
                                        <span class="text-muted small">No seats selected yet</span>
                                    @endforelse
                                </div>
                            </div>

                            <!-- Fare Calculation -->
                            <div class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                <h6 class="fw-bold mb-2 small"><i class="fas fa-calculator"></i> Fare Calculation</h6>

                                @php
                                    $seatCount = count($selectedSeats);
                                    $totalDiscount = $discountAmount * $seatCount;
                                    $fareAfterDiscount = $totalFare - $totalDiscount;
                                @endphp

                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Base Fare (Per Seat)</label>
                                        <input type="text" class="form-control form-control-sm"
                                            value="PKR {{ number_format($baseFare, 2) }}" readonly>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Discount (Per Seat)</label>
                                        <input type="text" class="form-control form-control-sm"
                                            value="{{ $discountAmount > 0 ? 'PKR ' . number_format($discountAmount, 2) : 'None' }}"
                                            readonly>
                                    </div>
                                </div>

                                @if ($seatCount > 0)
                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small mb-1">Total Fare ({{ $seatCount }}
                                                seat{{ $seatCount > 1 ? 's' : '' }})</label>
                                            <input type="text" class="form-control form-control-sm fw-bold"
                                                value="PKR {{ number_format($totalFare, 2) }}" readonly>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small mb-1">Total Discount</label>
                                            <input type="text"
                                                class="form-control form-control-sm text-danger fw-bold"
                                                value="{{ $totalDiscount > 0 ? '- PKR ' . number_format($totalDiscount, 2) : 'PKR 0.00' }}"
                                                readonly>
                                        </div>
                                    </div>

                                    <div class="row g-2 mb-2">
                                        <div class="col-6">
                                            <label class="form-label small mb-1">Fare After Discount</label>
                                            <input type="text"
                                                class="form-control form-control-sm fw-bold text-primary"
                                                value="PKR {{ number_format($fareAfterDiscount, 2) }}" readonly>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small mb-1">Tax/Charge
                                                <small class="text-muted">
                                                    @if ($paymentMethod === 'mobile_wallet')
                                                        (Suggested: PKR 40 per seat)
                                                    @else
                                                        (Optional)
                                                    @endif
                                                </small>
                                            </label>
                                            <input type="number" class="form-control form-control-sm"
                                                wire:model.blur="taxAmount" wire:loading.attr="disabled"
                                                placeholder="0.00" min="0" step="0.01">
                                            @error('taxAmount')
                                                <small class="text-danger">{{ $message }}</small>
                                            @enderror
                                            <div wire:loading wire:target="taxAmount"
                                                class="spinner-border spinner-border-sm text-primary mt-1"
                                                role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    </div>
                                @else
                                    <div class="alert alert-info mb-2 p-2 small text-center">
                                        <i class="fas fa-info-circle"></i> Select seats to calculate fare
                                    </div>
                                @endif

                                @if ($seatCount > 0)
                                    <div class="alert alert-success border-2 mb-0 p-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <span class="fw-bold">Final Amount:</span>
                                            <span class="fs-5 fw-bold text-success">PKR
                                                {{ number_format($finalAmount, 2) }}</span>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Booking Type & Payment -->
                            <div class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                <h6 class="fw-bold mb-2 small"><i class="fas fa-bookmark"></i> Type & Payment</h6>
                                <div class="row g-2">
                                    <div class="col-lg-6 col-md-12 mb-2">
                                        <label class="form-label small fw-bold">Booking Type</label>
                                        <select class="form-select form-select-sm" wire:model.live="bookingType"
                                            wire:loading.attr="disabled">
                                            <option value="counter"
                                                {{ $bookingType === 'counter' ? 'selected' : '' }}>🏪 Counter</option>
                                            <option value="phone" {{ $bookingType === 'phone' ? 'selected' : '' }}>📞
                                                Phone (Hold till before 60 mins of departure)
                                            </option>
                                        </select>
                                        <div wire:loading wire:target="bookingType"
                                            class="spinner-border spinner-border-sm text-primary mt-1" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    @if ($bookingType === 'counter')
                                        <div class="col-lg-6 col-md-12 mb-2">
                                            <label class="form-label small fw-bold">Payment Method</label>
                                            <select class="form-select form-select-sm" wire:model.live="paymentMethod"
                                                wire:loading.attr="disabled">
                                                @foreach ($paymentMethods as $method)
                                                    @if ($method['value'] !== 'other')
                                                        <option value="{{ $method['value'] }}">{{ $method['label'] }}
                                                        </option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <div wire:loading wire:target="paymentMethod"
                                                class="spinner-border spinner-border-sm text-primary mt-1"
                                                role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Payment Fields (Counter Only) -->
                            @if ($bookingType === 'counter')
                                <div class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                    <h6 class="fw-bold mb-2 small"><i class="fas fa-credit-card"></i> Payment Details
                                    </h6>

                                    @if ($paymentMethod !== 'cash')
                                        <div class="mb-2">
                                            <label class="form-label small">Transaction ID</label>
                                            <input type="text" class="form-control form-control-sm"
                                                wire:model="transactionId" placeholder="TXN123456789"
                                                maxlength="100">
                                        </div>
                                    @endif

                                    @if ($paymentMethod === 'cash')
                                        @php
                                            $amountReceivedValue = (float) ($amountReceived ?? 0);
                                            $finalAmountValue = (float) ($finalAmount ?? 0);
                                            $calculatedReturn = max(0, $amountReceivedValue - $finalAmountValue);
                                            $calculatedDue = max(0, $finalAmountValue - $amountReceivedValue);
                                        @endphp

                                        <!-- Final Amount Display -->
                                        @if ($finalAmountValue > 0)
                                            <div class="mb-3 p-2 bg-white rounded border border-primary">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="fw-bold small">
                                                        <i class="fas fa-calculator"></i> Final Amount:
                                                    </span>
                                                    <span class="fs-5 fw-bold text-primary">
                                                        PKR {{ number_format($finalAmountValue, 2) }}
                                                    </span>
                                                </div>
                                            </div>
                                        @endif

                                        <!-- Amount Received Input -->
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold">
                                                <i class="fas fa-money-bill"></i> Amount Received from Customer (PKR)
                                            </label>
                                            <input type="number" class="form-control form-control-lg fw-bold"
                                                wire:model.blur="amountReceived" wire:loading.attr="disabled"
                                                id="amountReceived" min="0" step="0.01" placeholder="0.00"
                                                style="font-size: 1.1rem;">
                                            <div wire:loading wire:target="amountReceived"
                                                class="spinner-border spinner-border-sm text-primary mt-1"
                                                role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                            <small class="text-muted d-block mt-1">
                                                <i class="fas fa-info-circle"></i> Press Tab or click outside to
                                                calculate return/due amount
                                            </small>
                                        </div>

                                        <!-- Payment Summary -->
                                        @if ($amountReceivedValue > 0 && $finalAmountValue > 0)
                                            <div class="payment-summary">
                                                <!-- Amount Remaining (Due) -->
                                                @if ($calculatedDue > 0)
                                                    <div class="alert alert-warning border-2 mb-2 p-3"
                                                        style="background-color: #fff3cd !important;">
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="fw-bold">
                                                                <i class="fas fa-exclamation-triangle"></i> Amount
                                                                Remaining (Due):
                                                            </span>
                                                            <span class="fs-3 fw-bold text-warning">
                                                                PKR {{ number_format($calculatedDue, 2) }}
                                                            </span>
                                                        </div>
                                                        <div class="progress mb-2" style="height: 8px;">
                                                            @php
                                                                $paymentPercentage = min(
                                                                    100,
                                                                    ($amountReceivedValue / $finalAmountValue) * 100,
                                                                );
                                                            @endphp
                                                            <div class="progress-bar bg-warning" role="progressbar"
                                                                style="width: {{ $paymentPercentage }}%"
                                                                aria-valuenow="{{ $paymentPercentage }}"
                                                                aria-valuemin="0" aria-valuemax="100">
                                                            </div>
                                                        </div>
                                                        <small class="text-muted d-block">
                                                            <i class="fas fa-info-circle"></i> Customer still needs to
                                                            pay PKR {{ number_format($calculatedDue, 2) }} more
                                                        </small>
                                                    </div>
                                                @endif

                                                <!-- Return Amount -->
                                                @if ($calculatedReturn > 0)
                                                    <div class="alert alert-success border-2 mb-2 p-3"
                                                        style="background-color: #d1e7dd !important;">
                                                        <div
                                                            class="d-flex justify-content-between align-items-center mb-2">
                                                            <span class="fw-bold">
                                                                <i class="fas fa-money-bill-wave"></i> Return to
                                                                Customer:
                                                            </span>
                                                            <span class="fs-3 fw-bold text-success">
                                                                PKR {{ number_format($calculatedReturn, 2) }}
                                                            </span>
                                                        </div>
                                                        <small class="text-muted d-block">
                                                            <i class="fas fa-info-circle"></i> Customer paid extra.
                                                            Return PKR {{ number_format($calculatedReturn, 2) }} to
                                                            customer
                                                        </small>
                                                    </div>
                                                @endif

                                                <!-- Exact Payment -->
                                                @if ($calculatedReturn == 0 && $calculatedDue == 0)
                                                    <div class="alert alert-info border-2 mb-2 p-3"
                                                        style="background-color: #cfe2ff !important;">
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <span class="fw-bold">
                                                                <i class="fas fa-check-circle"></i> Payment Complete:
                                                            </span>
                                                            <span class="fs-4 fw-bold text-info">
                                                                No Return Required
                                                            </span>
                                                        </div>
                                                        <small class="text-muted d-block mt-1">
                                                            <i class="fas fa-info-circle"></i> Customer paid exactly
                                                            PKR {{ number_format($finalAmountValue, 2) }}
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>
                                        @elseif($amountReceivedValue > 0 && $finalAmountValue == 0)
                                            <div class="alert alert-secondary border-1 mb-2 p-2 small">
                                                <i class="fas fa-info-circle"></i> Please select seats first to
                                                calculate the final amount
                                            </div>
                                        @else
                                            <div class="alert alert-secondary border-1 mb-2 p-2 small">
                                                <i class="fas fa-info-circle"></i> Enter amount received to see payment
                                                summary
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endif

                            <!-- Notes -->
                            <div class="mb-2">
                                <label class="form-label small fw-bold"><i class="fas fa-sticky-note"></i>
                                    Notes</label>
                                <textarea class="form-control form-control-sm" wire:model="notes" {{-- wire:loading.attr="disabled" --}} rows="2"
                                    maxlength="500" placeholder="Optional notes..."></textarea>
                                <div wire:loading wire:target="notes"
                                    class="spinner-border spinner-border-sm text-primary mt-1" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <!-- Passenger Information Section -->
                            <div class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0 small"><i class="fas fa-users"></i> Passengers</h6>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="badge bg-info">{{ count($selectedSeats) }} seat(s)</span>
                                        @if (count($selectedSeats) > 0 && count($passengers) < count($selectedSeats))
                                            <button type="button" class="btn btn-outline-primary btn-sm"
                                                wire:click="addPassenger"
                                                title="Add another passenger (max {{ count($selectedSeats) }})">
                                                <i class="fas fa-plus-circle"></i> Add Passenger
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-muted small mb-2" style="font-size: 0.75rem;">
                                    <strong>Required:</strong> At least 1 passenger information required.
                                    @if (count($selectedSeats) > 0)
                                        You can add up to {{ count($selectedSeats) }} passenger(s) for
                                        {{ count($selectedSeats) }} selected seat(s).
                                    @endif
                                </p>
                                <div style="max-height: 250px; overflow-y: auto;">
                                    @foreach ($passengers as $index => $passenger)
                                        <div class="card mb-3 border-2 {{ $passenger['is_required'] ? '' : 'border-warning' }}"
                                            style="border-color: {{ $passenger['is_required'] ? '#e9ecef' : '#ffc107' }};">
                                            <div class="card-header"
                                                style="background-color: {{ $passenger['is_required'] ? '#f8f9fa' : '#fff3cd' }};">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">
                                                        @if ($passenger['is_required'])
                                                            <i class="fas fa-user"></i> Passenger {{ $index + 1 }}
                                                            <span class="badge bg-danger ms-2">Required</span>
                                                        @else
                                                            <i class="fas fa-user-plus"></i> Passenger
                                                            {{ $index + 1 }}
                                                        @endif
                                                    </h6>
                                                    @if (!$passenger['is_required'])
                                                        <button type="button"
                                                            class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                                                            wire:click="removePassenger({{ $index }})"
                                                            title="Remove this passenger">
                                                            <i class="bx bx-trash" style="font-size: 1rem;"></i>
                                                            <span>Remove</span>
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-2">
                                                    <div class="col-lg-6 col-md-12">
                                                        <label class="form-label small">Name <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm"
                                                            wire:model="passengers.{{ $index }}.name"
                                                            placeholder="Full Name" maxlength="100">
                                                        @error("passengers.{$index}.name")
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                    <div class="col-lg-3 col-md-6">
                                                        <label class="form-label small">Age <span
                                                                class="text-danger">*</span></label>
                                                        <input type="number" class="form-control form-control-sm"
                                                            wire:model="passengers.{{ $index }}.age"
                                                            min="1" max="120" maxlength="3"
                                                            placeholder="Age">
                                                        @error("passengers.{$index}.age")
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                    <div class="col-lg-3 col-md-6">
                                                        <label class="form-label small">Gender <span
                                                                class="text-danger">*</span></label>
                                                        <select class="form-select form-select-sm"
                                                            wire:model="passengers.{{ $index }}.gender">
                                                            <option value="">Select</option>
                                                            <option value="male">Male</option>
                                                            <option value="female">Female</option>
                                                        </select>
                                                        @error("passengers.{$index}.gender")
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                    <div class="col-lg-4 col-md-12">
                                                        <label class="form-label small">CNIC <span
                                                                class="text-danger">*</span></label>
                                                        <input type="text" class="form-control form-control-sm"
                                                            wire:model.blur="passengers.{{ $index }}.cnic"
                                                            id="cnic-{{ $index }}"
                                                            placeholder="12345-1234567-1"
                                                            pattern="^[0-9]{5}-[0-9]{7}-[0-9]{1}$"
                                                            maxlength="15"
                                                            inputmode="numeric"
                                                            oninput="this.value = this.value.replace(/[^0-9-]/g, '').replace(/(\d{5})(\d)/, '$1-$2').replace(/(\d{5}-\d{7})(\d)/, '$1-$2')"
                                                            title="Please enter a valid CNIC in format: 12345-1234567-1 (5 digits, hyphen, 7 digits, hyphen, 1 digit)"
                                                            required>
                                                        @error("passengers.{$index}.cnic")
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                        <small class="text-muted">Format: 12345-1234567-1</small>
                                                    </div>
                                                    <div class="col-lg-4 col-md-6">
                                                        <label class="form-label small">Phone</label>
                                                        <input type="tel" class="form-control form-control-sm"
                                                            wire:model.blur="passengers.{{ $index }}.phone"
                                                            id="phone-{{ $index }}"
                                                            placeholder="03001234567"
                                                            pattern="^0[0-9]{10}$"
                                                            maxlength="11"
                                                            inputmode="numeric"
                                                            oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                                            title="Please enter a valid phone number starting with 0 followed by 10 digits (e.g., 03001234567)">
                                                        @error("passengers.{$index}.phone")
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                        <small class="text-muted">Format: 03001234567 (11
                                                            digits)</small>
                                                    </div>
                                                    <div class="col-lg-4 col-md-6">
                                                        <label class="form-label small">Email</label>
                                                        <input type="email" class="form-control form-control-sm"
                                                            wire:model="passengers.{{ $index }}.email"
                                                            placeholder="email@example.com" maxlength="100">
                                                        @error("passengers.{$index}.email")
                                                            <small class="text-danger">{{ $message }}</small>
                                                        @enderror
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Confirm Button -->
                            <button class="btn btn-success w-100 fw-bold py-2 small" wire:click="confirmBooking"
                                wire:loading.attr="disabled">
                                <span wire:loading.remove>
                                    <i class="fas fa-check-circle"></i> Confirm Booking
                                </span>
                                <span wire:loading>
                                    <i class="fas fa-spinner fa-spin"></i> Processing...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Trip Passengers List (4 columns) -->
                <div class="col-xxl-4 col-xl-12 col-lg-12 col-md-12">
                    <!-- Trip Passengers List Card -->
                    <div class="card shadow-sm h-100">
                        <div
                            class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 small">
                                <i class="bx bx-list-check"></i> Booked Passengers
                                <span class="badge bg-info ms-2">Total Passengers: {{ count($tripPassengers) }}</span>
                                <span class="badge bg-success ms-2">Total Earnings: PKR
                                    {{ number_format($totalEarnings, 2) }}</span>
                            </h6>
                            @if (count($tripPassengers) > 0)
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-dark"
                                        onclick="window.printPassengerList && window.printPassengerList()"
                                        title="Print Passenger List">
                                        <i class="bx bx-printer"></i> Print List
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="window.printVoucher && window.printVoucher()"
                                        title="Print Voucher (For Police Record)">
                                        <i class="bx bx-file"></i> Print Voucher
                                    </button>
                                </div>
                            @endif
                        </div>
                        <div class="card-body p-2 scrollable-content">
                            @if (count($tripPassengers) > 0)
                                <div class="table-responsive">
                                    <table id="passengerListTable" class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="small">Booking #</th>
                                                <th class="small">Seat</th>
                                                <th class="small">Name</th>
                                                <th class="small">CNIC</th>
                                                <th class="small">Phone</th>
                                                <th class="small">From</th>
                                                <th class="small">To</th>
                                                <th class="small">Channel</th>
                                                <th class="small text-end">Fare (PKR)</th>
                                                <th class="small text-center">Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($tripPassengers as $passenger)
                                                <tr>
                                                    <td class="small">
                                                        <span
                                                            class="badge bg-dark">{{ $passenger['booking_number'] }}</span>
                                                    </td>
                                                    <td class="small">
                                                        <span
                                                            class="badge bg-info">{{ $passenger['seat_number'] }}</span>
                                                    </td>
                                                    <td class="small">
                                                        <strong>{{ $passenger['name'] }}</strong>
                                                    </td>
                                                    <td class="small">
                                                        <small>{{ $passenger['cnic'] ?? 'N/A' }}</small>
                                                    </td>
                                                    <td class="small">
                                                        <small>{{ $passenger['phone'] ?? 'N/A' }}</small>
                                                    </td>
                                                    <td class="small">
                                                        <small>{{ $passenger['from_code'] }}</small>
                                                    </td>
                                                    <td class="small">
                                                        <small>{{ $passenger['to_code'] }}</small>
                                                    </td>
                                                    <td class="small">
                                                        @php
                                                            $channel = $passenger['channel'] ?? 'online';
                                                            $channelEnum = \App\Enums\ChannelEnum::tryFrom($channel) ?? \App\Enums\ChannelEnum::ONLINE;
                                                        @endphp
                                                        <span class="{{ $channelEnum->getBadge() }}" title="{{ $channelEnum->getLabel() }}">
                                                            <i class="{{ $channelEnum->getIcon() }}"></i> {{ $channelEnum->getLabel() }}
                                                        </span>
                                                    </td>
                                                    <td class="small text-end">
                                                        <strong>PKR
                                                            {{ number_format($passenger['final_amount'] ?? 0, 2) }}</strong>
                                                    </td>
                                                    <td class="small">
                                                        <div class="d-flex gap-1 justify-content-center">
                                                            <a href="{{ route('admin.bookings.edit', $passenger['booking_id']) }}"
                                                                class="btn btn-sm btn-outline-warning" target="_blank"
                                                                title="Edit Booking">
                                                                <i class="bx bx-edit"></i>
                                                            </a>
                                                            <button type="button" class="btn btn-sm btn-outline-info"
                                                                onclick="printBothTickets({{ $passenger['booking_id'] }})"
                                                                title="Print Both Customer and Host Tickets">
                                                                <i class="bx bx-printer"></i>
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="3" class="text-end fw-bold small">
                                                    <strong>Total Passengers:</strong> <span
                                                        class="badge bg-info">{{ count($tripPassengers) }}</span>
                                                </td>
                                                <td colspan="5" class="text-end fw-bold small">Total Earnings:</td>
                                                <td class="fw-bold text-success small text-end">PKR
                                                    {{ number_format($totalEarnings, 2) }}</td>
                                                <td></td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            @else
                                <div class="text-center py-4 text-muted">
                                    <i class="fas fa-info-circle"></i>
                                    <p class="mb-0 small">No passengers booked yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Gender Selection Modal -->
    <div class="modal fade" id="genderModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-user"></i> Select Gender - Seat <span id="modalSeatNumber"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-center mb-0">Please select passenger gender:</p>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-outline-primary btn-lg flex-grow-1 fw-bold"
                        onclick="window.setGender('male')" data-gender="male">
                        👨 Male
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-lg flex-grow-1 fw-bold"
                        onclick="window.setGender('female')" data-gender="female">
                        👩 Female
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bus Assignment Modal -->
    <div class="modal fade" id="busAssignmentModal" tabindex="-1" wire:ignore.self data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-bus"></i> Assign Bus & Driver
                    </h5>
                    <button type="button" class="btn-close btn-close-white"
                        wire:click="closeBusAssignmentModal"></button>
                </div>
                <div class="modal-body py-4">
                    @if ($showBusAssignmentModal)
                        <!-- General Error Display -->
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                                <strong><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Bus Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-bus"></i> Select Bus <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" wire:model="selectedBusId">
                                <option value="">-- Select Bus --</option>
                                @foreach ($availableBuses as $bus)
                                    <option value="{{ $bus->id }}">
                                        {{ $bus->name }} ({{ $bus->registration_number }}) -
                                        {{ $bus->model }}
                                    </option>
                                @endforeach
                            </select>
                            @error('selectedBusId')
                                <small class="text-danger">{{ $message }}</small>
                            @enderror
                        </div>

                        <!-- Driver Information -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-3"><i class="fas fa-user-tie"></i> Driver Information</h6>
                            <div class="row g-3">
                                <div class="col-lg-6 col-md-12">
                                    <label class="form-label small">Driver Name <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="driverName" placeholder="Enter driver name" maxlength="255">
                                    @error('driverName')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <label class="form-label small">Driver Phone <span
                                            class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm"
                                        wire:model="driverPhone" placeholder="03001234567" maxlength="20">
                                    @error('driverPhone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Driver Address</label>
                                    <textarea class="form-control form-control-sm" wire:model="driverAddress" rows="2"
                                        placeholder="Enter driver address" maxlength="500"></textarea>
                                    @error('driverAddress')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Host Information -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <h6 class="fw-bold mb-3"><i class="fas fa-user"></i> Host/Hostess Information</h6>
                            <div class="row g-3">
                                <div class="col-lg-6 col-md-12">
                                    <label class="form-label small">Host Name</label>
                                    <input type="text" class="form-control form-control-sm" wire:model="hostName"
                                        placeholder="Enter host name" maxlength="255">
                                    @error('hostName')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                                <div class="col-lg-6 col-md-12">
                                    <label class="form-label small">Host Phone</label>
                                    <input type="text" class="form-control form-control-sm" wire:model="hostPhone"
                                        placeholder="03001234567" maxlength="20">
                                    @error('hostPhone')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Expenses Section -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0">
                                    <i class="fas fa-receipt"></i> Expenses (From
                                    {{ $fromStop['terminal_name'] ?? 'Current' }} to Next Stop)
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    wire:click="addExpense">
                                    <i class="fas fa-plus"></i> Add Expense
                                </button>
                            </div>
                            @foreach ($expenses as $index => $expense)
                                <div class="card mb-3 border-2">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <h6 class="mb-0 small fw-bold">Expense {{ $index + 1 }}</h6>
                                                @if (isset($expense['from_terminal_name']) && isset($expense['to_terminal_name']))
                                                    <small class="text-muted">
                                                        <i class="fas fa-route"></i> 
                                                        From <span class="fw-semibold">{{ $expense['from_terminal_name'] }}</span> 
                                                        to <span class="fw-semibold">{{ $expense['to_terminal_name'] }}</span>
                                                    </small>
                                                @endif
                                            </div>
                                            @if (count($expenses) > 1)
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    wire:click="removeExpense({{ $index }})">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-lg-4 col-md-12">
                                                <label class="form-label small">Expense Type</label>
                                                <select class="form-select form-select-sm"
                                                    wire:model="expenses.{{ $index }}.expense_type">
                                                    <option value="">-- Select Type --</option>
                                                    @foreach ($expenseTypes as $type)
                                                        <option value="{{ $type['value'] }}">{{ $type['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error("expenses.{$index}.expense_type")
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <div class="col-lg-4 col-md-6">
                                                <label class="form-label small">Amount (PKR)</label>
                                                <input type="number" class="form-control form-control-sm"
                                                    wire:model="expenses.{{ $index }}.amount"
                                                    placeholder="0.00" min="0" step="0.01">
                                                @error("expenses.{$index}.amount")
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <div class="col-lg-4 col-md-6">
                                                <label class="form-label small">Description</label>
                                                <input type="text" class="form-control form-control-sm"
                                                    wire:model="expenses.{{ $index }}.description"
                                                    placeholder="Optional description" maxlength="500">
                                                @error("expenses.{$index}.description")
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Expenses will be recorded from
                                {{ $fromStop['terminal_name'] ?? 'current terminal' }} to the next stop.
                            </small>
                        </div>
                    @endif
                </div>
                <div class="modal-footer d-flex gap-2">
                    <button type="button" class="btn btn-secondary" wire:click="closeBusAssignmentModal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary fw-bold" wire:click="assignBus"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="fas fa-check"></i> Assign Bus
                        </span>
                        <span wire:loading>
                            <i class="fas fa-spinner fa-spin"></i> Processing...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Expense Management Modal -->
    <div class="modal fade" id="expenseModal" tabindex="-1" wire:ignore.self data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-receipt"></i> Manage Expenses
                    </h5>
                    <button type="button" class="btn-close btn-close-white"
                        wire:click="closeExpenseModal"></button>
                </div>
                <div class="modal-body py-4">
                    @if ($showExpenseModal)
                        <!-- General Error Display -->
                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                                <strong><i class="fas fa-exclamation-triangle"></i> Please fix the following errors:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <!-- Expenses Section -->
                        <div class="mb-4 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="fw-bold mb-0">
                                    <i class="fas fa-receipt"></i> Expenses (From
                                    {{ $fromStop['terminal_name'] ?? 'Current' }} to Next Stop)
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-primary"
                                    wire:click="addExpense">
                                    <i class="fas fa-plus"></i> Add Expense
                                </button>
                            </div>
                            @foreach ($expenses as $index => $expense)
                                <div class="card mb-3 border-2">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <h6 class="mb-0 small fw-bold">Expense {{ $index + 1 }}</h6>
                                                @if (isset($expense['from_terminal_name']) && isset($expense['to_terminal_name']))
                                                    <small class="text-muted">
                                                        <i class="fas fa-route"></i> 
                                                        From <span class="fw-semibold">{{ $expense['from_terminal_name'] }}</span> 
                                                        to <span class="fw-semibold">{{ $expense['to_terminal_name'] }}</span>
                                                    </small>
                                                @endif
                                            </div>
                                            @if (count($expenses) > 1)
                                                <button type="button" class="btn btn-sm btn-outline-danger"
                                                    wire:click="removeExpense({{ $index }})">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-lg-4 col-md-12">
                                                <label class="form-label small">Expense Type</label>
                                                <select class="form-select form-select-sm"
                                                    wire:model="expenses.{{ $index }}.expense_type">
                                                    <option value="">-- Select Type --</option>
                                                    @foreach ($expenseTypes as $type)
                                                        <option value="{{ $type['value'] }}">{{ $type['label'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error("expenses.{$index}.expense_type")
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <div class="col-lg-4 col-md-6">
                                                <label class="form-label small">Amount (PKR)</label>
                                                <input type="number" class="form-control form-control-sm"
                                                    wire:model="expenses.{{ $index }}.amount"
                                                    placeholder="0.00" min="0" step="0.01">
                                                @error("expenses.{$index}.amount")
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                            <div class="col-lg-4 col-md-6">
                                                <label class="form-label small">Description</label>
                                                <input type="text" class="form-control form-control-sm"
                                                    wire:model="expenses.{{ $index }}.description"
                                                    placeholder="Optional description" maxlength="500">
                                                @error("expenses.{$index}.description")
                                                    <small class="text-danger">{{ $message }}</small>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Expenses will be recorded from
                                {{ $fromStop['terminal_name'] ?? 'current terminal' }} to the next stop.
                            </small>
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeExpenseModal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-success fw-bold" wire:click="saveExpenses"
                        wire:loading.attr="disabled">
                        <span wire:loading.remove>
                            <i class="fas fa-save"></i> Save Expenses
                        </span>
                        <span wire:loading>
                            <span class="spinner-border spinner-border-sm me-1" role="status"></span>
                            Saving...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1" wire:ignore.self>
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-check-circle"></i> Booking Confirmed Successfully!
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    @if ($lastBookingData)
                        <div class="mb-4 p-3 bg-light rounded text-center">
                            <h6 class="text-muted mb-2">Booking Number</h6>
                            <h3 class="fw-bold text-primary">#{{ $lastBookingData['booking_number'] }}</h3>
                        </div>

                        <div class="mb-4">
                            <p class="mb-2"><strong>Seats:</strong> <span
                                    class="badge bg-info ms-2">{{ $lastBookingData['seats'] }}</span></p>
                            <p class="mb-0"><strong>Status:</strong> <span
                                    class="badge bg-success ms-2">{{ ucfirst($lastBookingData['status']) }}</span>
                            </p>
                        </div>

                        <div class="alert alert-light border-2 mb-4">
                            <h6 class="fw-bold mb-3">Fare Breakdown</h6>
                            <p class="mb-2"><strong>Total Fare:</strong> <span class="float-end">PKR
                                    {{ number_format($lastBookingData['total_fare'], 2) }}</span></p>
                            @if ($lastBookingData['discount_amount'] > 0)
                                <p class="mb-2"><strong>Discount:</strong> <span class="float-end text-danger">-PKR
                                        {{ number_format($lastBookingData['discount_amount'], 2) }}</span></p>
                            @endif
                            @if ($lastBookingData['tax_amount'] > 0)
                                <p class="mb-2"><strong>Tax/Charge:</strong> <span class="float-end">+PKR
                                        {{ number_format($lastBookingData['tax_amount'], 2) }}</span></p>
                            @endif
                            <hr>
                            <p class="mb-0"><strong>Final Amount:</strong> <span
                                    class="float-end fw-bold text-success">PKR
                                    {{ number_format($lastBookingData['final_amount'], 2) }}</span></p>
                        </div>

                        <p><strong>Payment Method:</strong> <span
                                class="badge bg-warning ms-2">{{ ucfirst(str_replace('_', ' ', $lastBookingData['payment_method'])) }}</span>
                        </p>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted">No booking data available</p>
                        </div>
                    @endif
                </div>
                <div class="modal-footer d-flex gap-2">
                    <button type="button" class="btn btn-primary btn-lg fw-bold flex-fill"
                        @if ($lastBookingId) onclick="printBothTickets({{ $lastBookingId }})" @else disabled @endif>
                        <i class="fas fa-print"></i> Print Both Tickets (Customer & Host)
                    </button>
                    <button type="button" class="btn btn-success btn-lg fw-bold flex-fill" data-bs-dismiss="modal">
                        <i class="fas fa-check"></i> Done
                    </button>
                </div>
            </div>
        </div>
    </div>

    @script
        <script>
            // Removed input masking and error highlighting functions as they were not working properly

            // Re-initialize Select2 after every Livewire update
            document.addEventListener('livewire:update', () => {
                if (typeof $ !== 'undefined' && $.fn.select2) {
                    // Destroy existing Select2 instances safely
                    $('.select2').each(function() {
                        if ($(this).hasClass('select2-hidden-accessible')) {
                            $(this).select2('destroy');
                        }
                    });

                    // Re-initialize Select2
                    setTimeout(() => {
                        $('.select2').select2({
                            width: 'resolve'
                        });
                    }, 100);
                }
            });

            let pendingSeatNumber = null;
            let genderModalInstance = null;

            // Handle Livewire events
            $wire.on('show-gender-modal', (event) => {
                pendingSeatNumber = event.seatNumber;
                document.getElementById('modalSeatNumber').textContent = event.seatNumber;

                // Get or create modal instance
                const modalElement = document.getElementById('genderModal');
                genderModalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                genderModalInstance.show();
            });

            function closeGenderModal() {
                if (genderModalInstance) {
                    genderModalInstance.hide();
                    genderModalInstance = null;
                } else {
                    // Fallback: try to get instance
                    const modalElement = document.getElementById('genderModal');
                    if (modalElement) {
                        const instance = bootstrap.Modal.getInstance(modalElement);
                        if (instance) {
                            instance.hide();
                        } else {
                            // Last resort: use jQuery/bootstrap data attribute
                            if (typeof $ !== 'undefined') {
                                $('#genderModal').modal('hide');
                            } else {
                                // Use Bootstrap 5 native way
                                const bsModal = new bootstrap.Modal(modalElement);
                                bsModal.hide();
                            }
                        }
                    }
                }
            }

            // Make setGender available globally
            window.setGender = function(gender) {
                if (pendingSeatNumber) {
                    // Call Livewire method
                    $wire.call('setSeatGender', pendingSeatNumber, gender)
                        .then(() => {
                            // Close modal immediately
                            closeGenderModal();
                            pendingSeatNumber = null;
                        })
                        .catch((error) => {
                            console.error('Error setting gender:', error);
                            // Still try to close modal even if there's an error
                            closeGenderModal();
                        });
                }
            };

            // Also add event listeners as backup (works with Livewire dynamic content)
            document.addEventListener('livewire:init', () => {
                // Use event delegation to handle dynamically added buttons
                document.addEventListener('click', function(e) {
                    const button = e.target.closest('[data-gender]');
                    if (button && button.hasAttribute('data-gender')) {
                        e.preventDefault();
                        const gender = button.getAttribute('data-gender');
                        if (window.setGender && typeof window.setGender === 'function') {
                            window.setGender(gender);
                        }
                    }
                });
            });

            // Listen for gender selected event
            $wire.on('gender-selected', () => {
                closeGenderModal();
            });

            let lastBookingId = null;

            // Listen for form reset event
            $wire.on('form-reset', () => {
                // Form reset handled by Livewire
                // reset the amountReceived and returnAmount to 0
                document.getElementById('amountReceived').value = 0;
            });

            $wire.on('booking-success', () => {
                // Show modal after a small delay to ensure DOM is ready
                setTimeout(() => {
                    const successModalElement = document.getElementById('successModal');
                    if (successModalElement) {
                        // Hide any existing modal instance first
                        const existingModal = bootstrap.Modal.getInstance(successModalElement);
                        if (existingModal) {
                            existingModal.hide();
                        }

                        // Create new modal instance and show
                        const successModal = new bootstrap.Modal(successModalElement);
                        successModal.show();

                        // Listen for modal close event to ensure form is ready for next booking
                        successModalElement.addEventListener('hidden.bs.modal', function onModalHidden() {
                            // Scroll to passenger section to make it visible
                            setTimeout(() => {
                                const passengerSection = document.querySelector(
                                    '.card-body .mb-2.p-2.bg-light.rounded');
                                if (passengerSection) {
                                    passengerSection.scrollIntoView({
                                        behavior: 'smooth',
                                        block: 'nearest'
                                    });
                                }

                                // Focus on first passenger name field if available
                                const firstPassengerName = document.querySelector(
                                    'input[wire\\:model*="passengers.0.name"]');
                                if (firstPassengerName) {
                                    setTimeout(() => {
                                        firstPassengerName.focus();
                                    }, 300);
                                }
                            }, 200);
                            // Remove listener after first use to avoid multiple bindings
                            successModalElement.removeEventListener('hidden.bs.modal', onModalHidden);
                        });
                    } else {
                        console.error('successModal element not found');
                    }
                }, 100);
            });

            function printBooking(bookingId, ticketType = null) {
                if (!bookingId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Booking',
                        text: 'Booking ID is missing.',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }

                // Default behavior: automatically print both customer and host tickets
                // If no ticket type is specified, print both tickets
                if (!ticketType || ticketType === 'both') {
                    printBothTickets(bookingId);
                    return;
                }

                // If ticket type is specified (customer or host), print single ticket
                if (ticketType) {
                    try {
                        const printWindow = window.open(`/admin/bookings/${bookingId}/print/${ticketType}/80mm`,
                            '_blank');

                        if (!printWindow) {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Popup Blocked',
                                text: 'Please allow popups for this site to print the booking ticket.',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    } catch (error) {
                        console.error('Error opening print window:', error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Print Error',
                            text: 'Failed to open print window. Please try again.',
                            confirmButtonColor: '#d33'
                        });
                    }
                }
            }

            // Function to print both customer and host tickets automatically
            function printBothTickets(bookingId) {
                if (!bookingId) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Invalid Booking',
                            text: 'Booking ID is missing.',
                            confirmButtonColor: '#d33'
                        });
                    } else {
                        alert('Booking ID is missing.');
                    }
                    return;
                }

                try {
                    // Open a single window with both tickets combined (always 80mm)
                    const bothTicketsUrl = `/admin/bookings/${bookingId}/print/both/80mm`;
                    const printWindow = window.open(bothTicketsUrl, 'bothTickets');

                    // Check if window was blocked
                    if (!printWindow) {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Popup Blocked',
                                text: 'Please allow popups for this site to print tickets. Click the browser\'s popup blocker icon and allow popups.',
                                confirmButtonColor: '#3085d6'
                            });
                        } else {
                            alert('Popup blocked. Please allow popups for this site.');
                        }
                        return;
                    }
                } catch (error) {
                    console.error('Error opening print window:', error);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Print Error',
                            text: 'Failed to open print window. Please try again.',
                            confirmButtonColor: '#d33'
                        });
                    } else {
                        alert('Failed to open print window. Please try again.');
                    }
                }
            }

            // Make printBooking and printBothTickets available globally
            window.printBooking = printBooking;
            window.printBothTickets = printBothTickets;

            // Define printVoucher function for police records
            window.printVoucher = function() {
            // Get current data from Livewire component dynamically
            const tripPassengers = $wire.get('tripPassengers') || [];
            const tripData = $wire.get('tripDataForJs') || null;
            const travelDate = $wire.get('travelDate') || '';
            const routeData = $wire.get('routeData') || null;
            const fromStop = $wire.get('fromStop') || null;
            const toStop = $wire.get('toStop') || null;
            const tripStops = tripData?.stops || [];

            // Get departure and arrival times
            let departureTime = 'N/A';
            let arrivalTime = 'N/A';

            if (fromStop?.departure_at) {
                const depDate = new Date(fromStop.departure_at);
                departureTime = depDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            } else if (tripData?.departure_datetime) {
                const depDate = new Date(tripData.departure_datetime);
                departureTime = depDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }

            if (toStop?.arrival_at) {
                const arrDate = new Date(toStop.arrival_at);
                arrivalTime = arrDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            } else if (tripData?.estimated_arrival_datetime) {
                const arrDate = new Date(tripData.estimated_arrival_datetime);
                arrivalTime = arrDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }

            // Extract host information from trip notes
            let hostInfo = null;
            if (tripData?.notes) {
                const hostMatch = tripData.notes.match(/Host:\s*([^(]+)(?:\s*\(([^)]+)\))?/i);
                if (hostMatch) {
                    hostInfo = {
                        name: hostMatch[1]?.trim() || 'N/A',
                        phone: hostMatch[2]?.trim() || null
                    };
                }
            }

            if (!tripPassengers || tripPassengers.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No passengers found to print voucher.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Create voucher content (NO fare information for police record)
            const voucherContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Passenger Voucher - Police Record</title>
                    <style>
                        @media print {
                            @page {
                                margin: 1cm;
                                size: A4;
                            }
                        }
                        body {
                            font-family: Arial, sans-serif;
                            font-size: 11px;
                            margin: 0;
                            padding: 20px;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 20px;
                            border-bottom: 3px solid #000;
                            padding-bottom: 10px;
                        }
                        .header h1 {
                            margin: 0;
                            font-size: 18px;
                            font-weight: bold;
                            text-transform: uppercase;
                        }
                        .header h2 {
                            margin: 5px 0;
                            font-size: 14px;
                            color: #666;
                        }
                        .trip-info {
                            margin-bottom: 15px;
                            padding: 10px;
                            background-color: #f5f5f5;
                            border: 1px solid #ddd;
                        }
                        .trip-info table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        .trip-info td {
                            padding: 5px;
                            border: none;
                        }
                        .trip-info td:first-child {
                            font-weight: bold;
                            width: 150px;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 10px;
                        }
                        th, td {
                            border: 1px solid #000;
                            padding: 6px;
                            text-align: left;
                        }
                        th {
                            background-color: #333;
                            color: #fff;
                            font-weight: bold;
                            text-align: center;
                        }
                        .text-center {
                            text-align: center;
                        }
                        .print-date {
                            margin-top: 20px;
                            font-size: 10px;
                            text-align: right;
                            color: #666;
                        }
                        .footer-note {
                            margin-top: 15px;
                            padding: 10px;
                            background-color: #fff3cd;
                            border: 1px solid #ffc107;
                            font-size: 10px;
                            text-align: center;
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>PASSENGER VOUCHER</h1>
                        <h2>For Police Record - ${routeData?.name || 'N/A'}</h2>
                    </div>
                    
                    <div class="trip-info">
                        <table>
                            <tr>
                                <td>Travel Date:</td>
                                <td><strong>${travelDate || 'N/A'}</strong></td>
                                <td>Route:</td>
                                <td><strong>${routeData?.name || 'N/A'}</strong></td>
                            </tr>
                            <tr>
                                <td>Departure Time:</td>
                                <td><strong>${departureTime}</strong></td>
                                <td>Arrival Time:</td>
                                <td><strong>${arrivalTime}</strong></td>
                            </tr>
                            ${fromStop && toStop ? `
                                                                                    <tr>
                                                                                        <td>From Terminal:</td>
                                                                                        <td><strong>${fromStop.terminal_name || 'N/A'} (${fromStop.terminal_code || 'N/A'})</strong></td>
                                                                                        <td>To Terminal:</td>
                                                                                        <td><strong>${toStop.terminal_name || 'N/A'} (${toStop.terminal_code || 'N/A'})</strong></td>
                                                                                    </tr>
                                                                                    ` : ''}
                            <tr>
                                <td>Total Passengers:</td>
                                <td><strong>${tripPassengers.length}</strong></td>
                                <td>Printed On:</td>
                                <td>${new Date().toLocaleString()}</td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="trip-info" style="margin-top: 15px;">
                        <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Bus Information</h3>
                        <table>
                            <tr>
                                <td>Bus Name:</td>
                                <td><strong>${tripData?.bus ? (tripData.bus.name || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                                <td>Registration Number:</td>
                                <td><strong>${tripData?.bus ? (tripData.bus.registration_number || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                            </tr>
                            <tr>
                                <td>Bus Model:</td>
                                <td><strong>${tripData?.bus ? (tripData.bus.model || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                                <td>Total Seats:</td>
                                <td><strong>${tripData?.bus?.bus_layout ? (tripData.bus.bus_layout.total_seats || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="trip-info" style="margin-top: 15px;">
                        <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Driver Information</h3>
                        <table>
                            <tr>
                                <td>Driver Name:</td>
                                <td><strong>${tripData?.driver_name || 'N/A (Not Assigned)'}</strong></td>
                                <td>Driver Phone:</td>
                                <td><strong>${tripData?.driver_phone || 'N/A (Not Assigned)'}</strong></td>
                            </tr>
                            ${tripData?.driver_address ? `
                                                                                    <tr>
                                                                                        <td>Driver Address:</td>
                                                                                        <td colspan="3"><strong>${tripData.driver_address}</strong></td>
                                                                                    </tr>
                                                                                    ` : ''}
                            ${routeData ? `
                                                                                    <tr>
                                                                                        <td>Route:</td>
                                                                                        <td colspan="3"><strong>${routeData.name || 'N/A'}</strong></td>
                                                                                    </tr>
                                                                                    ` : ''}
                        </table>
                    </div>
                    
                    <div class="trip-info" style="margin-top: 15px;">
                        <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Host/Hostess Information</h3>
                        <table>
                            <tr>
                                <td>Host Name:</td>
                                <td><strong>${hostInfo ? (hostInfo.name !== 'N/A' ? hostInfo.name : 'N/A (Not Assigned)') : 'N/A (Not Assigned)'}</strong></td>
                                <td>Host Phone:</td>
                                <td><strong>${hostInfo ? (hostInfo.phone || 'N/A (Not Assigned)') : 'N/A (Not Assigned)'}</strong></td>
                            </tr>
                        </table>
                    </div>
                    
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 5%;">#</th>
                                <th style="width: 12%;">Booking #</th>
                                <th style="width: 8%;">Seat</th>
                                <th style="width: 18%;">Passenger Name</th>
                                <th style="width: 18%;">CNIC</th>
                                <th style="width: 15%;">Phone</th>
                                <th style="width: 12%;">From</th>
                                <th style="width: 12%;">To</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${tripPassengers.map((passenger, index) => `
                                                                                        <tr>
                                                                                            <td class="text-center">${index + 1}</td>
                                                                                            <td class="text-center"><strong>${passenger.booking_number || 'N/A'}</strong></td>
                                                                                            <td class="text-center"><strong>${passenger.seat_number || 'N/A'}</strong></td>
                                                                                            <td><strong>${passenger.name || 'N/A'}</strong></td>
                                                                                            <td>${passenger.cnic || 'N/A'}</td>
                                                                                            <td>${passenger.phone || 'N/A'}</td>
                                                                                            <td>${passenger.from_code || 'N/A'}</td>
                                                                                            <td>${passenger.to_code || 'N/A'}</td>
                                                                                        </tr>
                                                                                    `).join('')}
                        </tbody>
                    </table>
                    
                    <div class="footer-note">
                        <strong>NOTE:</strong> This voucher is for police record purposes only. No financial information is included.
                    </div>
                    
                    <div class="print-date">
                        Printed on: ${new Date().toLocaleString()}
                    </div>
                </body>
                </html>
            `;

            // Open print window
            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Popup Blocked',
                    text: 'Please allow popups for this site to print the voucher.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            printWindow.document.write(voucherContent);
            printWindow.document.close();

            // Wait for content to load, then print
            printWindow.onload = function() {
                setTimeout(function() {
                    printWindow.print();
                }, 250);
            };
            };

            // Define printPassengerList directly on window object
            window.printPassengerList = function() {
                const table = document.getElementById('passengerListTable');
                if (!table) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Passenger list table not found.',
                        confirmButtonColor: '#d33'
                    });
                    return;
                }

                // Get trip information dynamically from Livewire component
                const tripData = $wire.get('tripDataForJs') || null;
                const travelDate = $wire.get('travelDate') || '';
                const routeData = $wire.get('routeData') || null;
                const fromStop = $wire.get('fromStop') || null;
                const toStop = $wire.get('toStop') || null;
                const tripPassengers = $wire.get('tripPassengers') || [];
                const tripStops = tripData?.stops || [];
                const totalPassengers = tripPassengers.length;
                const totalEarnings = $wire.get('totalEarnings') || 0;

                console.log(tripData, 'tripData');
                console.log(travelDate, 'travelDate');
                console.log(routeData, 'routeData');
                console.log(fromStop, 'fromStop');
                console.log(toStop, 'toStop');
                console.log(tripPassengers, 'tripPassengers');
                console.log(tripStops, 'tripStops');
                console.log(totalPassengers, 'totalPassengers');
                console.log(totalEarnings, 'totalEarnings');

                // Get fare information
                const fareData = $wire.get('fareData');
                const baseFare = $wire.get('baseFare') || 0;
                const discountAmount = $wire.get('discountAmount') || 0;
                const taxAmount = $wire.get('taxAmount') || 0;

                // Extract host information from trip notes
                let hostInfo = null;
                if (tripData?.notes) {
                    const hostMatch = tripData.notes.match(/Host:\s*([^(]+)(?:\s*\(([^)]+)\))?/i);
                    if (hostMatch) {
                        hostInfo = {
                            name: hostMatch[1]?.trim() || 'N/A',
                            phone: hostMatch[2]?.trim() || null
                        };
                    }
                }

                // Get departure and arrival times
                let departureTime = 'N/A';
                let arrivalTime = 'N/A';

                if (fromStop?.departure_at) {
                    const depDate = new Date(fromStop.departure_at);
                    departureTime = depDate.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                } else if (tripData?.departure_datetime) {
                    const depDate = new Date(tripData.departure_datetime);
                    departureTime = depDate.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                }

                if (toStop?.arrival_at) {
                    const arrDate = new Date(toStop.arrival_at);
                    arrivalTime = arrDate.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                } else if (tripData?.estimated_arrival_datetime) {
                    const arrDate = new Date(tripData.estimated_arrival_datetime);
                    arrivalTime = arrDate.toLocaleTimeString('en-US', {
                        hour: '2-digit',
                        minute: '2-digit',
                        hour12: true
                    });
                }

                // Removed complete stop-to-stop information section as per user request

                // Create print window content
                const printContent = `
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Passenger List - Trip Report</title>
                    <style>
                        @media print {
                            @page {
                                margin: 1cm;
                                size: A4 landscape;
                            }
                        }
                        body {
                            font-family: Arial, sans-serif;
                            font-size: 12px;
                            margin: 0;
                            padding: 20px;
                        }
                        .header {
                            text-align: center;
                            margin-bottom: 20px;
                            border-bottom: 2px solid #000;
                            padding-bottom: 10px;
                        }
                        .header h1 {
                            margin: 0;
                            font-size: 20px;
                            font-weight: bold;
                        }
                        .header h2 {
                            margin: 5px 0;
                            font-size: 16px;
                        }
                        .trip-info {
                            margin-bottom: 15px;
                            padding: 10px;
                            background-color: #f5f5f5;
                            border: 1px solid #ddd;
                        }
                        .trip-info table {
                            width: 100%;
                            border-collapse: collapse;
                        }
                        .trip-info td {
                            padding: 5px;
                            border: none;
                        }
                        .trip-info td:first-child {
                            font-weight: bold;
                            width: 150px;
                        }
                        table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-top: 10px;
                        }
                        th, td {
                            border: 1px solid #ddd;
                            padding: 8px;
                            text-align: left;
                        }
                        th {
                            background-color: #f8f9fa;
                            font-weight: bold;
                            text-align: center;
                        }
                        tfoot {
                            font-weight: bold;
                            background-color: #f8f9fa;
                        }
                        .badge {
                            padding: 3px 6px;
                            border-radius: 3px;
                            font-size: 10px;
                        }
                        .text-center {
                            text-align: center;
                        }
                        .text-end {
                            text-align: right;
                        }
                        .text-success {
                            color: #28a745;
                        }
                        .print-date {
                            margin-top: 20px;
                            font-size: 10px;
                            text-align: right;
                            color: #666;
                        }
                    </style>
                </head>
                <body>
                    <div class="header">
                        <h1>PASSENGER LIST REPORT</h1>
                        <h2>Trip Passenger Details</h2>
                    </div>
                    
                    <div class="trip-info" style="margin-top: 0;">
                        <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Bus Information</h3>
                        <table>
                            <tr>
                                <td>Bus Name:</td>
                                <td><strong>${tripData?.bus ? (tripData.bus.name || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                                <td>Registration Number:</td>
                                <td><strong>${tripData?.bus ? (tripData.bus.registration_number || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                            </tr>
                            <tr>
                                <td>Bus Model:</td>
                                <td><strong>${tripData?.bus ? (tripData.bus.model || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                                <td>Total Seats:</td>
                                <td><strong>${tripData?.bus?.bus_layout ? (tripData.bus.bus_layout.total_seats || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="trip-info" style="margin-top: 15px;">
                        <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Driver Information</h3>
                        <table>
                            <tr>
                                <td>Driver Name:</td>
                                <td><strong>${tripData?.driver_name || 'N/A (Not Assigned)'}</strong></td>
                                <td>Driver Phone:</td>
                                <td><strong>${tripData?.driver_phone || 'N/A (Not Assigned)'}</strong></td>
                            </tr>
                            ${tripData?.driver_address ? `
                                                                                    <tr>
                                                                                        <td>Driver Address:</td>
                                                                                        <td colspan="3"><strong>${tripData.driver_address}</strong></td>
                                                                                    </tr>
                                                                                    ` : ''}
                            ${routeData ? `
                                                                                    <tr>
                                                                                        <td>Route:</td>
                                                                                        <td colspan="3"><strong>${routeData.name || 'N/A'}</strong></td>
                                                                                    </tr>
                                                                                    ` : ''}
                        </table>
                    </div>
                    
                    <div class="trip-info" style="margin-top: 15px;">
                        <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Host/Hostess Information</h3>
                        <table>
                            <tr>
                                <td>Host Name:</td>
                                <td><strong>${hostInfo ? (hostInfo.name !== 'N/A' ? hostInfo.name : 'N/A (Not Assigned)') : 'N/A (Not Assigned)'}</strong></td>
                                <td>Host Phone:</td>
                                <td><strong>${hostInfo ? (hostInfo.phone || 'N/A (Not Assigned)') : 'N/A (Not Assigned)'}</strong></td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="trip-info" style="margin-top: 15px;">
                        <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Fare Information</h3>
                        <table>
                            <tr>
                                <td>Base Fare (Per Seat):</td>
                                <td><strong>PKR ${parseFloat(baseFare || 0).toFixed(2)}</strong></td>
                                <td>Discount (Per Seat):</td>
                                <td><strong>PKR ${parseFloat(discountAmount || 0).toFixed(2)}</strong></td>
                            </tr>
                            <tr>
                                <td>Tax/Charges:</td>
                                <td><strong>PKR ${parseFloat(taxAmount || 0).toFixed(2)}</strong></td>
                                <td>Total Earnings:</td>
                                <td class="text-success"><strong>PKR ${parseFloat(totalEarnings || 0).toFixed(2)}</strong></td>
                            </tr>
                            ${fareData && fareData.from_terminal ? `
                                                                                    <tr>
                                                                                        <td>Fare Route:</td>
                                                                                        <td colspan="3"><strong>${fareData.from_terminal.name || 'N/A'} (${fareData.from_terminal.code || 'N/A'}) → ${fareData.to_terminal?.name || 'N/A'} (${fareData.to_terminal?.code || 'N/A'})</strong></td>
                                                                                    </tr>
                                                                                    ` : ''}
                        </table>
                    </div>
                    
                    <h3 style="font-size: 14px; margin-bottom: 10px;">Passenger Details</h3>
                    ${(() => {
                        // Clone the table and remove the Actions column (last column)
                        const clonedTable = table.cloneNode(true);
                        const rows = clonedTable.querySelectorAll('tr');
                        rows.forEach(row => {
                            const cells = row.querySelectorAll('th, td');
                            if (cells.length > 0) {
                                // Remove the last cell (Actions column)
                                cells[cells.length - 1].remove();
                            }
                        });
                        // Update footer colspan if exists
                        const footerRow = clonedTable.querySelector('tfoot tr');
                        if (footerRow) {
                            const footerCells = footerRow.querySelectorAll('td');
                            if (footerCells.length > 0) {
                                // Update the last footer cell colspan
                                const lastCell = footerCells[footerCells.length - 1];
                                if (lastCell.hasAttribute('colspan')) {
                                    lastCell.setAttribute('colspan', '2');
                                }
                            }
                        }
                        return clonedTable.outerHTML;
                    })()}
                    
                    <div class="print-date">
                        Printed on: ${new Date().toLocaleString()}
                    </div>
                </body>
                </html>
            `;

                // Open print window
                const printWindow = window.open('', '_blank');
                if (!printWindow) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Popup Blocked',
                        text: 'Please allow popups for this site to print the passenger list.',
                        confirmButtonColor: '#3085d6'
                    });
                    return;
                }

                printWindow.document.write(printContent);
                printWindow.document.close();

                // Wait for content to load, then print
                printWindow.onload = function() {
                    setTimeout(function() {
                        printWindow.print();
                    }, 250);
                };
            };

            // Global loader variable
            let currentLoader = null;

            // Function to show SweetAlert loader
            function showLoader(title = 'Loading...', text = 'Please wait while we process your request.') {
                // Close existing loader if any
                if (currentLoader) {
                    Swal.close();
                }

                currentLoader = Swal.fire({
                    title: title,
                    text: text,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            }

            // Function to hide loader
            function hideLoader() {
                if (currentLoader) {
                    Swal.close();
                    currentLoader = null;
                }
            }

            // Listen for loader events
            $wire.on('show-loader', (event) => {
                const title = event.title || 'Loading...';
                const text = event.text || 'Please wait while we process your request.';
                showLoader(title, text);
            });

            $wire.on('hide-loader', () => {
                hideLoader();
            });

            // Use Livewire hooks to detect when actions start and end
            document.addEventListener('livewire:init', () => {
                let isLoading = false;

                // Show loader when component starts a request
                Livewire.hook('request', ({
                    component,
                    uri,
                    payload,
                    options,
                    respond
                }) => {
                    // Check which method is being called
                    const method = payload?.fingerprint?.method || '';

                    // Only show loader if not already showing
                    if (!isLoading && !currentLoader) {
                        if (method === 'loadTrip') {
                            isLoading = true;
                            showLoader('Loading Trip', 'Fetching trip data and seat availability...');
                        } else if (method === 'confirmBooking') {
                            isLoading = true;
                            showLoader('Processing Booking', 'Creating your booking, please wait...');
                        } else if (method === 'assignBus') {
                            isLoading = true;
                            showLoader('Assigning Bus', 'Assigning bus and driver information...');
                        } else if (method === 'loadTripPassengers') {
                            // Don't show loader for this as it's usually quick
                        } else if (method && (method.startsWith('load') || method.startsWith('update'))) {
                            // Show generic loader for other load/update methods
                            isLoading = true;
                            showLoader('Loading', 'Please wait...');
                        }
                    }
                });

                // Hide loader when request completes
                Livewire.hook('message.processed', ({
                    component,
                    message,
                    respond
                }) => {
                    isLoading = false;
                    // Small delay to ensure UI updates are complete
                    setTimeout(() => {
                        hideLoader();
                    }, 300);
                });

                // Also hide loader on errors
                Livewire.hook('message.failed', ({
                    component,
                    message,
                    respond
                }) => {
                    isLoading = false;
                    hideLoader();
                });
            });


            $wire.on('show-error', (event) => {
                hideLoader(); // Hide loader on error

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: event.message,
                    confirmButtonColor: '#d33'
                });
            });

            $wire.on('show-success', (event) => {
                hideLoader(); // Hide loader on success
                Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: event.message,
                    confirmButtonColor: '#28a745'
                });
            });

            // Handle bus assignment modal
            let busAssignmentModalInstance = null;

            // Function to show modal
            function showBusAssignmentModal() {
                const modalElement = document.getElementById('busAssignmentModal');
                if (modalElement) {
                    busAssignmentModalInstance = bootstrap.Modal.getInstance(modalElement) || new bootstrap.Modal(modalElement);
                    busAssignmentModalInstance.show();
                }
            }

            // Function to hide modal
            function hideBusAssignmentModal() {
                const modalElement = document.getElementById('busAssignmentModal');
                if (modalElement) {
                    if (busAssignmentModalInstance) {
                        busAssignmentModalInstance.hide();
                    } else {
                        const instance = bootstrap.Modal.getInstance(modalElement);
                        if (instance) {
                            instance.hide();
                        }
                    }
                }
            }

            // Listen for Livewire events
            $wire.on('open-bus-assignment-modal', () => {
                console.log('Opening bus assignment modal');
                setTimeout(() => showBusAssignmentModal(), 200);
            });

            $wire.on('close-bus-assignment-modal', () => {
                console.log('Closing bus assignment modal');
                hideBusAssignmentModal();
            });

            // Expense Modal Functions
            function showExpenseModal() {
                const modalElement = document.getElementById('expenseModal');
                if (modalElement) {
                    const modal = new bootstrap.Modal(modalElement);
                    modal.show();
                }
            }

            function hideExpenseModal() {
                const modalElement = document.getElementById('expenseModal');
                if (modalElement) {
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    if (modal) {
                        modal.hide();
                    }
                }
            }

            $wire.on('open-expense-modal', () => {
                console.log('Opening expense modal');
                setTimeout(() => showExpenseModal(), 200);
            });

            $wire.on('close-expense-modal', () => {
                console.log('Closing expense modal');
                hideExpenseModal();
            });

            // Listen for seat map update after bus assignment
            $wire.on('seat-map-updated', () => {
                console.log('Seat map updated after bus assignment');
                // Livewire will automatically re-render the seat map
                // This event is just for confirmation/logging
            });

            // Also watch for property changes as fallback
            $wire.watch('showBusAssignmentModal', (value) => {
                console.log('showBusAssignmentModal changed to:', value);
                if (value) {
                    setTimeout(() => showBusAssignmentModal(), 200);
                } else {
                    hideBusAssignmentModal();
                }
            });

            // Initialize modal on page load if already open
            document.addEventListener('livewire:init', () => {
                setTimeout(() => {
                    if ($wire.get('showBusAssignmentModal')) {
                        showBusAssignmentModal();
                    }
                }, 500);
            });

            // WebSocket integration for real-time updates
            $wire.on('trip-loaded', (...args) => {
                // Extract tripId from event - Livewire v3 passes data as array
                let tripId = null;

                if (args.length > 0) {
                    // Check if first arg is an array with data
                    if (Array.isArray(args[0]) && args[0].length > 0) {
                        tripId = args[0][0]?.tripId || args[0][0];
                    } else if (typeof args[0] === 'object' && args[0] !== null) {
                        tripId = args[0].tripId || args[0].detail?.tripId;
                    } else if (typeof args[0] === 'number') {
                        tripId = args[0];
                    }
                }

                // Fallback: get from component property
                if (!tripId) {
                    tripId = $wire.get('tripId');
                }

                console.log('Trip loaded event received. Setting up WebSocket for trip:', tripId, 'Args:', args);

                setupEchoChannel(tripId);
            });

            // Function to setup Echo channel subscription
            function setupEchoChannel(tripId) {
                if (!tripId || !window.Echo) {
                    console.error('Cannot setup WebSocket - tripId:', tripId, 'Echo available:', !!window.Echo);
                    if (!window.Echo) {
                        console.error(
                            'Laravel Echo is not loaded. Make sure vite is included in the layout and assets are built. resources/js/app.js'
                        );
                    }
                    return;
                }

                // Leave previous channel if exists
                if (window.currentEchoChannel) {
                    console.log('Leaving previous channel:', window.currentEchoChannel);
                    Echo.leave(window.currentEchoChannel);
                    window.currentEchoChannel = null;
                }

                // Join new channel
                window.currentEchoChannel = 'trip.' + tripId;
                console.log('Joining channel:', window.currentEchoChannel);

                try {
                    const channel = Echo.channel(window.currentEchoChannel);

                    // Listen for seat locked events - note: backend broadcasts as 'seat-locked'
                    channel.listen('.seat-locked', (e) => {
                        console.log('Seat locked event received:', e);
                        $wire.call('handleSeatLocked', e.trip_id, e.seat_numbers, e.user_id);
                    });

                    // Listen for seat unlocked events - note: backend broadcasts as 'seat-unlocked'
                    channel.listen('.seat-unlocked', (e) => {
                        console.log('Seat unlocked event received:', e);
                        $wire.call('handleSeatUnlocked', e.trip_id, e.seat_numbers, e.user_id);
                    });

                    // Listen for seat confirmed events - note: backend broadcasts as 'seat-confirmed'
                    channel.listen('.seat-confirmed', (e) => {
                        console.log('Seat confirmed event received:', e);
                        $wire.call('handleSeatConfirmed', e.trip_id, e.seat_numbers, e.user_id);
                    });

                    console.log('WebSocket listeners registered successfully for channel:', window.currentEchoChannel);
                } catch (error) {
                    console.error('Error setting up WebSocket listeners:', error);
                }
            }

            // Also check if trip is already loaded when component initializes
            document.addEventListener('livewire:init', () => {
                // Wait a bit for component to be ready
                setTimeout(() => {
                    const tripId = $wire.get('tripId');
                    if (tripId && window.Echo && !window.currentEchoChannel) {
                        console.log('Component initialized with existing trip. Setting up WebSocket for trip:',
                            tripId);
                        setupEchoChannel(tripId);
                    }
                }, 500);
            });

            // Cleanup on component destroy
            $wire.on('destroy', () => {
                if (window.currentEchoChannel) {
                    Echo.leave(window.currentEchoChannel);
                    window.currentEchoChannel = null;
                }
                if (genderModalInstance) {
                    genderModalInstance.hide();
                    genderModalInstance = null;
                }
            });
        </script>
    @endscript
</div>
