{{-- Fare Calculation Functions --}}

// ========================================
// FETCH FARE FOR SEGMENT
// ========================================
function fetchFare(fromTerminalId, toTerminalId) {
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
    updateSummaryCard();
    calculateFinal();
}

// ========================================
// CALCULATE TOTAL FARE (based on seat count)
// ========================================
function calculateTotalFare() {
    const seatCount = Object.keys(appState.selectedSeats).length;
    const baseFare = appState.baseFare || 0;
    const totalFare = baseFare * seatCount;

    document.getElementById('totalFare').value = totalFare.toFixed(2);
    updateSummaryCard();
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
    updateSummaryCard();
    calculateReturn();
}

// ========================================
// UPDATE SUMMARY CARD (Matching Image Design)
// ========================================
function updateSummaryCard() {
    const totalFare = parseFloat(document.getElementById('totalFare').value) || 0;
    const tax = parseFloat(document.getElementById('tax').value) || 0;
    const final = totalFare + tax;
    
    // Update outbound fare (base fare)
    const outboundFareEl = document.getElementById('outboundFare');
    if (outboundFareEl) {
        outboundFareEl.textContent = `Rs ${totalFare.toFixed(0)}`;
    }
    
    // Update total fare (final amount)
    const totalSummaryFareEl = document.getElementById('totalSummaryFare');
    if (totalSummaryFareEl) {
        totalSummaryFareEl.textContent = `Rs ${final.toFixed(0)}`;
    }
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

