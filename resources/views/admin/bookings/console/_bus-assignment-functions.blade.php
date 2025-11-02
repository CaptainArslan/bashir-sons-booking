<script>
    {{-- Bus Assignment and Trip Passenger Functions --}}

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

                // ✅ If no passengers found
                if (response.length === 0) {
                    passengersList.innerHTML = `
                        <div class="text-center py-5">
                            <div class="mb-3" style="font-size: 3rem; opacity: 0.3;">
                                <i class="fas fa-users-slash"></i>
                            </div>
                            <p class="text-muted mb-0">No passengers booked for this trip yet.</p>
                            <small class="text-muted">Passengers will appear here once bookings are made</small>
                        </div>
                    `;
                    return;
                }

                // ✅ Build all HTML at once
                let html = ``;

                // ✅ Add summary header
                const totalSeats = response.reduce((sum, p) => {
                    const seatCount = p.seats_display ? p.seats_display.split(',').length : 0;
                    return sum + seatCount;
                }, 0);

                html += `
                        <div class="alert alert-info mb-2 p-2">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <i class="fas fa-users"></i> <strong>Total Passengers:</strong>
                                    <span class="badge bg-primary">${response.length}</span>
                                </div>
                                <div>
                                    <strong>Total Seats Booked:</strong>
                                    <span class="badge bg-info">${totalSeats}</span>
                                </div>
                            </div>
                        </div>
                        <div class="row g-2" style="max-height: calc(100vh - 250px); overflow-y: auto; padding-right: 5px;">
                    `;

                // ✅ Render each passenger as a card
                response.forEach(passenger => {
                    const gender = passenger.gender ? String(passenger.gender).toLowerCase() : null;
                    const genderIcon = gender === 'male' ? '👨' : gender === 'female' ? '👩' : '❓';
                    const genderLabel = gender === 'male' ? 'Male' : gender === 'female' ?
                        'Female' : 'Unknown';
                    const genderBadge = gender === 'male' ? 'bg-primary' : gender === 'female' ?
                        'bg-danger' : 'bg-secondary';
                    const leftBorder = gender === 'male' ? '#3B82F6' : gender === 'female' ?
                        '#EC4899' : '#94a3b8';

                    const statusBadge =
                        passenger.status === 'confirmed' ? 'bg-success' :
                        passenger.status === 'hold' ? 'bg-warning' :
                        passenger.status === 'checked_in' ? 'bg-info' :
                        passenger.status === 'boarded' ? 'bg-primary' :
                        passenger.status === 'cancelled' ? 'bg-danger' : 'bg-secondary';

                    const channelBadge =
                        passenger.channel === 'counter' ? 'bg-info' :
                        passenger.channel === 'phone' ? 'bg-warning' :
                        passenger.channel === 'online' ? 'bg-success' : 'bg-secondary';

                    const channelLabel =
                        passenger.channel === 'counter' ? '🏪 Counter' :
                        passenger.channel === 'phone' ? '📞 Phone' :
                        passenger.channel === 'online' ? '🌐 Online' : passenger.channel || 'N/A';

                    const paymentBadge =
                        passenger.payment_method === 'cash' ? 'bg-success' :
                        passenger.payment_method === 'card' ? 'bg-info' :
                        passenger.payment_method === 'mobile_wallet' ? 'bg-primary' :
                        passenger.payment_method === 'bank_transfer' ? 'bg-secondary' :
                        'bg-secondary';

                    const paymentLabel =
                        passenger.payment_method === 'cash' ? '💰 Cash' :
                        passenger.payment_method === 'card' ? '💳 Card' :
                        passenger.payment_method === 'mobile_wallet' ? '📱 Mobile Wallet' :
                        passenger.payment_method === 'bank_transfer' ? '🏦 Bank Transfer' :
                        passenger.payment_method || 'N/A';

                    html += `
                        <div class="col-12">
                            <div class="card shadow-sm mb-2 border-start border-4"
                                style="border-left-color:${leftBorder}; transition: transform 0.2s, box-shadow 0.2s;"
                                onmouseover="this.style.transform='translateX(2px)'; this.style.boxShadow='0 4px 8px rgba(0,0,0,0.15)'"
                                onmouseout="this.style.transform='translateX(0)'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.1)'"
                            >
                                <div class="card-body p-3">
                                    <div class="row g-3 align-items-start">

                                        <!-- Passenger Info -->
                                        <div class="col-12 col-lg-4">
                                            <div class="d-flex align-items-center mb-2">
                                                <div class="me-2" style="font-size:1.5rem;">${genderIcon}</div>
                                                <div class="flex-grow-1 min-w-0">
                                                    <h6 class="mb-0 fw-bold text-dark text-truncate">${passenger.name || 'N/A'}</h6>
                                                    <small class="text-muted d-block">
                                                        ${passenger.phone ? `<i class="fas fa-phone-alt"></i> ${passenger.phone}` : 'No phone'}
                                                    </small>
                                                    ${passenger.email ? `<small class="text-muted d-block"><i class="fas fa-envelope"></i> ${passenger.email}</small>` : ''}
                                                </div>
                                            </div>

                                            <div class="d-flex gap-1 flex-wrap">
                                                <span class="badge ${genderBadge} small">${genderLabel}</span>
                                                <span class="badge bg-info small"><i class="fas fa-chair"></i> ${passenger.seats_display}</span>
                                            </div>
                                        </div>

                                        <!-- Route Info -->
                                        <div class="col-12 col-lg-3">
                                            <small class="text-muted"><i class="fas fa-route"></i> Route</small>
                                            <div><strong>${passenger.from_code} → ${passenger.to_code}</strong></div>

                                            <small class="text-muted d-block mt-1">From:</small>
                                            <div class="fw-semibold">${passenger.from_stop}</div>

                                            <small class="text-muted d-block mt-1">To:</small>
                                            <div class="fw-semibold">${passenger.to_stop}</div>
                                        </div>

                                        <!-- Booking + Status -->
                                        <div class="col-12 col-lg-3">
                                            <small class="text-muted d-block"><i class="fas fa-ticket-alt"></i> Booking #</small>
                                            <span class="badge bg-dark">${passenger.booking_number}</span>

                                            <div class="d-flex gap-1 flex-wrap mt-2">
                                                <span class="badge ${channelBadge} small">${channelLabel}</span>
                                                <span class="badge ${paymentBadge} small">${paymentLabel}</span>
                                            </div>

                                            <div class="mt-2">
                                                <span class="badge ${statusBadge} small">
                                                    <i class="fas fa-circle" style="font-size:0.5rem;"></i>
                                                    ${passenger.status ? passenger.status.charAt(0).toUpperCase() + passenger.status.slice(1) : 'N/A'}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Action -->
                                        <div class="col-12 col-lg-2">
                                            <a href="/admin/bookings/${passenger.booking_id}/edit"
                                                target="_blank"
                                                class="btn btn-primary btn-sm fw-bold w-100">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += `</div>`; // close row

                // ✅ Render final HTML
                passengersList.innerHTML = html;
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
    // RENDER BUS & DRIVER SECTION (Updated for Segment Assignments)
    // ========================================
    function renderBusDriverSection(trip) {
        const busDriverSection = document.getElementById('busDriverSection');
        busDriverSection.innerHTML = ''; // Clear previous content

        // Show/hide assign bus card in right column based on bus assignment status
        const assignBusCard = document.getElementById('assignBusCard');
        const assignBusBtn = document.getElementById('assignBusBtnCard');

        // Check for segment-based bus assignments (new system)
        const busAssignments = appState.tripData?.bus_assignments || [];
        const hasSegmentAssignments = busAssignments.length > 0;

        // Get the currently selected segment from the booking form
        const currentFromStopId = appState.tripData?.from_stop?.id;
        const currentToStopId = appState.tripData?.to_stop?.id;

        // Check if an assignment already exists for the current segment
        let assignmentExistsForSegment = false;
        if (currentFromStopId && currentToStopId && hasSegmentAssignments) {
            assignmentExistsForSegment = busAssignments.some(assignment => {
                return assignment.from_trip_stop_id == currentFromStopId &&
                    assignment.to_trip_stop_id == currentToStopId;
            });
        }

        // Check if legacy bus is assigned (fallback)
        const isLegacyBusAssigned = trip.bus_id && trip.bus_id !== null && trip.bus_id !== undefined;

        if (assignBusCard && assignBusBtn) {
            // Hide the assign bus button if assignment already exists for this segment
            if (assignmentExistsForSegment) {
                assignBusCard.style.display = 'none';
            } else {
                // Show the assign bus card - open modal
                assignBusCard.style.display = 'block';
                assignBusBtn.textContent = '🚌 Assign Bus & Driver';
                assignBusBtn.onclick = function() {
                    if (trip && trip.id) {
                        openAssignBusModal(trip.id);
                    } else {
                        Swal.fire({
                            icon: 'warning',
                            title: 'No Trip Selected',
                            text: 'Please load a trip first.',
                            confirmButtonColor: '#ffc107'
                        });
                    }
                };
            }
        }

        // Check if current segment has an assignment
        let currentSegmentAssignment = null;
        if (currentFromStopId && currentToStopId && hasSegmentAssignments) {
            currentSegmentAssignment = busAssignments.find(assignment => {
                return assignment.from_trip_stop_id == currentFromStopId &&
                    assignment.to_trip_stop_id == currentToStopId;
            });
        }

        if (currentSegmentAssignment) {
            // Show current segment assignment details
            busDriverSection.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="me-3" style="font-size: 2.5rem;">
                    <i class="fas fa-bus"></i>
                </div>
                <div style="flex: 1;">
                    <small class="d-block opacity-75" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Bus & Driver</small>
                    <h6 class="mb-1 fw-bold">${currentSegmentAssignment.bus?.name || 'No Bus Assigned'}</h6>
                    <div style="font-size: 0.75rem; opacity: 0.9;">
                        <div><i class="fas fa-route"></i> ${currentSegmentAssignment.segment_label}</div>
                        ${currentSegmentAssignment.driver_name ? `<div><i class="fas fa-user-tie"></i> Driver: ${currentSegmentAssignment.driver_name}</div>` : ''}
                        ${currentSegmentAssignment.driver_phone ? `<div><i class="fas fa-phone"></i> ${currentSegmentAssignment.driver_phone}</div>` : ''}
                        ${currentSegmentAssignment.host_name ? `<div><i class="fas fa-user"></i> Host: ${currentSegmentAssignment.host_name}</div>` : ''}
                    </div>
                </div>
            </div>
        `;
            return;
        }

        if (hasSegmentAssignments) {
            // Show segment-based assignments summary (but not for current segment)
            let html = `<div>
            <small class="d-block opacity-75" style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 0.5rem;">
                <i class="fas fa-route"></i> Other Segment Assignments (${busAssignments.length})
            </small>`;

            // Show assignments that are NOT for the current segment
            const otherAssignments = busAssignments.filter(assignment => {
                return !(assignment.from_trip_stop_id == currentFromStopId &&
                    assignment.to_trip_stop_id == currentToStopId);
            }).slice(0, 2);

            otherAssignments.forEach((assignment) => {
                html += `
                <div class="mb-1" style="font-size: 0.75rem; padding: 0.25rem 0.5rem; background: rgba(255,255,255,0.2); border-radius: 4px;">
                    <strong>${assignment.segment_label}</strong> - ${assignment.bus?.name || 'No Bus'} 
                    ${assignment.driver_name ? `| Driver: ${assignment.driver_name}` : ''}
                </div>
            `;
            });

            if (otherAssignments.length === 0) {
                html += `<small class="text-white-50">No other segment assignments</small>`;
            } else if (otherAssignments.length < busAssignments.length - (currentSegmentAssignment ? 1 : 0)) {
                const remaining = busAssignments.length - (currentSegmentAssignment ? 1 : 0) - otherAssignments.length;
                html += `<small class="text-white-50">+${remaining} more segment(s)...</small>`;
            }

            html += `</div>`;
            busDriverSection.innerHTML = html;
            return;
        }

        // Fallback to legacy single bus assignment display
        if (!isLegacyBusAssigned) {
            busDriverSection.innerHTML = `
            <div class="d-flex align-items-center">
                <div class="me-3" style="font-size: 2.5rem; opacity: 0.7;">
                    <i class="fas fa-bus"></i>
                </div>
                <div>
                    <small class="d-block opacity-75" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px;">Bus & Driver</small>
                    <p class="mb-0 fw-bold">Not Assigned</p>
                    <small class="opacity-75" style="font-size: 0.7rem;">Manage via Bus Assignments</small>
                </div>
            </div>
        `;
            return;
        }

        // Legacy bus assigned - show details
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
    // ADD EXPENSE ROW
    // ========================================
    let expenseRowCounter = 0;

    function addExpenseRow() {
        const container = document.getElementById('expensesContainer');
        if (!container || !window.expenseTypes) return;

        const rowId = `expense_row_${expenseRowCounter++}`;
        const expenseTypes = window.expenseTypes;

        // Get current segment from hidden fields (auto-selected, read-only)
        // First try to get from hidden select fields (they're hidden but still have values)
        const fromTripStopSelect = document.getElementById('fromTripStopSelect');
        const toTripStopSelect = document.getElementById('toTripStopSelect');
        let fromTerminalId = '';
        let toTerminalId = '';
        let fromTerminalName = 'N/A';
        let toTerminalName = 'N/A';

        if (fromTripStopSelect && toTripStopSelect && window.tripStops) {
            const selectedFromStop = window.tripStops.find(s => s.id == fromTripStopSelect.value);
            const selectedToStop = window.tripStops.find(s => s.id == toTripStopSelect.value);

            if (selectedFromStop && selectedToStop) {
                fromTerminalId = selectedFromStop.terminal_id;
                toTerminalId = selectedToStop.terminal_id;
                fromTerminalName = selectedFromStop.terminal_name;
                toTerminalName = selectedToStop.terminal_name;
            }
        }

        // Fallback to trip data from appState if modal fields not available
        if (!fromTerminalId || !toTerminalId) {
            const tripData = appState.tripData;
            fromTerminalId = tripData?.from_stop?.terminal_id || '';
            toTerminalId = tripData?.to_stop?.terminal_id || '';
            fromTerminalName = tripData?.from_stop?.terminal?.name || 'N/A';
            toTerminalName = tripData?.to_stop?.terminal?.name || 'N/A';
        }

        let expenseTypesHtml = '<option value="">-- Select Type --</option>';
        expenseTypes.forEach(type => {
            expenseTypesHtml += `<option value="${type.value}">${type.label}</option>`;
        });

        const row = document.createElement('div');
        row.className = 'card mb-2 expense-row';
        row.id = rowId;
        row.innerHTML = `
        <div class="card-body p-3">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h6 class="mb-0 text-muted">Expense #${expenseRowCounter}</h6>
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeExpenseRow('${rowId}')">
                    <i class="fas fa-trash"></i> Remove
                </button>
            </div>
            <div class="row g-2">
                <div class="col-md-4">
                    <label class="form-label small">Expense Type <span class="text-danger">*</span></label>
                    <select class="form-select form-select-sm expense-type" required>
                        ${expenseTypesHtml}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Amount (PKR) <span class="text-danger">*</span></label>
                    <input type="number" class="form-control form-control-sm expense-amount" 
                        min="0" step="0.01" placeholder="0.00" required>
                </div>
                <div class="col-md-5">
                    <label class="form-label small">Segment (Auto-selected)</label>
                    <input type="text" class="form-control form-control-sm" 
                        value="${fromTerminalName} → ${toTerminalName}" readonly 
                        style="background-color: #f8f9fa; cursor: not-allowed;">
                    <input type="hidden" class="expense-from-terminal" value="${fromTerminalId}">
                    <input type="hidden" class="expense-to-terminal" value="${toTerminalId}">
                </div>
            </div>
            <div class="row mt-2">
                <div class="col-12">
                    <label class="form-label small">Description</label>
                    <textarea class="form-control form-control-sm expense-description" rows="1" 
                        placeholder="Optional description"></textarea>
                </div>
            </div>
        </div>
    `;

        container.appendChild(row);
    }

    // ========================================
    // REMOVE EXPENSE ROW
    // ========================================
    function removeExpenseRow(rowId) {
        const row = document.getElementById(rowId);
        if (row) {
            row.remove();
        }
    }

    // ========================================
    // OPEN ASSIGN BUS MODAL (Updated for Segment Assignments)
    // ========================================
    function openAssignBusModal(tripId) {
        // Reset expense row counter
        expenseRowCounter = 0;

        const tripData = appState.tripData;
        if (!tripData || !tripData.trip || !tripData.from_stop || !tripData.to_stop) {
            Swal.fire({
                icon: 'error',
                title: 'Trip Data Not Available',
                text: 'Please load a trip first before assigning a bus.',
                confirmButtonColor: '#d33'
            });
            return;
        }

        // Use tripId parameter (already declared in function signature)
        // Verify tripId matches tripData
        if (tripId !== tripData.trip.id) {
            console.warn('Trip ID mismatch between parameter and trip data');
        }

        const defaultFromTripStopId = tripData.from_stop.id;
        const defaultToTripStopId = tripData.to_stop.id;
        const defaultFromTerminalId = tripData.from_stop.terminal_id;
        const defaultToTerminalId = tripData.to_stop.terminal_id;

        // Fetch list of available buses, expense types, and all trip stops
        Promise.all([
            $.ajax({
                url: "{{ route('admin.bookings.list-buses') }}",
                type: 'GET'
            }),
            $.ajax({
                url: "{{ route('admin.bookings.expense-types') }}",
                type: 'GET'
            }),
            $.ajax({
                url: "{{ route('admin.bus-assignments.trip-stops', ['tripId' => ':tripId']) }}".replace(
                    ':tripId', tripId),
                type: 'GET'
            })
        ]).then(function([busesResponse, expenseTypesResponse, tripStopsResponse]) {
            if (!busesResponse.success || !expenseTypesResponse.success || !tripStopsResponse.success) {
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to Load Data',
                    text: 'Unable to fetch required data. Please try again.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            const buses = busesResponse.buses;
            const expenseTypes = expenseTypesResponse.expense_types;
            const tripStops = tripStopsResponse.stops.sort((a, b) => a.sequence - b.sequence);

            let busesHtml = '<option value="">-- Select a Bus --</option>';
            buses.forEach(bus => {
                busesHtml +=
                    `<option value="${bus.id}">${bus.name} (${bus.registration_number})</option>`;
            });

            let expenseTypesHtml = '<option value="">-- Select Expense Type --</option>';
            expenseTypes.forEach(type => {
                expenseTypesHtml +=
                    `<option value="${type.value}" data-icon="${type.icon}">${type.label}</option>`;
            });

            // Build trip stops dropdowns
            let fromStopHtml = '<option value="">-- Select From Terminal --</option>';
            let toStopHtml = '<option value="">-- Select To Terminal --</option>';

            tripStops.forEach(stop => {
                const selectedFrom = stop.id == defaultFromTripStopId ? 'selected' : '';
                const selectedTo = stop.id == defaultToTripStopId ? 'selected' : '';

                fromStopHtml +=
                    `<option value="${stop.id}" ${selectedFrom} data-terminal-id="${stop.terminal_id}">${stop.label}</option>`;

                // To stops should only include stops after the selected from stop
                if (selectedFrom || stop.sequence > (tripStops.find(s => s.id == defaultFromTripStopId)
                        ?.sequence || 0)) {
                    toStopHtml +=
                        `<option value="${stop.id}" ${selectedTo} data-terminal-id="${stop.terminal_id}">${stop.label}</option>`;
                }
            });

            const modalBody = document.getElementById('assignBusModalBody');
            const defaultFromStop = tripStops.find(s => s.id == defaultFromTripStopId);
            const defaultToStop = tripStops.find(s => s.id == defaultToTripStopId);
            const defaultFromLabel = defaultFromStop ? defaultFromStop.label : 'N/A';
            const defaultToLabel = defaultToStop ? defaultToStop.label : 'N/A';

            modalBody.innerHTML = `
                <form id="assignBusForm">
                    <!-- Segment Selection (Auto-selected, Read-only) -->
                    <div class="alert alert-info mb-3">
                        <h6 class="fw-bold mb-2"><i class="fas fa-route"></i> Trip Segment (Auto-selected from Booking)</h6>
                        <p class="mb-2 small">This segment is automatically set from your booking selection and cannot be changed.</p>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="fas fa-map-marker-alt"></i> From Terminal
                            </label>
                            <input type="text" id="fromTripStopDisplay" class="form-control" 
                                value="${defaultFromLabel}" readonly 
                                style="background-color: #f8f9fa; cursor: not-allowed;">
                            <select id="fromTripStopSelect" class="form-select d-none" required>
                                ${fromStopHtml}
                            </select>
                            <small class="text-muted d-block mt-1">Auto-selected from your booking</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">
                                <i class="fas fa-map-marker-alt"></i> To Terminal
                            </label>
                            <input type="text" id="toTripStopDisplay" class="form-control" 
                                value="${defaultToLabel}" readonly 
                                style="background-color: #f8f9fa; cursor: not-allowed;">
                            <select id="toTripStopSelect" class="form-select d-none" required>
                                ${toStopHtml}
                            </select>
                            <small class="text-muted d-block mt-1">Auto-selected from your booking</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">
                            <i class="fas fa-bus"></i> Select Bus <span class="text-danger">*</span>
                        </label>
                        <select id="busSelect" class="form-select form-select-lg" required>
                            ${busesHtml}
                        </select>
                        <small class="text-muted d-block mt-2">Choose a bus to assign to this trip segment</small>
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

                    <hr class="my-4">

                    <h6 class="fw-bold mb-3"><i class="fas fa-user-friends"></i> Host/Trip Attendant Information (Optional)</h6>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Host Name</label>
                            <input type="text" id="hostName" class="form-control" placeholder="Enter host/trip attendant name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Host Phone</label>
                            <input type="tel" id="hostPhone" class="form-control" placeholder="03001234567">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea id="assignmentNotes" class="form-control" rows="2" placeholder="Optional notes for this assignment"></textarea>
                    </div>

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-money-bill-wave"></i> Trip Expenses (Auto-filled with segment terminals)</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addExpenseRow()">
                            <i class="fas fa-plus"></i> Add Expense
                        </button>
                    </div>

                    <div id="expensesContainer" class="mb-3">
                        <!-- Expense rows will be added here -->
                    </div>

                    <input type="hidden" id="tripIdInput" value="${tripId}">
                    <input type="hidden" id="fromTripStopId" value="${defaultFromTripStopId}">
                    <input type="hidden" id="toTripStopId" value="${defaultToTripStopId}">
                    <input type="hidden" id="fromTerminalId" value="${defaultFromTerminalId || ''}">
                    <input type="hidden" id="toTerminalId" value="${defaultToTerminalId || ''}">
                </form>
            `;

            // Store expense types and trip stops for use in addExpenseRow
            window.expenseTypes = expenseTypes;
            window.tripStops = tripStops;

            const modalElement = document.getElementById('assignBusModal');
            const modal = new bootstrap.Modal(modalElement, {
                backdrop: 'static', // Modal cannot be closed by clicking outside
                keyboard: false // Modal cannot be closed by pressing ESC
            });

            // Reset form when modal is hidden (closed) - only when X or Cancel is clicked
            modalElement.addEventListener('hidden.bs.modal', function() {
                resetAssignBusForm();
            });

            // Prevent closing on backdrop click (extra safety)
            modalElement.addEventListener('click', function(e) {
                if (e.target === modalElement) {
                    e.stopPropagation();
                }
            });

            modal.show();

            // Set hidden selects to default values (auto-selected, read-only)
            const fromTripStopSelect = document.getElementById('fromTripStopSelect');
            const toTripStopSelect = document.getElementById('toTripStopSelect');
            const fromTripStopIdHidden = document.getElementById('fromTripStopId');
            const toTripStopIdHidden = document.getElementById('toTripStopId');
            const fromTerminalIdHidden = document.getElementById('fromTerminalId');
            const toTerminalIdHidden = document.getElementById('toTerminalId');

            // Set the hidden selects to default values (they're hidden, used only for form submission)
            if (fromTripStopSelect) {
                fromTripStopSelect.value = defaultFromTripStopId;
            }
            if (toTripStopSelect) {
                toTripStopSelect.value = defaultToTripStopId;
            }

            // Ensure hidden fields are set correctly
            if (fromTripStopIdHidden) {
                fromTripStopIdHidden.value = defaultFromTripStopId;
            }
            if (toTripStopIdHidden) {
                toTripStopIdHidden.value = defaultToTripStopId;
            }
            if (fromTerminalIdHidden) {
                fromTerminalIdHidden.value = defaultFromTerminalId;
            }
            if (toTerminalIdHidden) {
                toTerminalIdHidden.value = defaultToTerminalId;
            }

            // Add first expense row
            addExpenseRow();

            // Handle confirm button - Create Bus Assignment
            document.getElementById('confirmAssignBusBtn').onclick = () => {
                const busId = document.getElementById('busSelect').value;
                const driverName = document.getElementById('driverName').value;
                const driverPhone = document.getElementById('driverPhone').value;
                const driverCnic = document.getElementById('driverCnic').value;
                const driverLicense = document.getElementById('driverLicense').value;
                const driverAddress = document.getElementById('driverAddress').value;
                const hostName = document.getElementById('hostName').value;
                const hostPhone = document.getElementById('hostPhone').value;
                const assignmentNotes = document.getElementById('assignmentNotes').value;
                // Get trip stop IDs from hidden fields (auto-selected, read-only)
                const fromTripStopId = document.getElementById('fromTripStopId').value;
                const toTripStopId = document.getElementById('toTripStopId').value;

                if (!busId || !driverName || !driverPhone || !driverCnic || !driverLicense || !
                    fromTripStopId || !toTripStopId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Missing Information',
                        text: 'Please fill all required fields: Bus, Driver Name, Phone, CNIC, and License.',
                        confirmButtonColor: '#ffc107'
                    });
                    return;
                }

                // Collect expense data (auto-filled with segment terminals)
                const expenses = [];
                const expenseRows = document.querySelectorAll('.expense-row');
                const fromTerminalId = document.getElementById('fromTerminalId').value;
                const toTerminalId = document.getElementById('toTerminalId').value;

                expenseRows.forEach(row => {
                    const expenseType = row.querySelector('.expense-type')?.value;
                    const amount = row.querySelector('.expense-amount')?.value;

                    // Only add if expense type and amount are provided
                    if (expenseType && amount && parseFloat(amount) > 0) {
                        expenses.push({
                            expense_type: expenseType,
                            amount: parseFloat(amount),
                            from_terminal_id: fromTerminalId || null,
                            to_terminal_id: toTerminalId || null,
                            description: row.querySelector('.expense-description')?.value ||
                                null,
                        });
                    }
                });

                // Show loading
                Swal.fire({
                    title: 'Assigning Bus...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                // Step 1: Create Bus Assignment
                $.ajax({
                    url: "{{ route('admin.bus-assignments.store') }}",
                    type: 'POST',
                    data: {
                        trip_id: tripId,
                        from_trip_stop_id: fromTripStopId,
                        to_trip_stop_id: toTripStopId,
                        bus_id: busId,
                        driver_name: driverName,
                        driver_phone: driverPhone,
                        driver_cnic: driverCnic,
                        driver_license: driverLicense,
                        driver_address: driverAddress,
                        host_name: hostName || null,
                        host_phone: hostPhone || null,
                        notes: assignmentNotes || null,
                        _token: document.querySelector('meta[name="csrf-token"]').content
                    },
                    success: function(assignmentResponse) {
                        // Step 2: Create expenses if any (using separate expenses endpoint)
                        if (expenses.length > 0) {
                            $.ajax({
                                url: "{{ route('admin.bookings.add-expenses', ['tripId' => ':tripId']) }}"
                                    .replace(':tripId', tripId),
                                type: 'POST',
                                data: {
                                    expenses: expenses,
                                    _token: document.querySelector(
                                        'meta[name="csrf-token"]').content
                                },
                                success: function() {
                                    Swal.close();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success!',
                                        text: `Bus assignment and ${expenses.length} expense(s) created successfully!`,
                                        confirmButtonColor: '#28a745',
                                        timer: 2000,
                                        timerProgressBar: true
                                    });
                                    // Close modal and reset form
                                    const modalElement = document.getElementById(
                                        'assignBusModal');
                                    const modalInstance = bootstrap.Modal
                                        .getInstance(modalElement);
                                    if (modalInstance) {
                                        modalInstance.hide();
                                    }
                                    reloadTripData();
                                },
                                error: function(error) {
                                    Swal.close();
                                    const message = error.responseJSON?.error ||
                                        error.responseJSON?.message ||
                                        'Bus assignment created but expenses failed. Please add expenses manually.';
                                    Swal.fire({
                                        icon: 'warning',
                                        title: 'Partially Successful',
                                        text: message,
                                        confirmButtonColor: '#ffc107'
                                    });
                                    // Close modal and reset form
                                    const modalElement = document.getElementById(
                                        'assignBusModal');
                                    const modalInstance = bootstrap.Modal
                                        .getInstance(modalElement);
                                    if (modalInstance) {
                                        modalInstance.hide();
                                    }
                                    reloadTripData();
                                }
                            });
                        } else {
                            Swal.close();
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Bus assignment created successfully!',
                                confirmButtonColor: '#28a745',
                                timer: 2000,
                                timerProgressBar: true
                            });
                            // Close modal and reset form
                            const modalElement = document.getElementById('assignBusModal');
                            const modalInstance = bootstrap.Modal.getInstance(modalElement);
                            if (modalInstance) {
                                modalInstance.hide();
                            }
                            reloadTripData();
                        }
                    },
                    error: function(error) {
                        Swal.close();
                        const message = error.responseJSON?.message || error.responseJSON
                            ?.error ||
                            'Unable to create bus assignment. Please check your connection and try again.';
                        Swal.fire({
                            icon: 'error',
                            title: 'Failed',
                            text: message,
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            };

            // Helper function to reload trip data
            function reloadTripData() {
                if (appState.tripData && appState.tripData.trip.id) {
                    const fromTerminalId = document.getElementById('fromTerminal').value;
                    const toTerminalId = document.getElementById('toTerminal').value;
                    const departureTimeId = document.getElementById('departureTime').value;
                    const date = document.getElementById('travelDate').value;

                    if (fromTerminalId && toTerminalId && departureTimeId && date) {
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
                                appState.tripData = reloadResponse;
                                appState.seatMap = reloadResponse.seat_map;
                                renderBusDriverSection(reloadResponse.trip);
                            },
                            error: function() {
                                loadTrip();
                            }
                        });
                    }
                }
            }
        }).catch(function(error) {
            Swal.fire({
                icon: 'error',
                title: 'Failed to Fetch Buses',
                text: 'Unable to load bus list. Please check your connection and try again.',
                confirmButtonColor: '#d33'
            });
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

    // Make function available globally
    window.openAssignBusModal = openAssignBusModal;

    // ========================================
    // RESET ASSIGN BUS FORM
    // ========================================
    function resetAssignBusForm() {
        // Reset expense row counter
        expenseRowCounter = 0;

        // Clear expense types and trip stops from window
        window.expenseTypes = null;
        window.tripStops = null;

        // Clear the modal body
        const modalBody = document.getElementById('assignBusModalBody');
        if (modalBody) {
            modalBody.innerHTML = '';
        }

        // Clear expenses container
        const expensesContainer = document.getElementById('expensesContainer');
        if (expensesContainer) {
            expensesContainer.innerHTML = '';
        }

        // Reset confirm button onclick handler
        const confirmBtn = document.getElementById('confirmAssignBusBtn');
        if (confirmBtn) {
            confirmBtn.onclick = null;
        }

        console.log('Assign Bus form has been reset');
    }
</script>
