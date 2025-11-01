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
// ADD EXPENSE ROW
// ========================================
let expenseRowCounter = 0;

function addExpenseRow() {
    const container = document.getElementById('expensesContainer');
    if (!container || !window.expenseTypes) return;

    const rowId = `expense_row_${expenseRowCounter++}`;
    const expenseTypes = window.expenseTypes;
    const terminals = appState.terminals || [];
    const fromTerminalId = document.getElementById('fromTerminalId')?.value || '';
    const toTerminalId = document.getElementById('toTerminalId')?.value || '';

    let expenseTypesHtml = '<option value="">-- Select Type --</option>';
    expenseTypes.forEach(type => {
        expenseTypesHtml += `<option value="${type.value}">${type.label}</option>`;
    });

    let terminalsFromHtml = '<option value="">-- Select Terminal --</option>';
    let terminalsToHtml = '<option value="">-- Select Terminal --</option>';
    terminals.forEach(terminal => {
        const selectedFrom = terminal.id == fromTerminalId ? 'selected' : '';
        const selectedTo = terminal.id == toTerminalId ? 'selected' : '';
        terminalsFromHtml +=
            `<option value="${terminal.id}" ${selectedFrom}>${terminal.name} (${terminal.code})</option>`;
        terminalsToHtml +=
            `<option value="${terminal.id}" ${selectedTo}>${terminal.name} (${terminal.code})</option>`;
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
                <div class="col-md-2">
                    <label class="form-label small">From Terminal</label>
                    <select class="form-select form-select-sm expense-from-terminal">
                        ${terminalsFromHtml}
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small">To Terminal</label>
                    <select class="form-select form-select-sm expense-to-terminal">
                        ${terminalsToHtml}
                    </select>
                </div>
                <div class="col-md-1">
                    <label class="form-label small">&nbsp;</label>
                    <button type="button" class="btn btn-sm btn-outline-danger w-100" onclick="removeExpenseRow('${rowId}')" title="Remove">
                        <i class="fas fa-times"></i>
                    </button>
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
// OPEN ASSIGN BUS MODAL
// ========================================
function openAssignBusModal(tripId) {
    // Reset expense row counter
    expenseRowCounter = 0;

    // Fetch list of available buses and expense types
    Promise.all([
        $.ajax({
            url: "{{ route('admin.bookings.list-buses') }}",
            type: 'GET'
        }),
        $.ajax({
            url: "{{ route('admin.bookings.expense-types') }}",
            type: 'GET'
        })
    ]).then(function([busesResponse, expenseTypesResponse]) {
        if (!busesResponse.success || !expenseTypesResponse.success) {
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
        const tripData = appState.tripData;
        const fromTerminalId = tripData?.from_stop?.terminal_id || null;
        const toTerminalId = tripData?.to_stop?.terminal_id || null;

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

                    <hr class="my-4">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0"><i class="fas fa-money-bill-wave"></i> Trip Expenses</h6>
                        <button type="button" class="btn btn-sm btn-primary" onclick="addExpenseRow()">
                            <i class="fas fa-plus"></i> Add Expense
                        </button>
                    </div>

                    <div id="expensesContainer" class="mb-3">
                        <!-- Expense rows will be added here -->
                    </div>

                    <input type="hidden" id="tripIdInput" value="${tripId}">
                    <input type="hidden" id="fromTerminalId" value="${fromTerminalId || ''}">
                    <input type="hidden" id="toTerminalId" value="${toTerminalId || ''}">
                </form>
            `;

        // Store expense types for use in addExpenseRow
        window.expenseTypes = expenseTypes;

        const modal = new bootstrap.Modal(document.getElementById('assignBusModal'));
        modal.show();

        // Add first expense row
        addExpenseRow();

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

            // Collect expense data
            const expenses = [];
            const expenseRows = document.querySelectorAll('.expense-row');
            expenseRows.forEach(row => {
                const expenseType = row.querySelector('.expense-type')?.value;
                const amount = row.querySelector('.expense-amount')?.value;
                const fromTerminal = row.querySelector('.expense-from-terminal')?.value;
                const toTerminal = row.querySelector('.expense-to-terminal')?.value;
                const description = row.querySelector('.expense-description')?.value;

                // Only add if expense type and amount are provided
                if (expenseType && amount && parseFloat(amount) > 0) {
                    expenses.push({
                        expense_type: expenseType,
                        amount: parseFloat(amount),
                        from_terminal_id: fromTerminal || null,
                        to_terminal_id: toTerminal || null,
                        description: description || null,
                    });
                }
            });

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
                    expenses: expenses.length > 0 ? expenses : null,
                    _token: document.querySelector('meta[name="csrf-token"]')
                        .content
                },
                success: function(response) {
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Bus, Driver' + (expenses.length > 0 ?
                                    `, and ${expenses.length} expense(s)` : '') +
                                ' assigned successfully!',
                            confirmButtonColor: '#28a745',
                            timer: 2000,
                            timerProgressBar: true
                        });
                        modal.hide();
                        // Reload trip data from database to get updated bus assignment status
                        if (appState.tripData && appState.tripData.trip.id) {
                            // Get current form values to reload trip
                            const fromTerminalId = document.getElementById(
                                'fromTerminal').value;
                            const toTerminalId = document.getElementById(
                                'toTerminal').value;
                            const departureTimeId = document.getElementById(
                                'departureTime').value;
                            const date = document.getElementById('travelDate')
                                .value;

                            if (fromTerminalId && toTerminalId &&
                                departureTimeId && date) {
                                // Reload trip to get fresh data from database
                                $.ajax({
                                    url: "{{ route('admin.bookings.load-trip') }}",
                                    type: 'POST',
                                    data: {
                                        from_terminal_id: fromTerminalId,
                                        to_terminal_id: toTerminalId,
                                        timetable_id: departureTimeId,
                                        date: date,
                                        _token: document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content
                                    },
                                    success: function(reloadResponse) {
                                        // Update app state with fresh data
                                        appState.tripData =
                                            reloadResponse;
                                        appState.seatMap =
                                            reloadResponse.seat_map;

                                        // Re-render bus driver section with updated data
                                        renderBusDriverSection(
                                            reloadResponse.trip);
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

