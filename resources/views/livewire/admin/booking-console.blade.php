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
                    <button type="button" 
                            class="btn btn-light btn-sm fw-bold" 
                            onclick="printBooking({{ $lastBookingId }})"
                            title="Reprint Last Ticket">
                        <i class="fas fa-print"></i> Print Last Ticket
                    </button>
                @endif
            </div>
            <div class="card-body bg-light">
                <div class="row g-3">
                    <!-- Date -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Travel Date</label>
                        <input type="date" 
                               class="form-control form-control-lg" 
                               wire:model.live="travelDate"
                               min="{{ $minDate }}" 
                               max="{{ $maxDate }}" />
                    </div>

                    <!-- From Terminal -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">From Terminal</label>
                        <select class="form-select form-select-lg select2" 
                                wire:model.live="fromTerminalId"
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
                        <select class="form-select form-select-lg select2" 
                                wire:model.live="toTerminalId"
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
                        <select class="form-select form-select-lg select2" 
                                wire:model.live="departureTimeId"
                                @if (!$toTerminalId) disabled @endif>
                            <option value="">Select Departure Time</option>
                            @foreach ($departureTimes as $time)
                                <option value="{{ $time['id'] }}">
                                    {{ \Carbon\Carbon::parse($time['departure_at'])->format('H:i A') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Arrival Time -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Arrival Time</label>
                        <input type="text" 
                               class="form-control form-control-lg" 
                               value="{{ $arrivalTime }}" 
                               disabled readonly>
                    </div>

                    <!-- Load Trip Button -->
                    <div class="col-md-1 d-flex align-items-end gap-2">
                        <button class="btn btn-primary btn-lg flex-grow-1 fw-bold" 
                                wire:click="loadTrip"
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
                            <button type="button" class="btn-close" data-bs-dismiss="alert" wire:click="$set('fareError', null)"></button>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Trip Content (shown when trip loaded) -->
        @if ($showTripContent && $tripLoaded)
            <!-- Trip Details Card -->
            <div class="card mb-4 shadow-sm border-0"
                style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white p-4">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="font-size: 2.5rem;">
                                    <i class="fas fa-route"></i>
                                </div>
                                <div>
                                    <small class="d-block opacity-75"
                                        style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Route</small>
                                    <h5 class="mb-0 fw-bold">{{ $routeData['name'] ?? '-' }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="font-size: 2.5rem;">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <small class="d-block opacity-75"
                                        style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Date</small>
                                    <h5 class="mb-0 fw-bold">{{ \Carbon\Carbon::parse($travelDate)->format('d M Y') }}</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="font-size: 2.5rem;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <small class="d-block opacity-75"
                                        style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Time</small>
                                    <h5 class="mb-0 fw-bold">
                                        @if ($departureTimeId)
                                            {{ \Carbon\Carbon::parse(collect($departureTimes)->firstWhere('id', $departureTimeId)['departure_at'] ?? '')->format('H:i A') }}
                                        @else
                                            -
                                        @endif
                                    </h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            @php
                                $isOrigin = $tripData?->originStop && $fromStop && $tripData->originStop->id === $fromStop['trip_stop_id'];
                            @endphp
                            @if ($tripData?->bus_id && $tripData?->bus)
                                {{-- Bus assigned --}}
                                <div class="d-flex align-items-center">
                                    <div class="me-3" style="font-size: 2.5rem;">
                                        <i class="fas fa-bus"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <small class="d-block opacity-75"
                                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Bus & Driver</small>
                                        <h6 class="mb-1 fw-bold">{{ $tripData->bus->name ?? 'N/A' }}</h6>
                                        <small class="opacity-75" style="font-size: 0.7rem;">
                                            @if ($tripData->driver_name)
                                                <i class="fas fa-user-tie"></i> {{ $tripData->driver_name }}
                                            @endif
                                            @if ($tripData->driver_phone)
                                                | <i class="fas fa-phone"></i> {{ $tripData->driver_phone }}
                                            @endif
                                        </small>
                                        @if ($isOrigin)
                                            <div class="mt-2">
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-light fw-bold" 
                                                        wire:click="openBusAssignmentModal"
                                                        title="Edit Bus Assignment">
                                                    <i class="fas fa-edit"></i> Edit Assignment
                                                </button>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @elseif ($isOrigin)
                                {{-- No bus assigned - show assign button at origin --}}
                                <div class="d-flex align-items-center">
                                    <div class="me-3" style="font-size: 2.5rem; opacity: 0.7;">
                                        <i class="fas fa-bus"></i>
                                    </div>
                                    <div style="flex: 1;">
                                        <small class="d-block opacity-75"
                                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Bus & Driver</small>
                                        <p class="mb-2 fw-bold">Not Assigned</p>
                                        <button type="button" 
                                                class="btn btn-sm btn-light fw-bold w-100" 
                                                wire:click="openBusAssignmentModal">
                                            <i class="fas fa-bus"></i> Assign Bus & Driver
                                        </button>
                                    </div>
                                </div>
                            @else
                                {{-- No bus assigned - not at origin --}}
                                <div class="d-flex align-items-center">
                                    <div class="me-3" style="font-size: 2.5rem; opacity: 0.7;">
                                        <i class="fas fa-bus"></i>
                                    </div>
                                    <div>
                                        <small class="d-block opacity-75"
                                            style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Bus & Driver</small>
                                        <p class="mb-0 fw-bold">Not Assigned</p>
                                        <small class="opacity-75" style="font-size: 0.7rem;">Assign at origin terminal</small>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <!-- Left Column: Seat Map (3 columns) -->
                <div class="col-lg-3 col-md-6">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header text-white"
                            style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-chair"></i> Seat Map
                            </h6>
                        </div>
                        <div class="card-body p-3 scrollable-content">
                            <!-- Legend -->
                            <div class="mb-3 p-3 bg-white rounded-lg shadow-sm w-100 border border-gray-200">
                                <div class="seat-legend">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2"
                                            style="width: 16px; height: 16px; background: #E2E8F0; border: 1px solid #cbd5e1; border-radius: 4px;">
                                        </div>
                                        <span class="small text-dark">Available</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2"
                                            style="width: 16px; height: 16px; background: #3B82F6; border: 1px solid #2563eb; border-radius: 4px; position: relative;">
                                            <span
                                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.5rem;">👨</span>
                                        </div>
                                        <span class="small text-dark">Selected (Male)</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2"
                                            style="width: 16px; height: 16px; background: #3B82F6; border: 1px solid #2563eb; border-radius: 4px; position: relative;">
                                            <span
                                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.5rem;">👩</span>
                                        </div>
                                        <span class="small text-dark">Selected (Female)</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2"
                                            style="width: 16px; height: 16px; background: #22D3EE; border: 1px solid #06b6d4; border-radius: 4px; position: relative;">
                                            <span
                                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.5rem;">👨</span>
                                        </div>
                                        <span class="small text-dark">Male Booked</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2"
                                            style="width: 16px; height: 16px; background: #EC4899; border: 1px solid #db2777; border-radius: 4px; position: relative;">
                                            <span
                                                style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.5rem;">👩</span>
                                        </div>
                                        <span class="small text-dark">Female Booked</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2"
                                            style="width: 16px; height: 16px; background: #fbbf24; border: 1px solid #f59e0b; border-radius: 4px;">
                                        </div>
                                        <span class="small text-dark">Held</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Seat Grid -->
                            <div class="seat-map-container">
                                <h6 class="text-center mb-3" style="color: #334155; font-weight: 600; font-size: 1rem;">
                                    Select Your Seat</h6>
                                <div class="seat-grid">
                                    @php
                                        $totalSeats = count($seatMap) > 0 ? max(array_keys($seatMap)) : 44;
                                    @endphp
                                    @for ($row = 1; $row <= 11; $row++)
                                        <div class="seat-row-container">
                                            <!-- Left Pair -->
                                            <div class="seat-pair-left">
                                                @for ($seat = ($row - 1) * 4 + 1; $seat <= ($row - 1) * 4 + 2; $seat++)
                                                    @if ($seat <= $totalSeats)
                                                        @php
                                                            $seatData = $seatMap[$seat] ?? ['number' => $seat, 'status' => 'available'];
                                                            $isSelected = isset($selectedSeats[$seat]);
                                                            $seatGender = $isSelected ? ($selectedSeats[$seat]['gender'] ?? null) : ($seatData['gender'] ?? null);
                                                            $status = $seatData['status'] ?? 'available';
                                                            $isLockedByOtherUser = isset($lockedSeats[$seat]) && $lockedSeats[$seat] != auth()->id();
                                                            
                                                            // Override status if locked by another user
                                                            if ($isLockedByOtherUser) {
                                                                $status = 'held';
                                                            }
                                                        @endphp
                                                        <button type="button"
                                                                wire:click="selectSeat({{ $seat }})"
                                                                class="seat-btn 
                                                                    @if($status === 'booked')
                                                                        @if($seatGender === 'male') seat-booked-male
                                                                        @elseif($seatGender === 'female') seat-booked-female
                                                                        @else seat-booked-male
                                                                        @endif
                                                                    @elseif($status === 'held' || $isLockedByOtherUser)
                                                                        seat-held
                                                                    @elseif($isSelected) seat-selected
                                                                    @else seat-available
                                                                    @endif"
                                                                @if($status === 'booked' || $status === 'held' || $isLockedByOtherUser) disabled @endif>
                                                            {{ $seat }}
                                                            @if($isSelected && $seatGender)
                                                                <span class="seat-gender-badge {{ $seatGender === 'male' ? 'male-badge' : 'female-badge' }}">
                                                                    {{ $seatGender === 'male' ? '👨' : '👩' }}
                                                                </span>
                                                            @endif
                                                            @if($isLockedByOtherUser)
                                                                <span class="seat-locked-badge" title="Locked by another user">
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
                                                            $seatData = $seatMap[$seat] ?? ['number' => $seat, 'status' => 'available'];
                                                            $isSelected = isset($selectedSeats[$seat]);
                                                            $seatGender = $isSelected ? ($selectedSeats[$seat]['gender'] ?? null) : ($seatData['gender'] ?? null);
                                                            $status = $seatData['status'] ?? 'available';
                                                            $isLockedByOtherUser = isset($lockedSeats[$seat]) && $lockedSeats[$seat] != auth()->id();
                                                            
                                                            // Override status if locked by another user
                                                            if ($isLockedByOtherUser) {
                                                                $status = 'held';
                                                            }
                                                        @endphp
                                                        <button type="button"
                                                                wire:click="selectSeat({{ $seat }})"
                                                                class="seat-btn 
                                                                    @if($status === 'booked')
                                                                        @if($seatGender === 'male') seat-booked-male
                                                                        @elseif($seatGender === 'female') seat-booked-female
                                                                        @else seat-booked-male
                                                                        @endif
                                                                    @elseif($status === 'held' || $isLockedByOtherUser)
                                                                        seat-held
                                                                    @elseif($isSelected) seat-selected
                                                                    @else seat-available
                                                                    @endif"
                                                                @if($status === 'booked' || $status === 'held' || $isLockedByOtherUser) disabled @endif>
                                                            {{ $seat }}
                                                            @if($isSelected && $seatGender)
                                                                <span class="seat-gender-badge {{ $seatGender === 'male' ? 'male-badge' : 'female-badge' }}">
                                                                    {{ $seatGender === 'male' ? '👨' : '👩' }}
                                                                </span>
                                                            @endif
                                                            @if($isLockedByOtherUser)
                                                                <span class="seat-locked-badge" title="Locked by another user">
                                                                    🔒
                                                                </span>
                                                            @endif
                                                        </button>
                                                    @endif
                                                @endfor
                                            </div>
                                        </div>
                                    @endfor
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Middle Column: Booking Form (5 columns) -->
                <div class="col-lg-5 col-md-6">
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
                                            @if($seatData['gender'])
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
                                <h6 class="fw-bold mb-2 small"><i class="fas fa-calculator"></i> Fare</h6>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Base Fare</label>
                                        <input type="number" 
                                               class="form-control form-control-sm" 
                                               value="{{ number_format($baseFare, 2) }}" 
                                               readonly>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Discount</label>
                                        <input type="text" 
                                               class="form-control form-control-sm" 
                                               value="{{ $discountAmount > 0 ? 'PKR ' . number_format($discountAmount, 2) : 'None' }}" 
                                               readonly>
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Total Fare (Before Tax)</label>
                                        <input type="number" 
                                               class="form-control form-control-sm fw-bold" 
                                               value="{{ number_format($totalFare, 2) }}" 
                                               readonly>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Tax/Charge
                                            <small class="text-muted">
                                                @if($paymentMethod === 'mobile_wallet')
                                                    (Suggested: PKR 40 per seat)
                                                @else
                                                    (Optional)
                                                @endif
                                            </small>
                                        </label>
                                        <input type="number" 
                                               class="form-control form-control-sm" 
                                               wire:model.live="taxAmount"
                                               wire:loading.attr="disabled"
                                               placeholder="0.00" 
                                               min="0" 
                                               step="0.01">
                                        @error('taxAmount') 
                                            <small class="text-danger">{{ $message }}</small> 
                                        @enderror
                                        <div wire:loading wire:target="taxAmount" class="spinner-border spinner-border-sm text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="alert alert-primary border-1 mb-0 p-2 small text-center">
                                    <strong class="d-block mb-0">Final: PKR <span class="text-success">{{ number_format($finalAmount, 2) }}</span></strong>
                                </div>
                            </div>

                            <!-- Booking Type & Payment -->
                            <div class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                <h6 class="fw-bold mb-2 small"><i class="fas fa-bookmark"></i> Type & Payment</h6>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small fw-bold">Booking Type</label>
                                        <select class="form-select form-select-sm" 
                                                wire:model.live="bookingType"
                                                wire:loading.attr="disabled">
                                            <option value="counter">🏪 Counter</option>
                                            <option value="phone">📞 Phone (Hold till before 60 mins of departure)</option>
                                        </select>
                                        <div wire:loading wire:target="bookingType" class="spinner-border spinner-border-sm text-primary mt-1" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </div>
                                    @if($bookingType === 'counter')
                                        <div class="col-md-6 mb-2">
                                            <label class="form-label small fw-bold">Payment Method</label>
                                            <select class="form-select form-select-sm" 
                                                    wire:model.live="paymentMethod"
                                                    wire:loading.attr="disabled">
                                                @foreach ($paymentMethods as $method)
                                                    @if($method['value'] !== 'other')
                                                        <option value="{{ $method['value'] }}">{{ $method['label'] }}</option>
                                                    @endif
                                                @endforeach
                                            </select>
                                            <div wire:loading wire:target="paymentMethod" class="spinner-border spinner-border-sm text-primary mt-1" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Payment Fields (Counter Only) -->
                            @if($bookingType === 'counter')
                                <div class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                    <h6 class="fw-bold mb-2 small"><i class="fas fa-credit-card"></i> Payment Details</h6>

                                    @if($paymentMethod !== 'cash')
                                        <div class="mb-2">
                                            <label class="form-label small">Transaction ID</label>
                                            <input type="text" 
                                                   class="form-control form-control-sm" 
                                                   wire:model="transactionId"
                                                   placeholder="TXN123456789" 
                                                   maxlength="100">
                                        </div>
                                    @endif

                                    @if($paymentMethod === 'cash')
                                        <div class="mb-2">
                                            <label class="form-label small">Amount Received (PKR)</label>
                                            <input type="number" 
                                                   class="form-control form-control-sm" 
                                                   wire:model.live="amountReceived"
                                                   wire:loading.attr="disabled"
                                                   min="0" 
                                                   step="0.01" 
                                                   placeholder="0.00">
                                            <div wire:loading wire:target="amountReceived" class="spinner-border spinner-border-sm text-primary mt-1" role="status">
                                                <span class="visually-hidden">Loading...</span>
                                            </div>
                                        </div>
                                        @if($returnAmount > 0)
                                            <div class="alert alert-success border-1 mb-0 p-2 small">
                                                <strong>💰 Return: PKR {{ number_format($returnAmount, 2) }}</strong>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            @endif

                            <!-- Notes -->
                            <div class="mb-2">
                                <label class="form-label small fw-bold"><i class="fas fa-sticky-note"></i> Notes</label>
                                <textarea class="form-control form-control-sm" 
                                          wire:model.live="notes" 
                                          wire:loading.attr="disabled"
                                          rows="2" 
                                          maxlength="500"
                                          placeholder="Optional notes..."></textarea>
                                <div wire:loading wire:target="notes" class="spinner-border spinner-border-sm text-primary mt-1" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>

                            <!-- Passenger Information Section -->
                            <div class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0 small"><i class="fas fa-users"></i> Passengers</h6>
                                    <div class="d-flex gap-2 align-items-center">
                                        <span class="badge bg-info">{{ count($selectedSeats) }} seat(s)</span>
                                        @if(count($selectedSeats) > 0 && count($passengers) < count($selectedSeats))
                                            <button type="button" 
                                                    class="btn btn-outline-primary btn-sm" 
                                                    wire:click="addPassenger"
                                                    title="Add another passenger (max {{ count($selectedSeats) }})">
                                                <i class="fas fa-plus-circle"></i> Add Passenger
                                            </button>
                                        @endif
                                    </div>
                                </div>
                                <p class="text-muted small mb-2" style="font-size: 0.75rem;">
                                    <strong>Required:</strong> At least 1 passenger information required. 
                                    @if(count($selectedSeats) > 0)
                                        You can add up to {{ count($selectedSeats) }} passenger(s) for {{ count($selectedSeats) }} selected seat(s).
                                    @endif
                                </p>
                                <div style="max-height: 250px; overflow-y: auto;">
                                    @foreach($passengers as $index => $passenger)
                                        <div class="card mb-3 border-2 {{ $passenger['is_required'] ? '' : 'border-warning' }}" 
                                             style="border-color: {{ $passenger['is_required'] ? '#e9ecef' : '#ffc107' }};">
                                            <div class="card-header" style="background-color: {{ $passenger['is_required'] ? '#f8f9fa' : '#fff3cd' }};">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0">
                                                        @if($passenger['is_required'])
                                                            <i class="fas fa-user"></i> Passenger {{ $index + 1 }} <span class="badge bg-danger ms-2">Required</span>
                                                        @else
                                                            <i class="fas fa-user-plus"></i> Passenger {{ $index + 1 }}
                                                        @endif
                                                    </h6>
                                                    @if(!$passenger['is_required'])
                                                        <button type="button" 
                                                                class="btn btn-sm btn-outline-danger" 
                                                                wire:click="removePassenger({{ $index }})"
                                                                title="Remove this passenger">
                                                            <i class="fas fa-trash"></i> Remove
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="card-body">
                                                <div class="row g-2">
                                                    <div class="col-md-6">
                                                        <label class="form-label small">Name <span class="text-danger">*</span></label>
                                                        <input type="text" 
                                                               class="form-control form-control-sm" 
                                                               wire:model="passengers.{{ $index }}.name"
                                                               placeholder="Full Name" 
                                                               maxlength="100">
                                                        @error("passengers.{$index}.name") 
                                                            <small class="text-danger">{{ $message }}</small> 
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small">Age <span class="text-danger">*</span></label>
                                                        <input type="number" 
                                                               class="form-control form-control-sm" 
                                                               wire:model="passengers.{{ $index }}.age"
                                                               min="1" 
                                                               max="120" 
                                                               placeholder="Age">
                                                        @error("passengers.{$index}.age") 
                                                            <small class="text-danger">{{ $message }}</small> 
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-3">
                                                        <label class="form-label small">Gender <span class="text-danger">*</span></label>
                                                        <select class="form-select form-select-sm" 
                                                                wire:model="passengers.{{ $index }}.gender">
                                                            <option value="">Select</option>
                                                            <option value="male">👨 Male</option>
                                                            <option value="female">👩 Female</option>
                                                        </select>
                                                        @error("passengers.{$index}.gender") 
                                                            <small class="text-danger">{{ $message }}</small> 
                                                        @enderror
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">CNIC</label>
                                                        <input type="text" 
                                                               class="form-control form-control-sm" 
                                                               wire:model="passengers.{{ $index }}.cnic"
                                                               placeholder="12345-1234567-1" 
                                                               maxlength="20">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">Phone</label>
                                                        <input type="text" 
                                                               class="form-control form-control-sm" 
                                                               wire:model="passengers.{{ $index }}.phone"
                                                               placeholder="03001234567" 
                                                               maxlength="20">
                                                    </div>
                                                    <div class="col-md-4">
                                                        <label class="form-label small">Email</label>
                                                        <input type="email" 
                                                               class="form-control form-control-sm" 
                                                               wire:model="passengers.{{ $index }}.email"
                                                               placeholder="email@example.com" 
                                                               maxlength="100">
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
                            <button class="btn btn-success w-100 fw-bold py-2 small" 
                                    wire:click="confirmBooking"
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
                <div class="col-lg-4 col-md-12">
                    <!-- Trip Passengers List Card -->
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 small">
                                <i class="fas fa-list-check"></i> Booked Passengers
                                <span class="badge bg-info ms-2">Total Passengers: {{ count($tripPassengers) }}</span>
                                <span class="badge bg-success ms-2">Total Earnings: PKR {{ number_format($totalEarnings, 2) }}</span>
                            </h6>
                            @if(count($tripPassengers) > 0)
                                <button type="button" 
                                        class="btn btn-light btn-sm fw-bold" 
                                        onclick="window.printPassengerList && window.printPassengerList()"
                                        title="Print Passenger List">
                                    <i class="fas fa-print"></i> Print List
                                </button>
                            @endif
                        </div>
                        <div class="card-body p-2 scrollable-content">
                            @if(count($tripPassengers) > 0)
                                <div class="table-responsive">
                                    <table id="passengerListTable" class="table table-sm table-hover mb-0">
                                        <thead class="table-light">
                                            <tr>
                                                <th class="small">Booking #</th>
                                                <th class="small">Seat</th>
                                                <th class="small">Name</th>
                                                <th class="small">Gender</th>
                                                <th class="small">Age</th>
                                                <th class="small">Phone</th>
                                                <th class="small">From</th>
                                                <th class="small">To</th>
                                                <th class="small">Amount</th>
                                                <th class="small">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($tripPassengers as $passenger)
                                                <tr>
                                                    <td class="small">
                                                        <span class="badge bg-dark">{{ $passenger['booking_number'] }}</span>
                                                    </td>
                                                    <td class="small">
                                                        <span class="badge bg-info">{{ $passenger['seats_display'] }}</span>
                                                    </td>
                                                    <td class="small">
                                                        <strong>{{ $passenger['name'] }}</strong>
                                                    </td>
                                                    <td class="small">
                                                        @if($passenger['gender'])
                                                            <span class="badge bg-secondary">{{ ucfirst($passenger['gender']) }}</span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="small">{{ $passenger['age'] ?? '-' }}</td>
                                                    <td class="small">
                                                        @if($passenger['phone'])
                                                            <i class="bx bx-phone"></i> {{ $passenger['phone'] }}
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="small">
                                                        <small>{{ $passenger['from_code'] }}</small>
                                                    </td>
                                                    <td class="small">
                                                        <small>{{ $passenger['to_code'] }}</small>
                                                    </td>
                                                    <td class="small">
                                                        <strong class="text-success">PKR {{ number_format($passenger['final_amount'], 2) }}</strong>
                                                    </td>
                                                    <td class="small">
                                                        @php
                                                            $statusBadge = match($passenger['status']) {
                                                                'confirmed' => 'bg-success',
                                                                'hold' => 'bg-warning',
                                                                'cancelled' => 'bg-danger',
                                                                default => 'bg-secondary'
                                                            };
                                                        @endphp
                                                        <span class="badge {{ $statusBadge }}">{{ ucfirst($passenger['status'] ?? 'N/A') }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="table-light">
                                            <tr>
                                                <td colspan="7" class="text-end fw-bold small">
                                                    <strong>Total Passengers:</strong> <span class="badge bg-info">{{ count($tripPassengers) }}</span>
                                                </td>
                                                <td class="text-end fw-bold small">Total Earnings:</td>
                                                <td class="fw-bold text-success small">PKR {{ number_format($totalEarnings, 2) }}</td>
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
                    <button type="button" 
                            class="btn btn-outline-primary btn-lg flex-grow-1 fw-bold"
                            onclick="window.setGender('male')"
                            data-gender="male">
                        👨 Male
                    </button>
                    <button type="button" 
                            class="btn btn-outline-danger btn-lg flex-grow-1 fw-bold"
                            onclick="window.setGender('female')"
                            data-gender="female">
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
                    <button type="button" class="btn-close btn-close-white" wire:click="closeBusAssignmentModal"></button>
                </div>
                <div class="modal-body py-4">
                    @if ($showBusAssignmentModal)
                        <!-- Bus Selection -->
                        <div class="mb-4">
                            <label class="form-label fw-bold">
                                <i class="fas fa-bus"></i> Select Bus <span class="text-danger">*</span>
                            </label>
                            <select class="form-select" wire:model="selectedBusId">
                                <option value="">-- Select Bus --</option>
                                @foreach ($availableBuses as $bus)
                                    <option value="{{ $bus->id }}">
                                        {{ $bus->name }} ({{ $bus->registration_number }}) - {{ $bus->model }}
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
                                <div class="col-md-6">
                                    <label class="form-label small">Driver Name <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           wire:model="driverName"
                                           placeholder="Enter driver name" 
                                           maxlength="255">
                                    @error('driverName') 
                                        <small class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Driver Phone <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           wire:model="driverPhone"
                                           placeholder="03001234567" 
                                           maxlength="20">
                                    @error('driverPhone') 
                                        <small class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Driver CNIC <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           wire:model="driverCnic"
                                           placeholder="42101-1234567-1" 
                                           maxlength="50">
                                    @error('driverCnic') 
                                        <small class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Driver License <span class="text-danger">*</span></label>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           wire:model="driverLicense"
                                           placeholder="PK-DL-2023-001" 
                                           maxlength="100">
                                    @error('driverLicense') 
                                        <small class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label small">Driver Address</label>
                                    <textarea class="form-control form-control-sm" 
                                              wire:model="driverAddress"
                                              rows="2" 
                                              placeholder="Enter driver address" 
                                              maxlength="500"></textarea>
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
                                <div class="col-md-6">
                                    <label class="form-label small">Host Name</label>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           wire:model="hostName"
                                           placeholder="Enter host name" 
                                           maxlength="255">
                                    @error('hostName') 
                                        <small class="text-danger">{{ $message }}</small> 
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small">Host Phone</label>
                                    <input type="text" 
                                           class="form-control form-control-sm" 
                                           wire:model="hostPhone"
                                           placeholder="03001234567" 
                                           maxlength="20">
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
                                    <i class="fas fa-receipt"></i> Expenses (From {{ $fromStop['terminal_name'] ?? 'Current' }} to Next Stop)
                                </h6>
                                <button type="button" 
                                        class="btn btn-sm btn-outline-primary" 
                                        wire:click="addExpense">
                                    <i class="fas fa-plus"></i> Add Expense
                                </button>
                            </div>
                            @foreach ($expenses as $index => $expense)
                                <div class="card mb-3 border-2">
                                    <div class="card-body p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="mb-0 small fw-bold">Expense {{ $index + 1 }}</h6>
                                            @if (count($expenses) > 1)
                                                <button type="button" 
                                                        class="btn btn-sm btn-outline-danger" 
                                                        wire:click="removeExpense({{ $index }})">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-4">
                                                <label class="form-label small">Expense Type</label>
                                                <select class="form-select form-select-sm" 
                                                        wire:model="expenses.{{ $index }}.expense_type">
                                                    <option value="">-- Select Type --</option>
                                                    @foreach ($expenseTypes as $type)
                                                        <option value="{{ $type['value'] }}">{{ $type['label'] }}</option>
                                                    @endforeach
                                                </select>
                                                @error("expenses.{$index}.expense_type") 
                                                    <small class="text-danger">{{ $message }}</small> 
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Amount (PKR)</label>
                                                <input type="number" 
                                                       class="form-control form-control-sm" 
                                                       wire:model="expenses.{{ $index }}.amount"
                                                       placeholder="0.00" 
                                                       min="0" 
                                                       step="0.01">
                                                @error("expenses.{$index}.amount") 
                                                    <small class="text-danger">{{ $message }}</small> 
                                                @enderror
                                            </div>
                                            <div class="col-md-4">
                                                <label class="form-label small">Description</label>
                                                <input type="text" 
                                                       class="form-control form-control-sm" 
                                                       wire:model="expenses.{{ $index }}.description"
                                                       placeholder="Optional description" 
                                                       maxlength="500">
                                                @error("expenses.{$index}.description") 
                                                    <small class="text-danger">{{ $message }}</small> 
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i> Expenses will be recorded from {{ $fromStop['terminal_name'] ?? 'current terminal' }} to the next stop.
                            </small>
                        </div>
                    @endif
                </div>
                <div class="modal-footer d-flex gap-2">
                    <button type="button" 
                            class="btn btn-secondary" 
                            wire:click="closeBusAssignmentModal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" 
                            class="btn btn-primary fw-bold" 
                            wire:click="assignBus"
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
                    <div class="mb-4 p-3 bg-light rounded text-center">
                        <h6 class="text-muted mb-2">Booking Number</h6>
                        <h3 class="fw-bold text-primary" id="bookingNumberDisplay">-</h3>
                    </div>

                    <div class="mb-4">
                        <p class="mb-2"><strong>Seats:</strong> <span id="bookedSeatsDisplay" class="badge bg-info ms-2">-</span></p>
                        <p class="mb-0"><strong>Status:</strong> <span id="bookingStatusDisplay" class="badge bg-success ms-2">-</span></p>
                    </div>

                    <div class="alert alert-light border-2 mb-4">
                        <h6 class="fw-bold mb-3">Fare Breakdown</h6>
                        <p class="mb-2"><strong>Total Fare:</strong> <span class="float-end">PKR <span id="confirmedFare">0.00</span></span></p>
                        <p class="mb-2"><strong>Discount:</strong> <span class="float-end">-PKR <span id="confirmedDiscount">0.00</span></span></p>
                        <p class="mb-2"><strong>Tax/Charge:</strong> <span class="float-end">+PKR <span id="confirmedTax">0.00</span></span></p>
                        <hr>
                        <p class="mb-0"><strong>Final Amount:</strong> <span class="float-end fw-bold text-success">PKR <span id="confirmedFinal">0.00</span></span></p>
                    </div>

                    <p><strong>Payment Method:</strong> <span class="badge bg-warning ms-2" id="paymentMethodDisplay">-</span></p>
                </div>
                <div class="modal-footer d-flex gap-2">
                    <button type="button" 
                            class="btn btn-primary btn-lg fw-bold flex-fill" 
                            id="printTicketBtn">
                        <i class="fas fa-print"></i> Print Ticket (80mm)
                    </button>
                    <button type="button" 
                            class="btn btn-success btn-lg fw-bold flex-fill" 
                            data-bs-dismiss="modal">
                        <i class="fas fa-check"></i> Done
                    </button>
                </div>
            </div>
        </div>
    </div>

    @script
    <script>
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

        $wire.on('booking-success', (...args) => {
            console.log('Booking success event received - full args:', args);
            console.log('Args length:', args.length);
            
            // Livewire v3 passes event data as arguments
            // The data can be in args[0] or args[0][0] depending on how it's dispatched
            let bookingData;
            
            if (args.length > 0) {
                // Check if first arg is an array with data
                if (Array.isArray(args[0]) && args[0].length > 0) {
                    bookingData = args[0][0];
                } else if (args[0] && typeof args[0] === 'object') {
                    bookingData = args[0];
                } else if (args.length > 1 && args[1] && typeof args[1] === 'object') {
                    bookingData = args[1];
                }
            }
            
            if (!bookingData) {
                console.error('Could not parse booking data. Args:', args);
                return;
            }
            
            console.log('Parsed booking data:', bookingData);
            
            // Store booking ID for reprint
            lastBookingId = bookingData.bookingId || bookingData[0]?.bookingId;
            
            // Extract booking details - try multiple property formats
            const bookingNumber = bookingData.bookingNumber || bookingData[0]?.bookingNumber || '';
            const bookingId = bookingData.bookingId || bookingData[0]?.bookingId;
            const seats = bookingData.seats || bookingData[0]?.seats || '-';
            const status = bookingData.status || bookingData[0]?.status || 'confirmed';
            const totalFare = bookingData.totalFare || bookingData[0]?.totalFare || 0;
            const discountAmount = bookingData.discountAmount || bookingData[0]?.discountAmount || 0;
            const taxAmount = bookingData.taxAmount || bookingData[0]?.taxAmount || 0;
            const finalAmount = bookingData.finalAmount || bookingData[0]?.finalAmount || 0;
            const paymentMethod = bookingData.paymentMethod || bookingData[0]?.paymentMethod || 'cash';
            
            console.log('Extracted booking number:', bookingNumber);
            
            // Populate booking details
            const bookingNumberEl = document.getElementById('bookingNumberDisplay');
            if (bookingNumberEl) {
                const displayText = bookingNumber ? '#' + bookingNumber : '-';
                bookingNumberEl.textContent = displayText;
                bookingNumberEl.innerHTML = displayText; // Also set innerHTML as fallback
                console.log('Set booking number to:', displayText);
            } else {
                console.error('bookingNumberDisplay element not found');
            }
            
            const bookedSeatsEl = document.getElementById('bookedSeatsDisplay');
            if (bookedSeatsEl) {
                bookedSeatsEl.textContent = seats || '-';
            }
            
            const statusEl = document.getElementById('bookingStatusDisplay');
            if (statusEl) {
                statusEl.textContent = status ? status.charAt(0).toUpperCase() + status.slice(1) : 'Confirmed';
            }
            
            const fareEl = document.getElementById('confirmedFare');
            if (fareEl) {
                fareEl.textContent = parseFloat(totalFare || 0).toFixed(2);
            }
            
            const discountEl = document.getElementById('confirmedDiscount');
            if (discountEl) {
                discountEl.textContent = parseFloat(discountAmount || 0).toFixed(2);
            }
            
            const taxEl = document.getElementById('confirmedTax');
            if (taxEl) {
                taxEl.textContent = parseFloat(taxAmount || 0).toFixed(2);
            }
            
            const finalEl = document.getElementById('confirmedFinal');
            if (finalEl) {
                finalEl.textContent = parseFloat(finalAmount || 0).toFixed(2);
            }
            
            // Payment method display
            const paymentMethodDisplay = document.getElementById('paymentMethodDisplay');
            if (paymentMethodDisplay) {
                const paymentText = paymentMethod.charAt(0).toUpperCase() + paymentMethod.slice(1).replace('_', ' ');
                paymentMethodDisplay.textContent = paymentText;
            }
            
            // Setup print button
            const printBtn = document.getElementById('printTicketBtn');
            if (printBtn && bookingId) {
                printBtn.onclick = function() {
                    printBooking(bookingId);
                };
            }
            
            // Show modal
            const successModalElement = document.getElementById('successModal');
            if (successModalElement) {
                const successModal = new bootstrap.Modal(successModalElement);
                successModal.show();
            }
        });

        function printBooking(bookingId) {
            if (!bookingId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Booking',
                    text: 'Booking ID is missing.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            try {
                const printWindow = window.open(`/admin/bookings/${bookingId}/print`, '_blank');
                
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

        // Make printBooking available globally
        window.printBooking = printBooking;

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

            // Get trip information
            const tripData = @json($tripData ?? null);
            const travelDate = @json($travelDate ?? '');
            const fromStop = @json($fromStop ?? null);
            const toStop = @json($toStop ?? null);
            const fromTerminal = fromStop?.terminal_name || 'N/A';
            const toTerminal = toStop?.terminal_name || 'N/A';
            const departureTime = tripData?.departure_datetime ? new Date(tripData.departure_datetime).toLocaleString() : 'N/A';
            const totalPassengers = {{ count($tripPassengers) }};
            const totalEarnings = {{ $totalEarnings }};

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
                    
                    <div class="trip-info">
                        <table>
                            <tr>
                                <td>Travel Date:</td>
                                <td>${travelDate || 'N/A'}</td>
                                <td>Departure Time:</td>
                                <td>${departureTime}</td>
                            </tr>
                            <tr>
                                <td>From Terminal:</td>
                                <td>${fromTerminal}</td>
                                <td>To Terminal:</td>
                                <td>${toTerminal}</td>
                            </tr>
                            <tr>
                                <td>Total Passengers:</td>
                                <td><strong>${totalPassengers}</strong></td>
                                <td>Total Earnings:</td>
                                <td class="text-success"><strong>PKR ${parseFloat(totalEarnings).toFixed(2)}</strong></td>
                            </tr>
                        </table>
                    </div>
                    
                    ${table.outerHTML}
                    
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

        $wire.on('show-error', (event) => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: event.message,
                confirmButtonColor: '#d33'
            });
        });

        $wire.on('show-success', (event) => {
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
            
            // Initialize Select2 if needed
            if (typeof $ !== 'undefined' && $.fn.select2) {
                $('.select2').select2({
                    width: 'resolve'
                });
            }
        });
        
        // Function to setup Echo channel subscription
        function setupEchoChannel(tripId) {
            if (!tripId || !window.Echo) {
                console.error('Cannot setup WebSocket - tripId:', tripId, 'Echo available:', !!window.Echo);
                if (!window.Echo) {
                    console.error('Laravel Echo is not loaded. Make sure vite is included in the layout and assets are built. resources/js/app.js');
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
                    console.log('Component initialized with existing trip. Setting up WebSocket for trip:', tripId);
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