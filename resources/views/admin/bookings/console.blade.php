@extends('admin.layouts.app')

@section('title', 'Booking Console')

@section('content')
    <style>
        /* Responsive Layout Styles */
        @media (max-width: 1199px) {

            .col-lg-3,
            .col-lg-5,
            .col-lg-4 {
                margin-bottom: 1.5rem;
            }
        }

        @media (max-width: 767px) {
            .col-md-6 {
                font-size: 0.9rem;
            }

            .form-control-sm {
                font-size: 0.8rem !important;
            }

            .small {
                font-size: 0.75rem !important;
            }
        }

        /* Seat map styling - Clean Modern Design like Image */
        .seat-map-container {
            background: #F8FAFC;
            padding: 1.5rem;
            border-radius: 12px;
            min-height: 500px;
        }
        
        .seat-row-container {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            margin-bottom: 0.75rem;
        }
        
        .seat-pair-left, .seat-pair-right {
            display: flex;
            gap: 0.5rem;
        }
        
        .seat-aisle {
            width: 40px;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 0.7rem;
        }
        
        .seat-grid {
            display: flex;
            flex-direction: column;
            align-items: center;
            width: 100%;
            padding: 0;
        }
        
        /* Seat button styling - Clean Design */
        .seat-btn {
            min-width: 45px;
            min-height: 45px;
            width: 45px;
            height: 45px;
            font-size: 0.85rem;
            font-weight: 600;
            padding: 0;
            line-height: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 1.5px solid #cbd5e1;
            border-radius: 6px;
            transition: all 0.2s ease;
            cursor: pointer;
            position: relative;
        }
        
        /* Gender badge styling - Top right corner */
        .seat-gender-badge {
            position: absolute;
            top: -4px;
            right: -4px;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7rem;
            line-height: 1;
            border: 2px solid #ffffff;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
            z-index: 10;
        }
        
        .seat-gender-badge.male-badge {
            background: #3B82F6;
        }
        
        .seat-gender-badge.female-badge {
            background: #EC4899;
        }
        
        .seat-btn:hover:not(:disabled) {
            transform: scale(1.05);
            border-color: #94a3b8;
        }
        
        .seat-btn:disabled {
            cursor: not-allowed;
            opacity: 0.9;
        }
        
        /* Seat status colors - Matching Image */
        .seat-available {
            background: #E2E8F0;
            color: #334155;
            border-color: #cbd5e1;
        }
        
        .seat-selected {
            background: #3B82F6;
            color: #ffffff;
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
        }
        
        .seat-booked-male {
            background: #22D3EE;
            color: #ffffff;
            border-color: #06b6d4;
        }
        
        .seat-booked-female {
            background: #EC4899;
            color: #ffffff;
            border-color: #db2777;
        }
        
        .seat-held {
            background: #fbbf24;
            color: #78350f;
            border-color: #f59e0b;
        }

        /* Card body compact padding */
        .card-body.p-2 {
            padding: 0.5rem !important;
        }

        /* Scrollable areas */
        .scrollable-content {
            max-height: calc(100vh - 300px);
            overflow-y: auto;
        }

        /* Badge sizing */
        .badge.small {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }

        /* Alert sizing */
        .alert.small {
            padding: 0.5rem !important;
            margin-bottom: 0.5rem !important;
        }

        /* Form label sizing */
        .form-label.small {
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }

        /* Passenger info container */
        #passengerInfoContainer {
            scrollbar-width: thin;
            scrollbar-color: rgba(0, 0, 0, 0.2) rgba(0, 0, 0, 0.1);
        }

        #passengerInfoContainer::-webkit-scrollbar {
            width: 6px;
        }

        #passengerInfoContainer::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.1);
        }

        #passengerInfoContainer::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.2);
            border-radius: 3px;
        }

        /* Seat map legend - horizontal flex layout */
        .seat-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            width: 100%;
        }

        .seat-legend .badge {
            flex-shrink: 0;
            white-space: nowrap;
        }
    </style>
    <div class="container-fluid p-4">
        <!-- Header Section -->
        <div class="card mb-4 shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">
                    <i class="fas fa-ticket-alt"></i>
                    Booking Console - Real-Time Seat Booking
                    @if (auth()->user()->hasRole('admin'))
                        <span class="badge bg-info ms-2">Admin Mode</span>
                    @else
                        <span class="badge bg-warning ms-2">Employee Mode - Terminal:
                            {{ auth()->user()->terminal?->name ?? 'N/A' }}</span>
                    @endif
                </h5>
            </div>
            <div class="card-body bg-light">
                <div class="row g-3">

                    <!-- Date -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Travel Date</label>
                        <input type="date" class="form-control form-control-lg" id="travelDate"
                            value="{{ now()->format('Y-m-d') }}" min="{{ now()->format('Y-m-d') }}"
                            max="{{ now()->addDays(10)->format('Y-m-d') }}" />
                    </div>

                    <!-- From Terminal / Stop -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">From Terminal</label>
                        <select class="form-select form-select-lg select2" id="fromTerminal"
                            @if (!auth()->user()->hasRole('admin')) disabled @endif>
                            <option value="">Loading...</option>
                        </select>
                    </div>

                    <!-- To Terminal / Stop -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">To Terminal</label>
                        <select class="form-select form-select-lg select2" id="toTerminal" disabled
                            onchange="fetchArrivalTime()">
                            <option value="">Select Destination</option>
                        </select>
                    </div>

                    <!-- Departure Time (Timetable Stops) -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Departure Time</label>
                        <select class="form-select form-select-lg select2" id="departureTime" onchange="fetchArrivalTime()"
                            disabled>
                            <option value="">Select Departure Time</option>
                        </select>
                    </div>

                    <!-- Arrival Time (Timetable Stops) -->
                    <div class="col-md-2">
                        <label class="form-label fw-bold">Arrival Time</label>
                        <input type="text" class="form-control form-control-lg" id="arrivalTime" disabled readonly>
                    </div>

                    <!-- Load Trip Button -->
                    <div class="col-md-1 d-flex align-items-end gap-2">
                        <button class="btn btn-primary btn-lg flex-grow-1 fw-bold" id="loadTripBtn" onclick="loadTrip()">
                            <i class="fas fa-play"></i> Load
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Trip Content (shown when trip loaded) -->
        <div id="tripContent" style="display: none;">
            <!-- Trip Details Card (Above all sections) -->
            <div class="card mb-4 shadow-sm border-0" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body text-white p-4">
                    <div class="row g-4">
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="font-size: 2.5rem;">
                                    <i class="fas fa-route"></i>
                                </div>
                                <div>
                                    <small class="d-block opacity-75" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Route</small>
                                    <h5 class="mb-0 fw-bold" id="tripRoute">-</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="font-size: 2.5rem;">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div>
                                    <small class="d-block opacity-75" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Date</small>
                                    <h5 class="mb-0 fw-bold" id="tripDate">-</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="d-flex align-items-center">
                                <div class="me-3" style="font-size: 2.5rem;">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div>
                                    <small class="d-block opacity-75" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Time</small>
                                    <h5 class="mb-0 fw-bold" id="tripTime">-</h5>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div id="busDriverSection"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <!-- Left Column: Seat Map (3 columns) -->
                <div class="col-lg-3 col-md-6">
                    <div class="card shadow-sm h-100 border-0">
                        <div class="card-header text-white" style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%);">
                            <h6 class="mb-0 fw-bold">
                                <i class="fas fa-chair"></i> Seat Map
                            </h6>
                        </div>
                        <div class="card-body p-3"
                            style="max-height: calc(100vh - 300px); overflow-y: auto;">
                            <!-- Summary Card (Matching Image Design) -->
                            <div class="mb-3 p-3 bg-white rounded-lg shadow-sm w-100 border border-gray-200">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small text-dark">Outbound</span>
                                    <span class="small text-dark" id="outboundFare">Rs 0</span>
                                </div>
                                <hr class="my-2" style="border-color: #e2e8f0; margin: 0.5rem 0;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="small text-dark fw-semibold">Total</span>
                                    <span class="small fw-bold" style="color: #3B82F6;" id="totalSummaryFare">Rs 0</span>
                                </div>
                            </div>
                            
                            <!-- Legend -->
                            <div class="mb-3 p-3 bg-white rounded-lg shadow-sm w-100 border border-gray-200">
                                <div class="seat-legend">
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2" style="width: 16px; height: 16px; background: #E2E8F0; border: 1px solid #cbd5e1; border-radius: 4px;"></div>
                                        <span class="small text-dark">Available</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2" style="width: 16px; height: 16px; background: #3B82F6; border: 1px solid #2563eb; border-radius: 4px; position: relative;">
                                            <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.5rem;">👨</span>
                                        </div>
                                        <span class="small text-dark">Selected (Male)</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2" style="width: 16px; height: 16px; background: #3B82F6; border: 1px solid #2563eb; border-radius: 4px; position: relative;">
                                            <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.5rem;">👩</span>
                                        </div>
                                        <span class="small text-dark">Selected (Female)</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2" style="width: 16px; height: 16px; background: #22D3EE; border: 1px solid #06b6d4; border-radius: 4px; position: relative;">
                                            <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.5rem;">👨</span>
                                        </div>
                                        <span class="small text-dark">Male Booked</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <div class="me-2" style="width: 16px; height: 16px; background: #EC4899; border: 1px solid #db2777; border-radius: 4px; position: relative;">
                                            <span style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); font-size: 0.5rem;">👩</span>
                                        </div>
                                        <span class="small text-dark">Female Booked</span>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        <div class="me-2" style="width: 16px; height: 16px; background: #fbbf24; border: 1px solid #f59e0b; border-radius: 4px;"></div>
                                        <span class="small text-dark">Held</span>
                                    </div>
                                </div>
                            </div>
                            <!-- Seat Grid -->
                            <div class="seat-map-container">
                                <h6 class="text-center mb-3" style="color: #334155; font-weight: 600; font-size: 1rem;">Select Your Seat</h6>
                                <div class="seat-grid" id="seatGrid"></div>
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
                        <div class="card-body" style="max-height: calc(100vh - 300px); overflow-y: auto; padding: 1rem;">
                            <!-- Selected Seats -->
                            <div class="mb-2">
                                <label class="form-label fw-bold small mb-1">
                                    <i class="fas fa-list"></i> Selected Seats
                                    <span class="badge bg-primary ms-2" id="seatCount">(0)</span>
                                </label>
                                <div id="selectedSeatsList" class="d-flex flex-wrap gap-2 mb-0" style="min-height: 40px;">
                                    <span class="text-muted small">No seats selected yet</span>
                                </div>
                            </div>

                            <!-- Fare Calculation -->
                            <div class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                <h6 class="fw-bold mb-2 small"><i class="fas fa-calculator"></i> Fare</h6>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Base Fare</label>
                                        <input type="number" class="form-control form-control-sm" id="baseFare"
                                            min="0" step="0.01" placeholder="0.00" readonly>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Discount</label>
                                        <input type="text" class="form-control form-control-sm" id="discountInfo"
                                            placeholder="None" readonly>
                                    </div>
                                </div>
                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Total Fare</label>
                                        <input type="number" class="form-control form-control-sm fw-bold text-success"
                                            id="totalFare" min="0" step="0.01" placeholder="0.00" readonly>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small mb-1">Tax/Charge</label>
                                        <input type="number" class="form-control form-control-sm" id="tax"
                                            min="0" step="0.01" value="0" placeholder="0.00"
                                            onchange="calculateFinal()">
                                    </div>
                                </div>
                                <div class="alert alert-primary border-1 mb-0 p-2 small text-center">
                                    <strong class="d-block mb-0">Final: PKR <span id="finalAmount" class="text-success">0.00</span></strong>
                                </div>
                            </div>

                            <!-- Booking Type & Payment -->
                            <div class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                <h6 class="fw-bold mb-2 small"><i class="fas fa-bookmark"></i> Type & Payment</h6>
                                <div class="row g-2">
                                    <div class="col-md-6 mb-2">
                                        <label class="form-label small fw-bold">Booking Type</label>
                                        <select class="form-select form-select-sm" id="bookingType" name="bookingType"
                                            onchange="togglePaymentFields()">
                                            <option value="counter" selected>🏪 Counter</option>
                                            <option value="phone">📞 Phone (Hold till before 60 mins of departure)
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mb-2" id="paymentMethodSelect" style="display: block;">
                                        <label class="form-label small fw-bold">Payment Method</label>
                                        <select class="form-select form-select-sm" id="paymentMethod"
                                            name="paymentMethod" onchange="toggleTransactionIdField()">
                                            @foreach ($paymentMethods as $method)
                                                <option value="{{ $method['value'] }}"
                                                    {{ $loop->first ? 'selected' : '' }}>
                                                    {{ $method['label'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Payment Fields (Counter Only) -->
                            <div id="paymentFields" class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                <h6 class="fw-bold mb-2 small"><i class="fas fa-credit-card"></i> Payment Details</h6>

                                <!-- Transaction ID Field (for non-cash payments) -->
                                <div class="mb-2" id="transactionIdField" style="display: none;">
                                    <label class="form-label small">Transaction ID</label>
                                    <input type="text" class="form-control form-control-sm" id="transactionId"
                                        placeholder="TXN123456789" maxlength="100">
                                </div>

                                <div class="mb-2" id="amountReceivedField">
                                    <label class="form-label small">Amount Received (PKR)</label>
                                    <input type="number" class="form-control form-control-sm" id="amountReceived"
                                        min="0" step="0.01" placeholder="0.00" value="0"
                                        onchange="calculateReturn()">
                                </div>
                                <div id="returnDiv" style="display: none;">
                                    <div class="alert alert-success border-1 mb-0 p-2 small">
                                        <strong>💰 Return: PKR <span id="returnAmount">0.00</span></strong>
                                    </div>
                                </div>
                            </div>

                            <!-- Notes -->
                            <div class="mb-2">
                                <label class="form-label small fw-bold"><i class="fas fa-sticky-note"></i> Notes</label>
                                <textarea class="form-control form-control-sm" id="notes" rows="2" maxlength="500"
                                    placeholder="Optional notes..."></textarea>
                            </div>

                            <!-- Passenger Information Section -->
                            <div class="mb-2 p-2 bg-light rounded border border-secondary-subtle">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fw-bold mb-0 small"><i class="fas fa-users"></i> Passengers</h6>
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="addPassengerBtn"
                                        onclick="addExtraPassenger()">
                                        <i class="fas fa-plus-circle"></i> Add Passenger
                                    </button>
                                </div>
                                <p class="text-muted small mb-2" style="font-size: 0.75rem;"><strong>Required:</strong>
                                    At least 1 passenger information.</p>
                                <div id="passengerInfoContainer" style="max-height: 250px; overflow-y: auto;"></div>
                            </div>

                            <!-- Confirm Button -->
                            <button class="btn btn-success w-100 fw-bold py-2 small" onclick="confirmBooking()"
                                id="confirmBtn">
                                <i class="fas fa-check-circle"></i> Confirm Booking
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Assign Bus & Trip Passengers List (4 columns) -->
                <div class="col-lg-4 col-md-12">
                    <!-- Assign Bus Card -->
                    <div class="card shadow-sm mb-3" id="assignBusCard" style="display: none;">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0 small">
                                <i class="fas fa-bus"></i> Bus Assignment
                            </h6>
                        </div>
                        <div class="card-body p-2">
                            <div id="assignBusCardContent">
                                <p class="text-muted small mb-2">Assign a bus and driver for this trip.</p>
                                <button class="btn btn-primary btn-sm w-100 fw-bold" id="assignBusBtnCard"
                                    onclick="openAssignBusModalFromHeader()">
                                    <i class="fas fa-bus"></i> Assign Bus & Driver
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Trip Passengers List Card -->
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-warning text-dark">
                            <h6 class="mb-0 small">
                                <i class="fas fa-list-check"></i> Booked Passengers
                            </h6>
                        </div>
                        <div class="card-body p-2" style="max-height: calc(100vh - 250px); overflow-y: auto;">
                            <div id="tripPassengersList"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Gender Selection Modal -->
    <div class="modal fade" id="genderModal" tabindex="-1">
        <div class="modal-dialog modal-sm modal-dialog-centered">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-user"></i> Select Gender - <span id="seatLabel">Seat</span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <p class="text-center mb-0">Please select passenger gender:</p>
                </div>
                <div class="modal-footer gap-2">
                    <button type="button" class="btn btn-outline-primary btn-lg flex-grow-1 fw-bold"
                        onclick="selectGender('male')">
                        👨 Male
                    </button>
                    <button type="button" class="btn btn-outline-danger btn-lg flex-grow-1 fw-bold"
                        onclick="selectGender('female')">
                        👩 Female
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Booking Success Modal -->
    <div class="modal fade" id="successModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
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
                        <h3 class="fw-bold text-primary" id="bookingNumber">-</h3>
                    </div>

                    <div class="mb-4">
                        <p class="mb-2"><strong>Seats:</strong> <span id="bookedSeats"
                                class="badge bg-info ms-2"></span></p>
                        <p class="mb-0"><strong>Status:</strong> <span id="bookingStatus"
                                class="badge bg-success ms-2"></span></p>
                    </div>

                    <div class="alert alert-light border-2 mb-4">
                        <h6 class="fw-bold mb-3">Fare Breakdown</h6>
                        <p class="mb-2"><strong>Total Fare:</strong> <span class="float-end">PKR <span
                                    id="confirmedFare">0.00</span></span></p>
                        <p class="mb-2"><strong>Discount:</strong> <span class="float-end">-PKR <span
                                    id="confirmedDiscount">0.00</span></span></p>
                        <p class="mb-2"><strong>Tax/Charge:</strong> <span class="float-end">+PKR <span
                                    id="confirmedTax">0.00</span></span></p>
                        <hr>
                        <p class="mb-0"><strong>Final Amount:</strong> <span class="float-end fw-bold text-success">PKR
                                <span id="confirmedFinal">0.00</span></span></p>
                    </div>

                    <p><strong>Payment Method:</strong> <span class="badge bg-warning ms-2"
                            id="paymentMethodDisplay">-</span></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success btn-lg fw-bold w-100" data-bs-dismiss="modal"
                        onclick="resetForm()">
                        <i class="fas fa-check"></i> Done
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- Assign Bus/Driver Modal -->
    <div class="modal fade" id="assignBusModal" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content shadow-lg">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title fw-bold">
                        <i class="fas fa-bus"></i> Assign Bus & Driver
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body py-4">
                    <div id="assignBusModalBody"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                    <button type="button" class="btn btn-primary" id="confirmAssignBusBtn">
                        <i class="fas fa-check"></i> Assign Bus & Driver
                    </button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        // ========================================
        // STATE MANAGEMENT
        // ========================================
        let appState = {
            isAdmin: {{ auth()->user()->hasRole('admin') ? 'true' : 'false' }},
            userId: {{ auth()->user()->id }},
            userTerminalId: {{ auth()->user()->terminal_id ?? 'null' }},
            terminals: [],
            routeStops: [],
            timetableStops: [],
            tripData: null,
            seatMap: {},
            selectedSeats: {},
            passengerInfo: {}, // ← New: Store passenger details
            pendingSeat: null,
            tripLoaded: false,
            fareData: null,
            baseFare: 0,
            lockedSeats: {}, // Track locked seats: {seatNumber: userId}
            echoChannel: null, // Echo channel for this trip
        };

        // ========================================
        // INITIALIZATION
        // ========================================
        document.addEventListener('DOMContentLoaded', function() {
            fetchTerminals();
            setupWebSocket();
            togglePaymentFields(); // Initialize payment fields visibility
            updatePassengerForms(); // Initialize passenger forms with 1 mandatory passenger
        });

        // ========================================
        // FETCH TERMINALS
        // ========================================
        function fetchTerminals() {
            showLoader(true, 'Loading terminals...');
            $.ajax({
                url: "{{ route('admin.bookings.terminals') }}",
                type: 'GET',
                success: function(response) {
                    appState.terminals = response.terminals;
                    const fromSelect = document.getElementById('fromTerminal');

                    fromSelect.innerHTML = '<option value="">Select Terminal</option>';
                    response.terminals.forEach(t => {
                        fromSelect.innerHTML +=
                            `<option value="${t.id}">${t.name} (${t.code})</option>`;
                    });

                    // Employee: Set their terminal and disable
                    if (!appState.isAdmin && appState.userTerminalId) {
                        fromSelect.value = appState.userTerminalId;
                        fromSelect.disabled = true;
                        onFromTerminalChange();
                    } else {
                        fromSelect.disabled = false;
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Load Terminals',
                        text: 'Unable to fetch terminals. Please check your connection and try again.',
                        confirmButtonColor: '#d33'
                    });
                },
                complete: function() {
                    showLoader(false);
                }
            });
        }

        // ========================================
        // ON FROM TERMINAL CHANGE
        // ========================================
        document.getElementById('fromTerminal')?.addEventListener('change', onFromTerminalChange);

        function onFromTerminalChange() {
            const fromTerminalId = document.getElementById('fromTerminal').value;
            document.getElementById('toTerminal').value = '';
            document.getElementById('departureTime').innerHTML = '<option value="">Select Departure Time</option>';
            document.getElementById('toTerminal').disabled = true;
            document.getElementById('departureTime').disabled = true;

            if (fromTerminalId) {
                fetchToTerminals(fromTerminalId);
                fetchFare(fromTerminalId); // Fetch fare when from terminal changes
            }
        }

        // ========================================
        // FETCH TO TERMINALS (Route Stops)
        // ========================================
        function fetchToTerminals(fromTerminalId) {
            $.ajax({
                url: "{{ route('admin.bookings.route-stops') }}",
                type: 'GET',
                data: {
                    from_terminal_id: fromTerminalId
                },
                success: function(response) {
                    appState.routeStops = response.route_stops;
                    const toSelect = document.getElementById('toTerminal');

                    toSelect.innerHTML = '<option value="">Select Destination</option>';
                    response.route_stops.forEach(stop => {
                        toSelect.innerHTML +=
                            `<option value="${stop.id}">${stop.terminal.name} (${stop.terminal.code})</option>`;
                    });

                    toSelect.disabled = false;
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Load Destinations',
                        text: 'Unable to fetch available destinations for the selected terminal. Please try again.',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        }

        // ========================================
        // FETCH FARE FOR SEGMENT
        // ========================================
        function fetchFare(fromTerminalId, toTerminalId) {
            showLoader(true, 'Loading fare...');
            if (!fromTerminalId || !toTerminalId) {
                resetFareDisplay();
                return;
            }

            $.ajax({
                url: "{{ route('admin.bookings.fare') }}",
                type: 'GET',
                data: {
                    from_terminal_id: fromTerminalId,
                    to_terminal_id: toTerminalId
                },
                success: function(response) {
                    if (response.success) {
                        appState.fareData = response.fare;
                        appState.baseFare = response.fare.final_fare;

                        // Update UI with fare info
                        document.getElementById('baseFare').value = parseFloat(response.fare.final_fare)
                            .toFixed(2);

                        // Display discount info
                        if (response.fare.discount_type === 'flat') {
                            document.getElementById('discountInfo').value =
                                `Flat: PKR ${parseFloat(response.fare.discount_value).toFixed(2)}`;
                        } else if (response.fare.discount_type === 'percent') {
                            document.getElementById('discountInfo').value =
                                `${parseFloat(response.fare.discount_value).toFixed(0)}% Discount`;
                        } else {
                            document.getElementById('discountInfo').value = 'None';
                        }

                        calculateTotalFare();
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Fare Found',
                            text: 'No fare configuration found for this route segment. Please contact administrator.',
                            confirmButtonColor: '#ffc107'
                        });
                        resetFareDisplay();
                    }
                },
                error: function(error) {
                    const message = error.responseJSON?.error || 'Unable to fetch fare information';
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Load Fare',
                        text: message,
                        confirmButtonColor: '#d33'
                    });
                    resetFareDisplay();
                },
                complete: function() {
                    showLoader(false);
                }
            });
        }

        // ========================================
        // RESET FARE DISPLAY
        // ========================================
        function resetFareDisplay() {
            appState.fareData = null;
            appState.baseFare = 0;
            document.getElementById('baseFare').value = '';
            document.getElementById('discountInfo').value = '';
            document.getElementById('totalFare').value = '';
            calculateFinal();
        }

        // ========================================
        // ON TO TERMINAL CHANGE
        // ========================================
        document.getElementById('toTerminal')?.addEventListener('change', onToTerminalChange);

        function onToTerminalChange() {
            const fromTerminalId = document.getElementById('fromTerminal').value;
            const toTerminalId = document.getElementById('toTerminal').value;
            const date = document.getElementById('travelDate').value;

            document.getElementById('departureTime').innerHTML = '<option value="">Select Departure Time</option>';
            document.getElementById('departureTime').disabled = true;

            if (fromTerminalId && toTerminalId && date) {
                fetchFare(fromTerminalId, toTerminalId);
                fetchDepartureTimes(fromTerminalId, toTerminalId, date);
            }
        }

        // ========================================
        // FETCH DEPARTURE TIMES (Timetable Stops)
        // ========================================
        function fetchDepartureTimes(fromTerminalId, toTerminalId, date) {
            showLoader(true, 'Loading departure times...');
            $.ajax({
                url: "{{ route('admin.bookings.departure-times') }}",
                type: 'GET',
                data: {
                    from_terminal_id: fromTerminalId,
                    to_terminal_id: toTerminalId,
                    date: date
                },
                success: function(response) {
                    appState.timetableStops = response.timetable_stops;
                    const timeSelect = document.getElementById('departureTime');

                    timeSelect.innerHTML = '<option value="">Select Departure Time</option>';
                    response.timetable_stops.forEach(stop => {
                        timeSelect.innerHTML +=
                            `<option value="${stop.timetable_id}" data-arrival="${stop.arrival_at ?? 'N/A'}">${stop.departure_at}</option>`;
                    });
                    // console.log(timeSelect.innerHTML);
                    timeSelect.disabled = false;
                },
                error: function() {
                    Swal.fire({
                        icon: 'info',
                        title: 'No Trips Available',
                        text: 'No trips are available for the selected route and date. Please try a different date or route.',
                        confirmButtonColor: '#3085d6'
                    });
                },
                complete: function() {
                    showLoader(false);
                }
            });
        }

        // ========================================
        // ON TRAVEL DATE CHANGE
        // ========================================
        document.getElementById('travelDate')?.addEventListener('change', function() {
            const fromTerminalId = document.getElementById('fromTerminal').value;
            const toTerminalId = document.getElementById('toTerminal').value;

            if (fromTerminalId && toTerminalId) {
                fetchDepartureTimes(fromTerminalId, toTerminalId, this.value);
            }
        });

        // ========================================
        // LOAD TRIP
        // ========================================
        function loadTrip() {
            const fromTerminalId = document.getElementById('fromTerminal').value;
            const toTerminalId = document.getElementById('toTerminal').value;
            const timetableId = document.getElementById('departureTime').value;
            const date = document.getElementById('travelDate').value;

            if (!fromTerminalId || !toTerminalId || !timetableId || !date) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Information',
                    text: 'Please fill all required fields: From Terminal, To Terminal, Departure Time, and Travel Date.',
                    confirmButtonColor: '#ffc107'
                });
                return;
            }

            document.getElementById('loadTripBtn').disabled = true;
            showLoader(true, 'Loading trip...');
            $.ajax({
                url: "{{ route('admin.bookings.load-trip') }}",
                type: 'POST',
                data: {
                    from_terminal_id: fromTerminalId,
                    to_terminal_id: toTerminalId,
                    timetable_id: timetableId,
                    date: date,
                    _token: document.querySelector('meta[name="csrf-token"]').content
                },
                success: function(response) {
                    appState.tripData = response;
                    appState.seatMap = response.seat_map;
                    appState.tripLoaded = true;

                    // Update trip info display
                    document.getElementById('tripRoute').textContent = response.route.name;
                    document.getElementById('tripDate').textContent = new Date(response.trip.departure_datetime)
                        .toLocaleDateString();
                    document.getElementById('tripTime').textContent = new Date(response.trip.departure_datetime)
                        .toLocaleTimeString();

                    // Update bus & driver section
                    renderBusDriverSection(response.trip);
                    renderSeatMap();
                    document.getElementById('tripContent').style.display = 'block';
                    document.getElementById('tripContent').scrollIntoView({
                        behavior: 'smooth'
                    });
                    loadTripPassengers(response.trip.id);
                    setupTripWebSocket(response.trip.id); // Setup WebSocket for this trip
                },
                error: function(error) {
                    const message = error.responseJSON?.error ||
                        'Unable to load trip information. Please check all selections and try again.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Load Trip',
                        text: message,
                        confirmButtonColor: '#d33'
                    });
                },
                complete: function() {
                    document.getElementById('loadTripBtn').disabled = false;
                    showLoader(false);
                }
            });
        }

        // ========================================
        // FETCH ARRIVAL TIME
        // ========================================
        function fetchArrivalTime() {
            const select = document.getElementById('departureTime');
            const selectedOption = select.options[select.selectedIndex];

            // Read data-arrival attribute
            const arrivalTime = selectedOption.getAttribute('data-arrival');

            // Set input value
            const arrivalInput = document.getElementById('arrivalTime');
            arrivalInput.value = arrivalTime;
            // arrivalInput.disabled = false;
        }


        // ========================================
        // RENDER SEAT MAP - 2-2-2 Layout with Aisle
        // ========================================
        function renderSeatMap() {
            const grid = document.getElementById('seatGrid');
            grid.innerHTML = '';

            // Seat arrangement: 2-2-2 pattern (2 left, aisle, 2 right) for rows 1-10, last row (41-45) is 5 seats
            const totalSeats = 45;
            const lastRowStart = 41;
            let currentSeat = 1;

            // Rows 1-10: 2-2-2 pattern (4 seats per row)
            for (let row = 0; row < 10; row++) {
                const rowContainer = document.createElement('div');
                rowContainer.className = 'seat-row-container';

                // Left pair (2 seats)
                const leftPair = document.createElement('div');
                leftPair.className = 'seat-pair-left';

                for (let i = 0; i < 2; i++) {
                    const seatNumber = currentSeat++;
                    const seat = appState.seatMap[seatNumber];
                    const button = createSeatButton(seatNumber, seat);
                    leftPair.appendChild(button);
                }

                // Aisle
                const aisle = document.createElement('div');
                aisle.className = 'seat-aisle';
                aisle.textContent = '│';

                // Right pair (2 seats)
                const rightPair = document.createElement('div');
                rightPair.className = 'seat-pair-right';

                for (let i = 0; i < 2; i++) {
                    const seatNumber = currentSeat++;
                    const seat = appState.seatMap[seatNumber];
                    const button = createSeatButton(seatNumber, seat);
                    rightPair.appendChild(button);
                }

                rowContainer.appendChild(leftPair);
                rowContainer.appendChild(aisle);
                rowContainer.appendChild(rightPair);
                grid.appendChild(rowContainer);
            }

            // Last row: 5 seats in a row (seats 41-45)
            const lastRow = document.createElement('div');
            lastRow.className = 'seat-row-container';
            lastRow.style.gap = '0.5rem';

            for (let i = 0; i < 5; i++) {
                const seatNumber = lastRowStart + i;
                const seat = appState.seatMap[seatNumber];
                const button = createSeatButton(seatNumber, seat);
                lastRow.appendChild(button);
            }

            grid.appendChild(lastRow);
        }

        // ========================================
        // CREATE SEAT BUTTON
        // ========================================
        function createSeatButton(seatNumber, seat) {
            const button = document.createElement('button');
            button.className = 'seat-btn';
            button.type = 'button';

            // Create seat content with number
            const seatNumberSpan = document.createElement('span');
            seatNumberSpan.textContent = seatNumber;
            seatNumberSpan.style.fontSize = '0.85rem';
            seatNumberSpan.style.fontWeight = '600';
            seatNumberSpan.style.lineHeight = '1';

            // Determine seat status and apply appropriate class, also add gender icon
            let genderIcon = '';
            
            if (appState.selectedSeats[seatNumber]) {
                // Selected seat - same color for all
                button.className += ' seat-selected';
                const selectedGender = appState.selectedSeats[seatNumber];
                if (selectedGender === 'male') {
                    genderIcon = '👨';
                    button.title = `Seat ${seatNumber} - Selected (Male)`;
                } else if (selectedGender === 'female') {
                    genderIcon = '👩';
                    button.title = `Seat ${seatNumber} - Selected (Female)`;
                } else {
                    button.title = `Seat ${seatNumber} - Selected`;
                }
            } else if (appState.lockedSeats[seatNumber] && appState.lockedSeats[seatNumber] !== appState.userId) {
                button.className += ' seat-held';
                button.disabled = true;
                button.title = `Seat ${seatNumber} - Locked by another user`;
            } else if (seat?.status === 'booked') {
                // Check gender for booked seats
                if (seat?.gender === 'male') {
                    button.className += ' seat-booked-male';
                    genderIcon = '👨';
                    button.title = `Seat ${seatNumber} - Booked (Male)`;
                } else if (seat?.gender === 'female') {
                    button.className += ' seat-booked-female';
                    genderIcon = '👩';
                    button.title = `Seat ${seatNumber} - Booked (Female)`;
                } else {
                    // Fallback for booked seats without gender
                    button.className += ' seat-booked-male';
                    button.title = `Seat ${seatNumber} - Booked`;
                }
                button.disabled = true;
            } else if (seat?.status === 'held') {
                button.className += ' seat-held';
                button.disabled = true;
                button.title = `Seat ${seatNumber} - Held`;
            } else {
                button.className += ' seat-available';
                button.title = `Seat ${seatNumber} - Available`;
            }

            // Add seat number to button
            button.appendChild(seatNumberSpan);
            
            // Add gender badge in top-right corner if gender is available
            if (genderIcon) {
                const badge = document.createElement('span');
                badge.className = 'seat-gender-badge';
                badge.textContent = genderIcon;
                // Add badge color class
                if (genderIcon === '👨') {
                    badge.classList.add('male-badge');
                } else if (genderIcon === '👩') {
                    badge.classList.add('female-badge');
                }
                button.appendChild(badge);
            }

            // Additional safety check for disabled state
            if (seat?.status === 'booked' || seat?.status === 'held' ||
                (appState.lockedSeats[seatNumber] && appState.lockedSeats[seatNumber] !== appState.userId)) {
                button.disabled = true;
            }

            button.onclick = () => handleSeatClick(seatNumber);
            return button;
        }


        // ========================================
        // HANDLE SEAT CLICK
        // ========================================
        function handleSeatClick(seatNumber) {
            // If seat is already selected, deselect it (and unlock)
            if (appState.selectedSeats[seatNumber]) {
                unlockSeats([seatNumber]);
                delete appState.selectedSeats[seatNumber];
                updateSeatsList();
                renderSeatMap();
                return;
            }

            // Check if seat is locked by another user
            if (appState.lockedSeats[seatNumber] && appState.lockedSeats[seatNumber] !== appState.userId) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Seat Already Selected',
                    text: 'This seat is currently being booked by another user. Please select a different seat.',
                    confirmButtonColor: '#ffc107'
                });
                return;
            }

            // Check if seat is available
            const seat = appState.seatMap[seatNumber];
            if (!seat || seat.status === 'booked' || seat.status === 'held') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Seat Not Available',
                    text: 'This seat is not available for booking.',
                    confirmButtonColor: '#ffc107'
                });
                return;
            }

            // Lock the seat first, then show gender modal
            lockSeats([seatNumber], (success) => {
                if (success) {
                    appState.pendingSeat = seatNumber;
                    document.getElementById('seatLabel').textContent = `Seat ${seatNumber}`;
                    new bootstrap.Modal(document.getElementById('genderModal')).show();
                }
            });
        }

        // ========================================
        // SELECT GENDER
        // ========================================
        function selectGender(gender) {
            if (appState.pendingSeat) {
                appState.selectedSeats[appState.pendingSeat] = gender;
                appState.pendingSeat = null;
            }
            bootstrap.Modal.getInstance(document.getElementById('genderModal')).hide();
            updateSeatsList();
            renderSeatMap();
        }

        // ========================================
        // UPDATE SEATS LIST
        // ========================================
        function updateSeatsList() {
            const list = document.getElementById('selectedSeatsList');
            const count = Object.keys(appState.selectedSeats).length;
            document.getElementById('seatCount').textContent = `(${count})`;

            if (count === 0) {
                list.innerHTML = '<span class="text-muted small">No seats selected yet</span>';
                updatePassengerForms(); // ← Clear passenger forms
                calculateTotalFare();
                return;
            }

            // Clear previous content
            list.innerHTML = '';
            
            // Create compact badges for each selected seat
            Object.keys(appState.selectedSeats).sort((a, b) => a - b).forEach(seat => {
                const gender = appState.selectedSeats[seat];
                const genderIcon = gender === 'male' ? '👨' : '👩';
                
                const badge = document.createElement('span');
                badge.className = 'badge p-2';
                badge.style.cssText = 'font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;';
                
                if (gender === 'male') {
                    badge.classList.add('bg-primary');
                } else {
                    badge.classList.add('bg-danger');
                }
                
                badge.innerHTML = `<span>${genderIcon}</span> <strong>Seat ${seat}</strong>`;
                badge.title = `Seat ${seat} - ${gender === 'male' ? 'Male' : 'Female'}`;
                
                list.appendChild(badge);
            });
            
            updatePassengerForms(); // ← Update passenger forms based on seats
            calculateTotalFare();
        }

        // ========================================
        // UPDATE PASSENGER FORMS
        // ========================================
        function updatePassengerForms() {
            const container = document.getElementById('passengerInfoContainer');

            // Ensure at least 1 mandatory passenger exists
            if (!appState.passengerInfo['passenger_1']) {
                appState.passengerInfo['passenger_1'] = {
                    type: 'mandatory',
                    name: '',
                    age: '',
                    gender: '',
                    cnic: '',
                    phone: '',
                    email: ''
                };
            }

            // Generate forms
            let html = '';
            const passengers = Object.keys(appState.passengerInfo).sort((a, b) => {
                // Mandatory first, then extras
                if (appState.passengerInfo[a].type === 'mandatory') return -1;
                if (appState.passengerInfo[b].type === 'mandatory') return 1;
                return a.localeCompare(b);
            });

            passengers.forEach((passengerId, index) => {
                const info = appState.passengerInfo[passengerId];
                const isMandatory = info.type === 'mandatory';
                const passengerNumber = index + 1;

                html += `
                <div class="card mb-3 border-2 ${isMandatory ? '' : 'border-warning'}" style="border-color: ${isMandatory ? '#e9ecef' : '#ffc107'};">
                    <div class="card-header" style="background-color: ${isMandatory ? '#f8f9fa' : '#fff3cd'};">
                        <div class="d-flex justify-content-between align-items-center">
                            <h6 class="mb-0">
                                ${isMandatory ? '<i class="fas fa-user"></i> Passenger 1 <span class="badge bg-danger ms-2">Required</span>' : `<i class="fas fa-user-plus"></i> Passenger ${passengerNumber}`}
                            </h6>
                            ${!isMandatory ? `<button type="button" class="btn btn-sm btn-outline-danger" onclick="removeExtraPassenger('${passengerId}')">
                                        <i class="fas fa-trash"></i> Remove
                                    </button>` : ''}
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Name *</label>
                                <input type="text" class="form-control form-control-sm" 
                                    value="${info.name || ''}" 
                                    onchange="updatePassengerField('${passengerId}', 'name', this.value)"
                                    placeholder="Full Name" maxlength="100" required>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Gender *</label>
                                <select class="form-control form-control-sm" onchange="updatePassengerField('${passengerId}', 'gender', this.value)" required>
                                    <option value="">Select Gender</option>
                                    <option value="male" ${info.gender === 'male' ? 'selected' : ''}>👨 Male</option>
                                    <option value="female" ${info.gender === 'female' ? 'selected' : ''}>👩 Female</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Age</label>
                                <input type="number" class="form-control form-control-sm" 
                                    value="${info.age || ''}" 
                                    onchange="updatePassengerField('${passengerId}', 'age', this.value)"
                                    placeholder="Age" min="1" max="120">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">CNIC</label>
                                <input type="text" class="form-control form-control-sm" 
                                    value="${info.cnic || ''}" 
                                    onchange="updatePassengerField('${passengerId}', 'cnic', this.value)"
                                    placeholder="CNIC / ID Number" maxlength="20">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Phone</label>
                                <input type="tel" class="form-control form-control-sm" 
                                    value="${info.phone || ''}" 
                                    onchange="updatePassengerField('${passengerId}', 'phone', this.value)"
                                    placeholder="Phone Number" maxlength="20">
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label small fw-bold">Email</label>
                                <input type="email" class="form-control form-control-sm" 
                                    value="${info.email || ''}" 
                                    onchange="updatePassengerField('${passengerId}', 'email', this.value)"
                                    placeholder="Email Address" maxlength="100">
                            </div>
                        </div>
                    </div>
                </div>
            `;
            });

            container.innerHTML = html;
            document.getElementById('addPassengerBtn').style.display = 'inline-block';
        }

        // ========================================
        // UPDATE PASSENGER FIELD
        // ========================================
        function updatePassengerField(key, field, value) {
            if (appState.passengerInfo[key]) {
                appState.passengerInfo[key][field] = value;
            }
        }

        // ========================================
        // ADD EXTRA PASSENGER
        // ========================================
        function addExtraPassenger() {
            // Generate unique ID for new passenger
            const timestamp = Date.now();
            const passengerId = `passenger_extra_${timestamp}`;

            appState.passengerInfo[passengerId] = {
                type: 'extra',
                name: '',
                age: '',
                gender: '',
                cnic: '',
                phone: '',
                email: ''
            };

            updatePassengerForms();

            // Scroll to the newly added passenger form
            setTimeout(() => {
                const container = document.getElementById('passengerInfoContainer');
                container.scrollTop = container.scrollHeight;
            }, 100);
        }

        // ========================================
        // REMOVE EXTRA PASSENGER
        // ========================================
        function removeExtraPassenger(passengerId) {
            // Don't allow removing the mandatory passenger
            if (appState.passengerInfo[passengerId]?.type === 'mandatory') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cannot Remove',
                    text: 'At least one passenger information is required.',
                    confirmButtonColor: '#ffc107'
                });
                return;
            }

            delete appState.passengerInfo[passengerId];
            updatePassengerForms();
        }

        // ========================================
        // VALIDATE PASSENGER INFORMATION
        // ========================================
        function validatePassengerInfo() {
            const passengers = Object.keys(appState.passengerInfo);

            if (passengers.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Missing Passenger Information',
                    text: 'At least one passenger information is required.',
                    confirmButtonColor: '#ffc107'
                });
                return false;
            }

            // Validate all passengers
            for (let passengerId of passengers) {
                const info = appState.passengerInfo[passengerId];

                if (!info.name || info.name.trim() === '') {
                    const passengerNum = info.type === 'mandatory' ? '1' : 'extra';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Information',
                        text: `Passenger ${passengerNum}: Please enter passenger name`,
                        confirmButtonColor: '#ffc107'
                    });
                    return false;
                }

                if (!info.gender || info.gender === '') {
                    const passengerNum = info.type === 'mandatory' ? '1' : 'extra';
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Information',
                        text: `Passenger ${passengerNum}: Please select gender`,
                        confirmButtonColor: '#ffc107'
                    });
                    return false;
                }
            }

            return true;
        }

        // ========================================
        // CALCULATE TOTAL FARE (based on seat count)
        // ========================================
        function calculateTotalFare() {
            const seatCount = Object.keys(appState.selectedSeats).length;
            const baseFare = appState.baseFare || 0;
            const totalFare = baseFare * seatCount;

            document.getElementById('totalFare').value = totalFare.toFixed(2);
            calculateFinal();
        }

        // ========================================
        // CALCULATE FINAL AMOUNT
        // ========================================
        function calculateFinal() {
            const fare = parseFloat(document.getElementById('totalFare').value) || 0;
            const tax = parseFloat(document.getElementById('tax').value) || 0;
            const final = fare + tax;
            document.getElementById('finalAmount').textContent = final.toFixed(2);
            calculateReturn();
        }

        // ========================================
        // CALCULATE RETURN
        // ========================================
        function calculateReturn() {
            const final = parseFloat(document.getElementById('finalAmount').textContent);
            const received = parseFloat(document.getElementById('amountReceived').value) || 0;
            const returnDiv = document.getElementById('returnDiv');

            if (received > 0) {
                document.getElementById('returnAmount').textContent = Math.max(0, received - final).toFixed(2);
                returnDiv.style.display = 'block';
            } else {
                returnDiv.style.display = 'none';
            }
        }

        // ========================================
        // TOGGLE TRANSACTION ID FIELD
        // ========================================
        function toggleTransactionIdField() {
            const paymentMethod = document.getElementById('paymentMethod')?.value || 'cash';
            const transactionIdField = document.getElementById('transactionIdField');
            const transactionIdInput = document.getElementById('transactionId');
            const amountReceivedField = document.getElementById('amountReceivedField');
            const amountReceivedInput = document.getElementById('amountReceived');

            if (paymentMethod === 'cash') {
                // Cash payment: show Amount Received, hide Transaction ID
                if (transactionIdField) {
                    transactionIdField.style.display = 'none';
                    transactionIdInput.required = false;
                    transactionIdInput.value = '';
                }
                if (amountReceivedField) {
                    amountReceivedField.style.display = 'block';
                    amountReceivedInput.required = true;
                }
            } else {
                // Non-cash payment: show Transaction ID, hide Amount Received
                if (transactionIdField) {
                    transactionIdField.style.display = 'block';
                    transactionIdInput.required = true;
                }
                if (amountReceivedField) {
                    amountReceivedField.style.display = 'none';
                    amountReceivedInput.required = false;
                    amountReceivedInput.value = '0';
                }
            }
        }

        // ========================================
        // TOGGLE PAYMENT FIELDS
        // ========================================
        function togglePaymentFields() {
            const bookingType = document.getElementById('bookingType')?.value || 'counter';
            const isCounter = bookingType === 'counter';
            document.getElementById('paymentFields').style.display = isCounter ? 'block' : 'none';
            document.getElementById('paymentMethodSelect').style.display = isCounter ? 'block' : 'none';

            if (!isCounter) {
                // Clear transaction ID if switching to phone booking
                document.getElementById('transactionId').value = '';
            }

            toggleTransactionIdField();
        }

        // ========================================
        // CONFIRM BOOKING
        // ========================================
        function confirmBooking() {
            const selectedSeats = Object.keys(appState.selectedSeats);

            if (selectedSeats.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Seats Selected',
                    text: 'Please select at least one seat before confirming the booking.',
                    confirmButtonColor: '#ffc107'
                });
                return;
            }

            // Validate passenger information
            if (!validatePassengerInfo()) {
                return;
            }

            if (!appState.baseFare || appState.baseFare <= 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Fare Not Loaded',
                    text: 'Fare information is not available. Please select destination terminal first.',
                    confirmButtonColor: '#ffc107'
                });
                return;
            }

            const bookingType = document.getElementById('bookingType')?.value || 'counter';
            const isCounter = bookingType === 'counter';
            const final = parseFloat(document.getElementById('finalAmount').textContent);
            const received = parseFloat(document.getElementById('amountReceived').value) || 0;
            const paymentMethod = isCounter ?
                (document.getElementById('paymentMethod')?.value || 'cash') :
                'cash';

            if (isCounter && paymentMethod === 'cash' && received < final) {
                Swal.fire({
                    icon: 'error',
                    title: 'Insufficient Payment',
                    text: `Insufficient amount received from customer. Required: PKR ${final.toFixed(2)}, Received: PKR ${received.toFixed(2)}`,
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Create passengers array (without seat_number - passengers and seats are separate)
            const passengers = [];
            const passengerIds = Object.keys(appState.passengerInfo).sort((a, b) => {
                // Mandatory first, then extras
                if (appState.passengerInfo[a].type === 'mandatory') return -1;
                if (appState.passengerInfo[b].type === 'mandatory') return 1;
                return a.localeCompare(b);
            });

            // Create passengers array - just passenger information, no seat mapping
            passengerIds.forEach((passengerId) => {
                const info = appState.passengerInfo[passengerId];
                passengers.push({
                    name: info.name,
                    age: info.age || null,
                    gender: info.gender,
                    cnic: info.cnic || null,
                    phone: info.phone || null,
                    email: info.email || null
                });
            });

            // Create seats data with genders from appState.selectedSeats
            // appState.selectedSeats contains: {seatNumber: 'male'|'female'}
            const seatsData = selectedSeats.map(seatNum => ({
                seat_number: parseInt(seatNum),
                gender: appState.selectedSeats[seatNum] || 'male' // default to male if not set
            }));

            const totalFare = parseFloat(document.getElementById('totalFare').value);
            const tax = parseFloat(document.getElementById('tax').value) || 0;
            const farePerSeat = selectedSeats.length > 0 ? totalFare / selectedSeats.length : 0;

            document.getElementById('confirmBtn').disabled = true;

            $.ajax({
                url: "{{ route('admin.bookings.store') }}",
                type: 'POST',
                data: {
                    trip_id: appState.tripData.trip.id,
                    from_terminal_id: document.getElementById('fromTerminal').value,
                    to_terminal_id: document.getElementById('toTerminal').value,
                    seat_numbers: selectedSeats.map(Number),
                    seats_data: JSON.stringify(seatsData), // Send seats with genders separately
                    passengers: JSON.stringify(passengers), // Passengers without seat_number
                    channel: isCounter ? 'counter' : 'phone',
                    payment_method: paymentMethod,
                    amount_received: paymentMethod === 'cash' && isCounter ? received : null,
                    fare_per_seat: farePerSeat,
                    total_fare: totalFare,
                    discount_amount: 0,
                    tax_amount: tax,
                    final_amount: final,
                    notes: document.getElementById('notes').value,
                    transaction_id: paymentMethod !== 'cash' && isCounter ? document.getElementById('transactionId')
                        .value : null,
                    _token: document.querySelector('meta[name="csrf-token"]').content
                },
                success: function(response) {
                    const booking = response.booking;
                    document.getElementById('bookingNumber').textContent = booking.booking_number;
                    document.getElementById('bookedSeats').textContent = booking.seats.join(', ');
                    document.getElementById('bookingStatus').textContent = booking.status === 'hold' ?
                        'On Hold' : 'Confirmed';
                    document.getElementById('confirmedFare').textContent = parseFloat(booking.total_fare)
                        .toFixed(2);
                    document.getElementById('confirmedDiscount').textContent = '0.00';
                    document.getElementById('confirmedTax').textContent = parseFloat(booking.tax_amount)
                        .toFixed(2);
                    document.getElementById('confirmedFinal').textContent = parseFloat(booking.final_amount)
                        .toFixed(2);
                    document.getElementById('paymentMethodDisplay').textContent = booking.payment_method ||
                        'N/A';

                    // Unlock seats after successful booking (they will be confirmed via WebSocket)
                    const bookedSeats = booking.seats.map(Number);
                    unlockSeats(bookedSeats);

                    new bootstrap.Modal(document.getElementById('successModal')).show();
                },
                error: function(error) {
                    const message = error.responseJSON?.error || error.responseJSON?.message ||
                        'Unable to complete the booking. Please check all information and try again.';
                    Swal.fire({
                        icon: 'error',
                        title: 'Booking Failed',
                        text: message,
                        confirmButtonColor: '#d33'
                    });
                },
                complete: function() {
                    document.getElementById('confirmBtn').disabled = false;
                }
            });
        }

        // ========================================
        // RESET FORM
        // ========================================
        function resetForm() {
            // Unlock all seats before resetting
            const lockedSeats = Object.keys(appState.lockedSeats).filter(
                seat => appState.lockedSeats[seat] === appState.userId
            );
            if (lockedSeats.length > 0 && appState.tripLoaded) {
                unlockSeats(lockedSeats.map(Number));
            }

            // Leave WebSocket channel
            if (appState.echoChannel && appState.tripData?.trip?.id) {
                window.Echo.leave(`trip.${appState.tripData.trip.id}`);
                appState.echoChannel = null;
            }

            appState.selectedSeats = {};
            appState.passengerInfo = {}; // ← Clear passenger info
            appState.lockedSeats = {};
            updatePassengerForms(); // Reinitialize with 1 mandatory passenger
            appState.tripLoaded = false;
            appState.fareData = null;
            appState.baseFare = 0;
            document.getElementById('tripContent').style.display = 'none';
            // Hide assign bus card
            const assignBusCard = document.getElementById('assignBusCard');
            if (assignBusCard) {
                assignBusCard.style.display = 'none';
            }
            document.getElementById('baseFare').value = '';
            document.getElementById('discountInfo').value = '';
            document.getElementById('totalFare').value = '';
            document.getElementById('tax').value = '0';
            document.getElementById('amountReceived').value = '0';
            document.getElementById('notes').value = '';
            document.getElementById('finalAmount').textContent = '0.00';
            document.getElementById('departureTime').value = '';
            document.getElementById('arrivalTime').value = '';
            document.getElementById('arrivalTime').disabled = true;
            updateSeatsList();
        }

        // ========================================
        // SETUP WEBSOCKET
        // ========================================
        function setupWebSocket() {
            if (!window.Echo) return;

            window.Echo.connector.options.auth.headers['X-CSRF-TOKEN'] = document.querySelector('meta[name="csrf-token"]')
                .content;
        }

        // ========================================
        // SETUP TRIP WEBSOCKET LISTENERS
        // ========================================
        function setupTripWebSocket(tripId) {
            console.log(' setting up trip web socket for tripId', tripId);
            if (!window.Echo) return;

            console.log('appState.tripData?.trip?.id', appState.tripData?.trip?.id);
            // Leave previous channel if exists
            if (appState.echoChannel) {
                window.Echo.leave(`trip.${appState.tripData?.trip?.id}`);
            }

            // Join the trip channel
            appState.echoChannel = window.Echo.channel(`trip.${tripId}`);
            console.log('channel subscribed to', appState.echoChannel);

            // Listen for seat locked events
            appState.echoChannel.listen('.seat-locked', (event) => {
                console.log(' seat locked event', event);
                event.seat_numbers.forEach(seatNumber => {
                    appState.lockedSeats[seatNumber] = event.user_id;
                });
                renderSeatMap();
            });

            // Listen for seat unlocked events
            appState.echoChannel.listen('.seat-unlocked', (event) => {
                event.seat_numbers.forEach(seatNumber => {
                    delete appState.lockedSeats[seatNumber];
                });
                renderSeatMap();
            });

            // Listen for seat confirmed events (seats become booked)
            appState.echoChannel.listen('.seat-confirmed', (event) => {
                event.seat_numbers.forEach(seatNumber => {
                    delete appState.lockedSeats[seatNumber];
                    if (appState.seatMap[seatNumber]) {
                        appState.seatMap[seatNumber].status = 'booked';
                    }
                });
                renderSeatMap();
            });
        }

        // ========================================
        // LOCK SEATS
        // ========================================
        function lockSeats(seatNumbers, callback) {
            if (!appState.tripData || !appState.tripLoaded) {
                if (callback) callback(false);
                return;
            }

            const tripId = appState.tripData.trip.id;
            const fromStopId = appState.tripData.from_stop.id;
            const toStopId = appState.tripData.to_stop.id;

            $.ajax({
                url: "{{ route('admin.bookings.lock-seats') }}",
                type: 'POST',
                data: {
                    trip_id: tripId,
                    seat_numbers: seatNumbers,
                    from_stop_id: fromStopId,
                    to_stop_id: toStopId,
                    _token: document.querySelector('meta[name="csrf-token"]').content
                },
                success: function(response) {
                    // Mark seats as locked by current user
                    seatNumbers.forEach(seatNumber => {
                        appState.lockedSeats[seatNumber] = appState.userId;
                    });
                    renderSeatMap();
                    if (callback) callback(true);
                },
                error: function(error) {
                    const message = error.responseJSON?.error || error.responseJSON?.errors?.seats?.[0] ||
                        'Failed to lock seat';
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Lock Seat',
                        text: message,
                        confirmButtonColor: '#d33'
                    });
                    if (callback) callback(false);
                }
            });
        }

        // ========================================
        // UNLOCK SEATS
        // ========================================
        function unlockSeats(seatNumbers) {
            if (!appState.tripData || !appState.tripLoaded) {
                return;
            }

            const tripId = appState.tripData.trip.id;

            $.ajax({
                url: "{{ route('admin.bookings.unlock-seats') }}",
                type: 'POST',
                data: {
                    trip_id: tripId,
                    seat_numbers: seatNumbers,
                    _token: document.querySelector('meta[name="csrf-token"]').content
                },
                success: function(response) {
                    // Remove from locked seats
                    seatNumbers.forEach(seatNumber => {
                        delete appState.lockedSeats[seatNumber];
                    });
                    renderSeatMap();
                },
                error: function(error) {
                    // Silently fail for unlock - not critical
                    console.error('Failed to unlock seats:', error);
                    // Still remove from local state
                    seatNumbers.forEach(seatNumber => {
                        delete appState.lockedSeats[seatNumber];
                    });
                    renderSeatMap();
                }
            });
        }

        // ========================================
        // LOAD TRIP PASSENGERS
        // ========================================
        function loadTripPassengers(tripId) {
            $.ajax({
                url: "{{ route('admin.bookings.trip-passengers', ['tripId' => ':tripId']) }}".replace(':tripId',
                    tripId),
                type: 'GET',
                success: function(response) {
                    const passengersList = document.getElementById('tripPassengersList');
                    passengersList.innerHTML = ''; // Clear previous content

                    if (response.length === 0) {
                        passengersList.innerHTML =
                            '<p class="text-muted text-center py-4"><i class="fas fa-inbox"></i><br>No passengers booked for this trip yet.</p>';
                        return;
                    }

                    const table = document.createElement('table');
                    table.className = 'table table-striped table-bordered table-hover table-sm';
                    table.style.width = '100%';
                    table.style.fontSize = '0.88rem';

                    const headerRow = document.createElement('tr');
                    headerRow.className = '';
                    headerRow.innerHTML = `
                    <th style="width: 8%;">Seats</th>
                    <th style="width: 15%;">Passenger</th>
                    <th style="width: 10%;">Route</th>
                    <th style="width: 10%;">From</th>
                    <th style="width: 10%;">To</th>
                    <th style="width: 7%;">Gender</th>
                    <th style="width: 8%;">Booking Details</th>
                    <th style="width: 8%;">Status</th>
                    <th style="width: 8%;">Action</th>
                `;
                    table.appendChild(headerRow);

                    response.forEach(passenger => {
                        const row = document.createElement('tr');
                        const genderIcon = passenger.gender === 'male' ? '👨 Male' : passenger.gender ===
                            'female' ? '👩 Female' : 'Unknown';
                        const statusBadgeClass = passenger.status === 'confirmed' ? 'bg-success' :
                            passenger.status === 'hold' ? 'bg-warning' :
                            passenger.status === 'checked_in' ? 'bg-info' :
                            passenger.status === 'boarded' ? 'bg-primary' :
                            passenger.status === 'cancelled' ? 'bg-danger' : 'bg-secondary';
                        
                        const channelLabel = passenger.channel === 'counter' ? '🏪 Counter' :
                            passenger.channel === 'phone' ? '📞 Phone' :
                            passenger.channel === 'online' ? '🌐 Online' : passenger.channel || 'N/A';
                        
                        const paymentMethodLabel = passenger.payment_method === 'cash' ? '💰 Cash' :
                            passenger.payment_method === 'card' ? '💳 Card' :
                            passenger.payment_method === 'mobile_wallet' ? '📱 Mobile' :
                            passenger.payment_method === 'bank_transfer' ? '🏦 Transfer' : passenger.payment_method || 'N/A';

                        row.innerHTML = `
                        <td class="text-center">
                            <span class="badge bg-info">${passenger.seats_display || 'N/A'}</span>
                        </td>
                        <td>
                            <div class="fw-bold small">${passenger.name || 'N/A'}</div>
                            <small class="text-muted d-block">${passenger.phone || 'No phone'}</small>
                            ${passenger.email ? `<small class="text-muted d-block">${passenger.email}</small>` : ''}
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary small">${passenger.from_code} → ${passenger.to_code}</span>
                        </td>
                        <td>
                            <small><strong>${passenger.from_stop}</strong></small>
                        </td>
                        <td>
                            <small><strong>${passenger.to_stop}</strong></small>
                        </td>
                        <td class="text-center">
                            <small>${genderIcon}</small>
                        </td>
                        <td class="text-center">
                            <div class="mb-1">
                                <small class="badge bg-light text-dark">#${passenger.booking_number}</small>
                            </div>
                            <small class="text-muted d-block">${channelLabel}</small>
                            <small class="text-muted d-block">${paymentMethodLabel}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge ${statusBadgeClass} small">${passenger.status || 'N/A'}</span>
                        </td>
                        <td class="text-center">
                            <a href="/admin/bookings/${passenger.booking_id}/edit" target="_blank" class="btn btn-primary btn-sm">
                                <i class="fas fa-eye"></i> View
                            </a>
                        </td>
                    `;
                        table.appendChild(row);
                    });

                    passengersList.appendChild(table);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Load Passengers',
                        text: 'Unable to fetch passenger list for this trip. Please refresh and try again.',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        }


        // ========================================
        // RENDER BUS & DRIVER SECTION
        // ========================================
        function renderBusDriverSection(trip) {
            const busDriverSection = document.getElementById('busDriverSection');
            busDriverSection.innerHTML = ''; // Clear previous content

            // Show/hide assign bus card in right column based on bus assignment status
            const assignBusCard = document.getElementById('assignBusCard');
            const assignBusBtn = document.getElementById('assignBusBtnCard');
            
            // Check if bus is assigned from database
            const isBusAssigned = trip.bus_id && trip.bus_id !== null && trip.bus_id !== undefined;
            
            if (assignBusCard && assignBusBtn) {
                if (isBusAssigned) {
                    // Hide the assign bus card if bus is already assigned
                    assignBusCard.style.display = 'none';
                } else {
                    // Show the assign bus card if bus is not assigned
                    assignBusCard.style.display = 'block';
                    assignBusBtn.textContent = '🚌 Assign Bus & Driver';
                }
            }

            if (!isBusAssigned) {
                busDriverSection.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="me-3" style="font-size: 2.5rem; opacity: 0.7;">
                            <i class="fas fa-bus"></i>
                        </div>
                        <div>
                            <small class="d-block opacity-75" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Bus & Driver</small>
                            <p class="mb-0 fw-bold">Not Assigned</p>
                            <small class="opacity-75" style="font-size: 0.7rem;">Use button in right column</small>
                        </div>
                    </div>
                `;
                return;
            }

            // Bus assigned - show details in trip details card (compact format)
            busDriverSection.innerHTML = `
                <div class="d-flex align-items-center">
                    <div class="me-3" style="font-size: 2.5rem;">
                        <i class="fas fa-bus"></i>
                    </div>
                    <div>
                        <small class="d-block opacity-75" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Bus & Driver</small>
                        <h6 class="mb-1 fw-bold">${trip.bus?.name || 'N/A'}</h6>
                        <small class="opacity-75" style="font-size: 0.7rem;">
                            <i class="fas fa-user-tie"></i> ${trip.driver_name || 'N/A'} | 
                            <i class="fas fa-phone"></i> ${trip.driver_phone || 'N/A'}
                        </small>
                    </div>
                </div>
            `;
        }

        // ========================================
        // OPEN ASSIGN BUS MODAL
        // ========================================
        function openAssignBusModal(tripId) {
            // Fetch list of available buses
            $.ajax({
                url: "{{ route('admin.bookings.list-buses') }}",
                type: 'GET',
                success: function(response) {
                    if (response.success) {
                        const buses = response.buses;
                        let busesHtml = '<option value="">-- Select a Bus --</option>';
                        buses.forEach(bus => {
                            busesHtml +=
                                `<option value="${bus.id}">${bus.name} (${bus.registration_number})</option>`;
                        });

                        const modalBody = document.getElementById('assignBusModalBody');
                        modalBody.innerHTML = `
                        <form id="assignBusForm">
                            <div class="mb-3">
                                <label class="form-label fw-bold">
                                    <i class="fas fa-bus"></i> Select Bus
                                </label>
                                <select id="busSelect" class="form-select form-select-lg" required>
                                    ${busesHtml}
                                </select>
                                <small class="text-muted d-block mt-2">Choose a bus to assign to this trip</small>
                            </div>

                            <hr class="my-4">

                            <h6 class="fw-bold mb-3"><i class="fas fa-user-tie"></i> Driver Information</h6>

                            <div class="mb-3">
                                <label class="form-label">Driver Name <span class="text-danger">*</span></label>
                                <input type="text" id="driverName" class="form-control" placeholder="Enter driver name" required>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Driver Phone <span class="text-danger">*</span></label>
                                    <input type="tel" id="driverPhone" class="form-control" placeholder="03001234567" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Driver CNIC <span class="text-danger">*</span></label>
                                    <input type="text" id="driverCnic" class="form-control" placeholder="12345-6789012-3" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Driver License <span class="text-danger">*</span></label>
                                <input type="text" id="driverLicense" class="form-control" placeholder="License number" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Driver Address</label>
                                <textarea id="driverAddress" class="form-control" rows="2" placeholder="Enter driver address"></textarea>
                            </div>

                            <input type="hidden" id="tripIdInput" value="${tripId}">
                        </form>
                    `;

                        const modal = new bootstrap.Modal(document.getElementById('assignBusModal'));
                        modal.show();

                        // Handle confirm button
                        document.getElementById('confirmAssignBusBtn').onclick = () => {
                            const busId = document.getElementById('busSelect').value;
                            const driverName = document.getElementById('driverName').value;
                            const driverPhone = document.getElementById('driverPhone').value;
                            const driverCnic = document.getElementById('driverCnic').value;
                            const driverLicense = document.getElementById('driverLicense').value;
                            const driverAddress = document.getElementById('driverAddress').value;

                            if (!busId || !driverName || !driverPhone || !driverCnic || !driverLicense) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Missing Information',
                                    text: 'Please fill all required fields: Bus, Driver Name, Phone, CNIC, and License.',
                                    confirmButtonColor: '#ffc107'
                                });
                                return;
                            }

                            // Submit to backend
                            $.ajax({
                                url: "{{ route('admin.bookings.assign-bus-driver', ['tripId' => ':tripId']) }}"
                                    .replace(':tripId', tripId),
                                type: 'POST',
                                data: {
                                    bus_id: busId,
                                    driver_name: driverName,
                                    driver_phone: driverPhone,
                                    driver_cnic: driverCnic,
                                    driver_license: driverLicense,
                                    driver_address: driverAddress,
                                    _token: document.querySelector('meta[name="csrf-token"]')
                                        .content
                                },
                                success: function(response) {
                                    if (response.success) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Success!',
                                            text: 'Bus and Driver assigned successfully!',
                                            confirmButtonColor: '#28a745',
                                            timer: 2000,
                                            timerProgressBar: true
                                        });
                                        modal.hide();
                                        // Reload trip data from database to get updated bus assignment status
                                        if (appState.tripData && appState.tripData.trip.id) {
                                            // Get current form values to reload trip
                                            const fromTerminalId = document.getElementById('fromTerminal').value;
                                            const toTerminalId = document.getElementById('toTerminal').value;
                                            const departureTimeId = document.getElementById('departureTime').value;
                                            const date = document.getElementById('travelDate').value;
                                            
                                            if (fromTerminalId && toTerminalId && departureTimeId && date) {
                                                // Reload trip to get fresh data from database
                                                $.ajax({
                                                    url: "{{ route('admin.bookings.load-trip') }}",
                                                    type: 'POST',
                                                    data: {
                                                        from_terminal_id: fromTerminalId,
                                                        to_terminal_id: toTerminalId,
                                                        timetable_id: departureTimeId,
                                                        date: date,
                                                        _token: document.querySelector('meta[name="csrf-token"]').content
                                                    },
                                                    success: function(reloadResponse) {
                                                        // Update app state with fresh data
                                                        appState.tripData = reloadResponse;
                                                        appState.seatMap = reloadResponse.seat_map;
                                                        
                                                        // Re-render bus driver section with updated data
                                                        renderBusDriverSection(reloadResponse.trip);
                                                    },
                                                    error: function() {
                                                        // If reload fails, just try to refresh manually
                                                        loadTrip();
                                                    }
                                                });
                                            }
                                        }
                                    } else {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Assignment Failed',
                                            text: response.error || response.message ||
                                                'Unable to assign bus and driver. Please check all information and try again.',
                                            confirmButtonColor: '#d33'
                                        });
                                    }
                                },
                                error: function(error) {
                                    console.error('Error:', error);
                                    const errorMessage = error.responseJSON?.error || error
                                        .responseJSON?.message ||
                                        'Unable to assign bus and driver. Please check your connection and try again.';
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Failed to Assign Bus & Driver',
                                        text: errorMessage,
                                        confirmButtonColor: '#d33'
                                    });
                                }
                            });
                        };
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed to Load Buses',
                            text: 'Unable to fetch available buses. Please try again.',
                            confirmButtonColor: '#d33'
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to Fetch Buses',
                        text: 'Unable to load bus list. Please check your connection and try again.',
                        confirmButtonColor: '#d33'
                    });
                }
            });
        }

        // ========================================
        // OPEN ASSIGN BUS MODAL FROM HEADER
        // ========================================
        function openAssignBusModalFromHeader() {
            const tripId = appState.tripData ? appState.tripData.trip.id : null;
            if (tripId) {
                openAssignBusModal(tripId);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'No Trip Loaded',
                    text: 'Please load a trip first before assigning a bus and driver.',
                    confirmButtonColor: '#ffc107'
                });
            }
        }
    </script>
@endsection
