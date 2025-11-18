{{-- Print Functions for Booking Console --}}
{{-- All print-related functions are managed here for better code organization --}}

function printBooking(bookingId, ticketType = null) {
            if (!bookingId) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Booking',
                    text: 'Booking ID is missing.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Default behavior: print customer ticket
            // If no ticket type is specified, print customer ticket
            if (!ticketType || ticketType === 'both') {
                printTicket(bookingId);
                return;
            }

            // If ticket type is specified (customer or host), print single ticket
            if (ticketType) {
                try {
                    const printWindow = window.open(`/admin/bookings/${bookingId}/print/${ticketType}/80mm`,
                        '_blank');

                    if (!printWindow) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Popup Blocked',
                            text: 'Please allow popups for this site to print the booking ticket.',
                            confirmButtonColor: '#3085d6'
                        });
                    }
                } catch (error) {
                    console.error('Error opening print window:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Print Error',
                        text: 'Failed to open print window. Please try again.',
                        confirmButtonColor: '#d33'
                    });
                }
            }
        }

        // Function to print customer ticket
        function printTicket(bookingId) {
            if (!bookingId) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Invalid Booking',
                        text: 'Booking ID is missing.',
                        confirmButtonColor: '#d33'
                    });
                } else {
                    alert('Booking ID is missing.');
                }
                return;
            }

            try {
                // Open print window with customer ticket (always 80mm)
                const ticketUrl = `/admin/bookings/${bookingId}/print/customer/80mm`;
                const printWindow = window.open(ticketUrl, 'ticket');

                // Check if window was blocked
                if (!printWindow) {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Popup Blocked',
                            text: 'Please allow popups for this site to print ticket. Click the browser\'s popup blocker icon and allow popups.',
                            confirmButtonColor: '#3085d6'
                        });
                    } else {
                        alert('Popup blocked. Please allow popups for this site.');
                    }
                    return;
                }
            } catch (error) {
                console.error('Error opening print window:', error);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Print Error',
                        text: 'Failed to open print window. Please try again.',
                        confirmButtonColor: '#d33'
                    });
                } else {
                    alert('Failed to open print window. Please try again.');
                }
            }
        }

        // Legacy function name for backward compatibility - now prints single ticket
        function printBothTickets(bookingId) {
            printTicket(bookingId);
        }

        // Define printVoucher function for police records
        window.printVoucher = function() {
            // Get trip data from Livewire component
            const tripData = $wire.get('tripDataForJs') || null;
            
            if (!tripData || !tripData.id) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No trip selected. Please select a trip first.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Get trip passengers to check if there are any confirmed bookings
            let tripPassengers = $wire.get('tripPassengers') || [];
            tripPassengers = tripPassengers.filter(p => p.status === 'confirmed');

            if (!tripPassengers || tripPassengers.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No confirmed passengers found to print voucher.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Open the motorway voucher route
            const voucherUrl = `/admin/trips/${tripData.id}/motorway-voucher`;
            const printWindow = window.open(voucherUrl, '_blank');

            if (!printWindow) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Popup Blocked',
                    text: 'Please allow popups for this site to print the voucher.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
        };

        // Define printPassengerList directly on window object
        window.printPassengerList = function() {
            // Get trip data from Livewire component
            const tripData = $wire.get('tripDataForJs') || null;
            
            if (!tripData || !tripData.id) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No trip selected. Please select a trip first.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Get trip passengers to check if there are any confirmed bookings
            let tripPassengers = $wire.get('tripPassengers') || [];
            tripPassengers = tripPassengers.filter(p => p.status === 'confirmed');

            if (!tripPassengers || tripPassengers.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No confirmed passengers found to print report.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Open the head office report route
            const reportUrl = `/admin/trips/${tripData.id}/head-office-report`;
            const printWindow = window.open(reportUrl, '_blank');

            if (!printWindow) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Popup Blocked',
                    text: 'Please allow popups for this site to print the report.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }
        };

// Make printBooking, printTicket, and printBothTickets available globally
window.printBooking = printBooking;
window.printTicket = printTicket;
window.printBothTickets = printBothTickets; // Legacy function for backward compatibility

