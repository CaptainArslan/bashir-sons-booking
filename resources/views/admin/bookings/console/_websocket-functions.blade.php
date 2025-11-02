<script>
    {{-- WebSocket and Seat Locking Functions --}}

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
</script>
