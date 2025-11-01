{{-- Booking and Payment Functions --}}

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

    // Recalculate final amount to apply mobile wallet tax
    calculateFinal();
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
    let tax = parseFloat(document.getElementById('tax').value) || 0;

    // Apply mobile wallet tax if payment method is mobile_wallet
    if (paymentMethod === 'mobile_wallet') {
        tax = 40;
    }

    const discountAmount = appState.discountAmount || 0;
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
            discount_amount: discountAmount, // Discount amount from fare
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
            document.getElementById('confirmedDiscount').textContent = parseFloat(booking
                    .discount_amount || 0)
                .toFixed(2);
            document.getElementById('confirmedTax').textContent = parseFloat(booking.tax_amount)
                .toFixed(2);
            document.getElementById('confirmedFinal').textContent = parseFloat(booking.final_amount)
                .toFixed(2);
            document.getElementById('paymentMethodDisplay').textContent = booking.payment_method ||
                'N/A';

            // Unlock seats after successful booking (they will be confirmed via WebSocket)
            const bookedSeats = booking.seats.map(Number);
            unlockSeats(bookedSeats);

            // Store booking ID for print functionality
            window.lastBookingId = booking.id;

            // Show success modal
            const successModal = new bootstrap.Modal(document.getElementById('successModal'));
            successModal.show();

            // Setup print button
            document.getElementById('printTicketBtn').onclick = function() {
                if (window.lastBookingId) {
                    window.open('/admin/bookings/' + window.lastBookingId + '/print', '_blank');
                }
            };
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

