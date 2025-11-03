<script>
    {{-- Trip Loading and Seat Management Functions --}}

    // ========================================
    // LOAD TRIP
    // ========================================
    function loadTrip() {
        const fromTerminalId = document.getElementById('fromTerminal').value;
        const toTerminalId = document.getElementById('toTerminal').value;
        const timetableId = document.getElementById('departureTime').value;
        const date = document.getElementById('travelDate').value;
        const arrivalTime = document.getElementById('arrivalTime').value;

        // Validate all required fields
        if (!fromTerminalId || !toTerminalId || !timetableId || !date) {
            Swal.fire({
                icon: 'warning',
                title: 'Missing Information',
                text: 'Please fill all required fields: From Terminal, To Terminal, Departure Time, and Travel Date.',
                confirmButtonColor: '#ffc107'
            });
            return;
        }

        // Validate terminals are different
        if (fromTerminalId === toTerminalId) {
            Swal.fire({
                icon: 'warning',
                title: 'Invalid Selection',
                text: 'Origin and destination terminals must be different.',
                confirmButtonColor: '#ffc107'
            });
            return;
        }

        // Check if fare is valid
        if (appState.fareValid === false || !appState.fareData) {
            Swal.fire({
                icon: 'error',
                title: 'Fare Not Configured',
                text: 'No valid fare found for this route. Please configure the fare first before loading the trip.',
                confirmButtonColor: '#d33'
            }).then(() => {
                // Scroll to fare error if visible
                const fareErrorContainer = document.getElementById('fareErrorContainer');
                if (fareErrorContainer && fareErrorContainer.style.display !== 'none') {
                    fareErrorContainer.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
            return;
        }

        // Validate arrival time is set
        // if (!arrivalTime || arrivalTime === 'N/A') {
        //     Swal.fire({
        //         icon: 'warning',
        //         title: 'Missing Arrival Time',
        //         text: 'Please select a departure time to set the arrival time.',
        //         confirmButtonColor: '#ffc107'
        //     });
        //     return;
        // }

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

                // Update bus & driver section (with segment assignments)
                renderBusDriverSection(response.trip);
                renderSeatMap();
                document.getElementById('tripContent').style.display = 'block';
                document.getElementById('tripContent').scrollIntoView({
                    behavior: 'smooth'
                });
                
                // Load trip passengers immediately after trip is loaded
                const tripId = response.trip.id;
                console.log('[loadTrip] Trip loaded successfully. Trip ID:', tripId);
                console.log('[loadTrip] loadTripPassengers function available:', typeof loadTripPassengers === 'function');
                
                // Call loadTripPassengers - scripts are loaded in order so function should be available
                // Use a small delay to ensure DOM is fully rendered
                setTimeout(function() {
                    if (typeof loadTripPassengers === 'function') {
                        console.log('[loadTrip] Calling loadTripPassengers for trip:', tripId);
                        loadTripPassengers(tripId);
                    } else {
                        console.error('[loadTrip] ERROR: loadTripPassengers function not found!');
                        // Try one more time after a longer delay
                        setTimeout(function() {
                            if (typeof loadTripPassengers === 'function') {
                                console.log('[loadTrip] Retry successful - calling loadTripPassengers for trip:', tripId);
                                loadTripPassengers(tripId);
                            } else {
                                console.error('[loadTrip] ERROR: loadTripPassengers still not available after retry');
                                const passengersList = document.getElementById('tripPassengersList');
                                if (passengersList) {
                                    passengersList.innerHTML = `
                                        <div class="alert alert-danger">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            <strong>Error:</strong> Unable to load passengers. Please refresh the page.
                                        </div>
                                    `;
                                }
                            }
                        }, 800);
                    }
                }, 200);
                
                setupTripWebSocket(response.trip.id); // Setup WebSocket for this trip
            },
            error: function(error) {
                let message = 'Unable to load trip information. Please check all selections and try again.';
                
                if (error.responseJSON) {
                    if (error.responseJSON.error) {
                        message = error.responseJSON.error;
                    } else if (error.responseJSON.message) {
                        message = error.responseJSON.message;
                    } else if (error.responseJSON.errors) {
                        // Validation errors
                        const errors = error.responseJSON.errors;
                        const errorList = [];
                        for (const field in errors) {
                            if (Array.isArray(errors[field])) {
                                errorList.push(...errors[field]);
                            } else {
                                errorList.push(errors[field]);
                            }
                        }
                        if (errorList.length > 0) {
                            message = errorList.join(', ');
                        }
                    }
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Failed to Load Trip',
                    html: message,
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
        const arrivalInput = document.getElementById('arrivalTime');

        if (!select || !selectedOption || selectedOption.value === '') {
            // No departure time selected
            if (arrivalInput) {
                arrivalInput.value = '';
                arrivalInput.disabled = true;
            }
            return;
        }

        // Read data-arrival attribute
        const arrivalTime = selectedOption.getAttribute('data-arrival');

        // Set input value and enable it
        if (arrivalInput) {
            arrivalInput.value = arrivalTime || 'N/A';
            arrivalInput.disabled = false;
        }
        
        // Validate fare when departure time changes (if both terminals are selected)
        const fromTerminalId = document.getElementById('fromTerminal').value;
        const toTerminalId = document.getElementById('toTerminal').value;
        
        if (fromTerminalId && toTerminalId && typeof fetchFare === 'function') {
            // Re-fetch fare to ensure it's still valid
            fetchFare(fromTerminalId, toTerminalId);
        }
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
            // Check gender for booked seats - normalize to lowercase for comparison
            const seatGender = seat?.gender ? String(seat.gender).toLowerCase() : null;

            if (seatGender === 'male') {
                button.className += ' seat-booked-male';
                genderIcon = '👨';
                button.title = `Seat ${seatNumber} - Booked (Male)`;
            } else if (seatGender === 'female') {
                button.className += ' seat-booked-female';
                genderIcon = '👩';
                button.title = `Seat ${seatNumber} - Booked (Female)`;
            } else {
                // Fallback for booked seats without gender - show without icon
                button.className += ' seat-booked-male'; // Use default booked color
                button.title = `Seat ${seatNumber} - Booked`;
                // No gender icon for seats without gender info
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
            badge.style.cssText =
                'font-size: 0.75rem; display: inline-flex; align-items: center; gap: 0.25rem;';

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
</script>
