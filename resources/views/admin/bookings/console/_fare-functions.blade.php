<script>
    {{-- Fare Calculation Functions --}}

    // ========================================
    // FETCH FARE FOR SEGMENT
    // ========================================
    function fetchFare(fromTerminalId, toTerminalId) {
        // Hide error display first
        hideFareError();
        
        if (!fromTerminalId || !toTerminalId) {
            resetFareDisplay();
            return;
        }
        
        // Check if same terminals selected
        if (fromTerminalId === toTerminalId) {
            showFareError('Please select different terminals for origin and destination.');
            resetFareDisplay();
            return;
        }

        showLoader(true, 'Loading fare...');

        $.ajax({
            url: "{{ route('admin.bookings.fare') }}",
            type: 'GET',
            data: {
                from_terminal_id: fromTerminalId,
                to_terminal_id: toTerminalId
            },
            success: function(response) {
                if (response.success && response.fare) {
                    // Hide error display on success
                    hideFareError();
                    
                    appState.fareData = response.fare;
                    appState.baseFare = response.fare.base_fare;
                    appState.fareValid = true; // Mark fare as valid

                    // Update UI with fare info - show base fare
                    document.getElementById('baseFare').value = parseFloat(response.fare.base_fare)
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
                    // Show persistent error
                    const errorMsg = response.message || 'No fare configuration found for this route segment. Please contact administrator.';
                    showFareError(errorMsg);
                    resetFareDisplay();
                    appState.fareValid = false;
                }
            },
            error: function(error) {
                let message = 'Unable to fetch fare information. Please check your connection and try again.';
                
                if (error.responseJSON) {
                    if (error.responseJSON.error) {
                        message = error.responseJSON.error;
                    } else if (error.responseJSON.message) {
                        message = error.responseJSON.message;
                    }
                }
                
                // Show persistent error
                showFareError(message);
                resetFareDisplay();
                appState.fareValid = false;
            },
            complete: function() {
                showLoader(false);
            }
        });
    }

    // ========================================
    // SHOW FARE ERROR (Persistent Display)
    // ========================================
    function showFareError(message) {
        const errorContainer = document.getElementById('fareErrorContainer');
        const errorMessage = document.getElementById('fareErrorMessage');
        
        if (errorContainer && errorMessage) {
            errorMessage.textContent = message;
            errorContainer.style.display = 'block';
            
            // Scroll to error if needed
            errorContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
        appState.fareValid = false;
    }

    // ========================================
    // HIDE FARE ERROR
    // ========================================
    function hideFareError() {
        const errorContainer = document.getElementById('fareErrorContainer');
        if (errorContainer) {
            errorContainer.style.display = 'none';
        }
    }

    // ========================================
    // RESET FARE DISPLAY
    // ========================================
    function resetFareDisplay() {
        appState.fareData = null;
        appState.baseFare = 0;
        appState.fareValid = false;
        
        const baseFareInput = document.getElementById('baseFare');
        const discountInfoInput = document.getElementById('discountInfo');
        const totalFareInput = document.getElementById('totalFare');
        
        if (baseFareInput) baseFareInput.value = '';
        if (discountInfoInput) discountInfoInput.value = '';
        if (totalFareInput) totalFareInput.value = '';
        
        calculateFinal();
    }

    // ========================================
    // CALCULATE TOTAL FARE (based on seat count with discount)
    // ========================================
    function calculateTotalFare() {
        const seatCount = Object.keys(appState.selectedSeats).length;
        const baseFare = appState.baseFare || 0;

        // Calculate total base fare
        let totalBaseFare = baseFare * seatCount;

        // Apply discount if available
        let discountAmount = 0;
        if (appState.fareData && appState.fareData.discount_type && appState.fareData.discount_value) {
            if (appState.fareData.discount_type === 'flat') {
                // Flat discount per seat
                discountAmount = parseFloat(appState.fareData.discount_value) * seatCount;
            } else if (appState.fareData.discount_type === 'percent') {
                // Percentage discount on total
                discountAmount = totalBaseFare * (parseFloat(appState.fareData.discount_value) / 100);
            }
        }

        // Total fare after discount
        const totalFare = Math.max(0, totalBaseFare - discountAmount);

        document.getElementById('totalFare').value = totalFare.toFixed(2);

        // Store discount amount for later use
        appState.discountAmount = discountAmount;

        calculateFinal();
    }

    // ========================================
    // CALCULATE FINAL AMOUNT (with mobile wallet tax)
    // ========================================
    function calculateFinal() {
        const fare = parseFloat(document.getElementById('totalFare').value) || 0;
        let tax = parseFloat(document.getElementById('tax').value) || 0;
        const taxInput = document.getElementById('tax');
        const taxLabel = document.getElementById('taxLabel');

        // Check if payment method is mobile_wallet - add 40 automatically
        const paymentMethod = document.getElementById('paymentMethod')?.value || 'cash';
        if (paymentMethod === 'mobile_wallet') {
            // Mobile wallet automatically adds 40 as tax
            tax = 40;
            taxInput.value = '40';
            taxInput.readOnly = true;
            taxInput.style.backgroundColor = '#f0f0f0';
            if (taxLabel) {
                taxLabel.textContent = '(Auto: PKR 40 for Mobile Wallet)';
                taxLabel.classList.remove('text-muted');
                taxLabel.classList.add('text-info', 'fw-bold');
            }
        } else {
            // Allow manual tax entry for other payment methods
            taxInput.readOnly = false;
            taxInput.style.backgroundColor = '';
            if (taxLabel) {
                taxLabel.textContent = '(Auto: PKR 40 for Mobile Wallet)';
                taxLabel.classList.add('text-muted');
                taxLabel.classList.remove('text-info', 'fw-bold');
            }
        }

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
</script>
