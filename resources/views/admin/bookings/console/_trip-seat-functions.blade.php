{{-- Trip Loading and Seat Management Functions --}}

// ========================================
// LOAD TRIP
// ========================================
function loadTrip() {
    const fromTerminalId = document.getElementById('fromTerminal').value;
    const toTerminalId = document.getElementById('toTerminal').value;
    const departureTimeId = document.getElementById('departureTime').value;
    const date = document.getElementById('travelDate').value;

    if (!fromTerminalId || !toTerminalId || !departureTimeId || !date) {
        Swal.fire({
            icon: 'warning',
            title: 'Missing Information',
            text: 'Please fill all required fields: From Terminal, To Terminal, Departure Time, and Travel Date.',
            confirmButtonColor: '#ffc107'
        });
        return;
    }

    document.getElementById('loadTripBtn').disabled = true;

    $.ajax({
        url: "{{ route('admin.bookings.load-trip') }}",
        type: 'POST',
        data: {
            from_terminal_id: fromTerminalId,
            to_terminal_id: toTerminalId,
            timetable_stop_id: departureTimeId,
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
    arrivalInput.disabled = false;
}

// ========================================
// RENDER SEAT MAP
// ========================================
function renderSeatMap() {
    const grid = document.getElementById('seatGrid');
    grid.innerHTML = '';

    // Create container for seat rows
    const container = document.createElement('div');
    container.style.display = 'flex';
    container.style.flexDirection = 'column';
    container.style.alignItems = 'center';
    container.style.gap = '0.25rem';
    container.style.width = '100%';

    for (let row = 0; row < 11; row++) {
        const rowDiv = document.createElement('div');
        rowDiv.className = 'seat-row';
        rowDiv.style.display = 'flex';
        rowDiv.style.gap = '0.25rem';
        rowDiv.style.justifyContent = 'center';
        rowDiv.style.width = 'fit-content';

        for (let col = 0; col < 4; col++) {
            const seatNumber = row * 4 + col + 1;
            const seat = appState.seatMap[seatNumber];

            const button = document.createElement('button');
            button.className = 'btn btn-sm';
            button.style.width = '32px';
            button.style.height = '32px';
            button.style.fontSize = '0.65rem';
            button.style.padding = '1px';
            button.style.lineHeight = '1';
            button.style.flexShrink = '0';
            button.textContent = seatNumber;
            button.title = `Seat ${seatNumber} - ${seat.status}`;

            // Set color and status
            if (appState.selectedSeats[seatNumber]) {
                // User's own selected seat
                button.className += ' bg-info text-white';
            } else if (appState.lockedSeats[seatNumber] && appState.lockedSeats[seatNumber] !== appState.userId) {
                // Locked by another user - show as held
                button.className += ' bg-warning text-dark';
                button.disabled = true;
                button.title = `Seat ${seatNumber} - Locked by another user`;
            } else if (seat.status === 'booked') {
                button.className += ' bg-danger text-white';
                button.disabled = true;
            } else if (seat.status === 'held') {
                button.className += ' bg-warning text-dark';
                button.disabled = true;
            } else {
                button.className += ' bg-success text-white';
            }

            // Disable if not available or locked by another user
            if (seat.status === 'booked' || seat.status === 'held' ||
                (appState.lockedSeats[seatNumber] && appState.lockedSeats[seatNumber] !== appState.userId)) {
                button.disabled = true;
            }

            button.onclick = () => handleSeatClick(seatNumber);
            rowDiv.appendChild(button);
        }

        container.appendChild(rowDiv);
    }

    grid.appendChild(container);
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
        list.innerHTML = '<p class="text-muted mb-0">No seats selected yet</p>';
        updatePassengerForms(); // ← Clear passenger forms
        calculateTotalFare();
        return;
    }

    let html = '';
    Object.keys(appState.selectedSeats).sort((a, b) => a - b).forEach(seat => {
        const gender = appState.selectedSeats[seat] === 'male' ? '👨 Male' : '👩 Female';
        html +=
            `<div class="mb-2 p-2 bg-white rounded border"><strong>Seat ${seat}</strong> - ${gender}</div>`;
    });
    list.innerHTML = html;
    updatePassengerForms(); // ← Update passenger forms based on seats
    calculateTotalFare();
}

