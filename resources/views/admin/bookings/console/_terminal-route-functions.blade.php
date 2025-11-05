<script>
    {{-- Terminal and Route Related Functions --}}

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
        
        // Reset dependent fields
        document.getElementById('toTerminal').value = '';
        document.getElementById('departureTime').innerHTML = '<option value="">Select Departure Time</option>';
        document.getElementById('arrivalTime').value = '';
        document.getElementById('toTerminal').disabled = true;
        document.getElementById('departureTime').disabled = true;
        document.getElementById('arrivalTime').disabled = true;
        
        // Hide trip content if visible
        const tripContent = document.getElementById('tripContent');
        if (tripContent) {
            tripContent.style.display = 'none';
        }
        
        // Reset fare and app state
        if (typeof hideFareError === 'function') {
            hideFareError();
        }
        if (typeof resetFareDisplay === 'function') {
            resetFareDisplay();
        }

        if (fromTerminalId) {
            fetchToTerminals(fromTerminalId);
            // Don't fetch fare yet - need both terminals
        } else {
            // Reset fare display
            if (typeof resetFareDisplay === 'function') {
                resetFareDisplay();
            }
        }
    }

    // ========================================
    // FETCH TO TERMINALS (Route Stops)
    // ========================================
    function fetchToTerminals(fromTerminalId) {
        $.ajax({
            url: "http://bashir-sons.test/admin/bookings/console/route-stops",
            type: 'GET',
            data: {
                from_terminal_id: fromTerminalId
            },
            success: function(response) {

                appState.routeStops = response.route_stops;

                const toSelect = document.getElementById('toTerminal');
                toSelect.innerHTML = '<option value="">Select Destination</option>';

                response.route_stops.forEach(stop => {
                    toSelect.innerHTML += `
                    <option value="${stop.terminal_id}">
                        ${stop.name} (${stop.code})
                    </option>
                `;
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

        if (fromTerminalId && toTerminalId && date) {
            fetchFare(fromTerminalId, toTerminalId);
            fetchDepartureTimes(fromTerminalId, toTerminalId, date);
        }
    }

    // ========================================
    // FETCH DEPARTURE TIMES (Timetable Stops)
    // ========================================
    function fetchDepartureTimes(fromTerminalId, toTerminalId, date) {
        if (!fromTerminalId || !toTerminalId || !date) {
            console.error('[fetchDepartureTimes] Missing required parameters');
            return;
        }
        
        // Reset arrival time
        document.getElementById('arrivalTime').value = '';
        
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
                appState.timetableStops = response.timetable_stops || [];
                const timeSelect = document.getElementById('departureTime');

                timeSelect.innerHTML = '<option value="">Select Departure Time</option>';
                
                if (appState.timetableStops.length === 0) {
                    timeSelect.innerHTML += '<option value="" disabled>No trips available</option>';
                    timeSelect.disabled = true;
                    Swal.fire({
                        icon: 'info',
                        title: 'No Trips Available',
                        text: 'No trips are available for the selected route and date. Please try a different date or route.',
                        confirmButtonColor: '#3085d6'
                    });
                } else {
                    appState.timetableStops.forEach(stop => {
                        const arrivalTime = stop.arrival_at || 'N/A';
                        timeSelect.innerHTML +=
                            `<option value="${stop.timetable_id}" data-arrival="${arrivalTime}">${stop.departure_at}</option>`;
                    });
                    timeSelect.disabled = false;
                }
            },
            error: function(xhr) {
                const timeSelect = document.getElementById('departureTime');
                timeSelect.innerHTML = '<option value="">Error loading times</option>';
                timeSelect.disabled = true;
                
                const errorMsg = xhr.responseJSON?.error || xhr.responseJSON?.message || 
                    'Unable to fetch departure times. Please check your connection and try again.';
                
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to Load Departure Times',
                    text: errorMsg,
                    confirmButtonColor: '#d33'
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
        const date = this.value;

        // Reset departure time and arrival time
        document.getElementById('departureTime').innerHTML = '<option value="">Select Departure Time</option>';
        document.getElementById('departureTime').disabled = true;
        document.getElementById('arrivalTime').value = '';
        document.getElementById('arrivalTime').disabled = true;

        // Hide trip content if visible
        const tripContent = document.getElementById('tripContent');
        if (tripContent) {
            tripContent.style.display = 'none';
        }

        if (fromTerminalId && toTerminalId && date) {
            // Re-fetch fare to ensure it's still valid
            if (typeof fetchFare === 'function') {
                fetchFare(fromTerminalId, toTerminalId);
            }
            // Fetch departure times for new date
            fetchDepartureTimes(fromTerminalId, toTerminalId, date);
        }
    });
</script>
