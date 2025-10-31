{{-- Terminal and Route Related Functions --}}

// ========================================
// FETCH TERMINALS
// ========================================
function fetchTerminals() {
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
// ON TO TERMINAL CHANGE
// ========================================
document.getElementById('toTerminal')?.addEventListener('change', onToTerminalChange);

function onToTerminalChange() {
    const fromTerminalId = document.getElementById('fromTerminal').value;
    const toTerminalId = document.getElementById('toTerminal').value;
    const date = document.getElementById('travelDate').value;

    document.getElementById('departureTime').innerHTML = '<option value="">Select Departure Time</option>';
    document.getElementById('departureTime').disabled = true;
    // Reset arrival time when destination changes
    document.getElementById('arrivalTime').value = '';
    document.getElementById('arrivalTime').disabled = true;

    if (fromTerminalId && toTerminalId && date) {
        fetchFare(fromTerminalId, toTerminalId);
        fetchDepartureTimes(fromTerminalId, toTerminalId, date);
    }
}

// ========================================
// FETCH DEPARTURE TIMES (Timetable Stops)
// ========================================
function fetchDepartureTimes(fromTerminalId, toTerminalId, date) {
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
                // Store arrival_at as data attribute
                const arrivalTime = stop.arrival_at ? formatTimeForInput(stop.arrival_at) : '';
                timeSelect.innerHTML += `<option value="${stop.id}" data-arrival="${arrivalTime}">${stop.departure_at}</option>`;
            });

            timeSelect.disabled = false;
        },
        error: function() {
            Swal.fire({
                icon: 'info',
                title: 'No Trips Available',
                text: 'No trips are available for the selected route and date. Please try a different date or route.',
                confirmButtonColor: '#3085d6'
            });
        }
    });
}

// ========================================
// FORMAT TIME FOR INPUT (HH:MM)
// ========================================
function formatTimeForInput(dateTimeString) {
    if (!dateTimeString) return '';
    const date = new Date(dateTimeString);
    const hours = String(date.getHours()).padStart(2, '0');
    const minutes = String(date.getMinutes()).padStart(2, '0');
    return `${hours}:${minutes}`;
}

// ========================================
// ON DEPARTURE TIME CHANGE
// ========================================
document.getElementById('departureTime')?.addEventListener('change', function() {
    const selectedOption = this.options[this.selectedIndex];
    const arrivalTime = selectedOption.getAttribute('data-arrival');
    const arrivalTimeInput = document.getElementById('arrivalTime');

    if (arrivalTime && arrivalTime.trim() !== '') {
        arrivalTimeInput.value = arrivalTime;
        arrivalTimeInput.disabled = false;
    } else {
        arrivalTimeInput.value = '';
        arrivalTimeInput.disabled = true;
    }
});

// ========================================
// ON TRAVEL DATE CHANGE
// ========================================
document.getElementById('travelDate')?.addEventListener('change', function() {
    const fromTerminalId = document.getElementById('fromTerminal').value;
    const toTerminalId = document.getElementById('toTerminal').value;

    if (fromTerminalId && toTerminalId) {
        fetchDepartureTimes(fromTerminalId, toTerminalId, this.value);
        // Reset arrival time when date changes
        document.getElementById('arrivalTime').value = '';
        document.getElementById('arrivalTime').disabled = true;
    }
});

