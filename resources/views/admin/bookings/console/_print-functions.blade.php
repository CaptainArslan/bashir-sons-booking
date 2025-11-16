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
            // Get current data from Livewire component dynamically
            const tripPassengers = $wire.get('tripPassengers') || [];
            const tripData = $wire.get('tripDataForJs') || null;
            const travelDate = $wire.get('travelDate') || '';
            const routeData = $wire.get('routeData') || null;
            const fromStop = $wire.get('fromStop') || null;
            const toStop = $wire.get('toStop') || null;
            const tripStops = tripData?.stops || [];

            // Get departure and arrival times
            let departureTime = 'N/A';
            let arrivalTime = 'N/A';

            if (fromStop?.departure_at) {
                const depDate = new Date(fromStop.departure_at);
                departureTime = depDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            } else if (tripData?.departure_datetime) {
                const depDate = new Date(tripData.departure_datetime);
                departureTime = depDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }

            if (toStop?.arrival_at) {
                const arrDate = new Date(toStop.arrival_at);
                arrivalTime = arrDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            } else if (tripData?.estimated_arrival_datetime) {
                const arrDate = new Date(tripData.estimated_arrival_datetime);
                arrivalTime = arrDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }

            // Extract host information from trip notes
            let hostInfo = null;
            if (tripData?.notes) {
                const hostMatch = tripData.notes.match(/Host:\s*([^(]+)(?:\s*\(([^)]+)\))?/i);
                if (hostMatch) {
                    hostInfo = {
                        name: hostMatch[1]?.trim() || 'N/A',
                        phone: hostMatch[2]?.trim() || null
                    };
                }
            }

            if (!tripPassengers || tripPassengers.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No passengers found to print voucher.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Create voucher content (NO fare information for police record)
            const voucherContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Passenger Voucher - Police Record</title>
                <style>
                    @media print {
                        @page {
                            margin: 1cm;
                            size: A4;
                        }
                    }
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 11px;
                        margin: 0;
                        padding: 20px;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 20px;
                        border-bottom: 3px solid #000;
                        padding-bottom: 10px;
                    }
                    .header h1 {
                        margin: 0;
                        font-size: 18px;
                        font-weight: bold;
                        text-transform: uppercase;
                    }
                    .header h2 {
                        margin: 5px 0;
                        font-size: 14px;
                        color: #666;
                    }
                    .trip-info {
                        margin-bottom: 15px;
                        padding: 10px;
                        background-color: #f5f5f5;
                        border: 1px solid #ddd;
                    }
                    .trip-info table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .trip-info td {
                        padding: 5px;
                        border: none;
                    }
                    .trip-info td:first-child {
                        font-weight: bold;
                        width: 150px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 10px;
                    }
                    th, td {
                        border: 1px solid #000;
                        padding: 6px;
                        text-align: left;
                    }
                    th {
                        background-color: #333;
                        color: #fff;
                        font-weight: bold;
                        text-align: center;
                    }
                    .text-center {
                        text-align: center;
                    }
                    .print-date {
                        margin-top: 20px;
                        font-size: 10px;
                        text-align: right;
                        color: #666;
                    }
                    .footer-note {
                        margin-top: 15px;
                        padding: 10px;
                        background-color: #fff3cd;
                        border: 1px solid #ffc107;
                        font-size: 10px;
                        text-align: center;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>PASSENGER VOUCHER</h1>
                    <h2>For Police Record - ${routeData?.name || 'N/A'}</h2>
                </div>
                
                <div class="trip-info">
                    <table>
                        <tr>
                            <td>Travel Date:</td>
                            <td><strong>${travelDate || 'N/A'}</strong></td>
                            <td>Route:</td>
                            <td><strong>${routeData?.name || 'N/A'}</strong></td>
                        </tr>
                        <tr>
                            <td>Departure Time:</td>
                            <td><strong>${departureTime}</strong></td>
                            <td>Arrival Time:</td>
                            <td><strong>${arrivalTime}</strong></td>
                        </tr>
                        ${fromStop && toStop ? `
                            <tr>
                                <td>From Terminal:</td>
                                <td><strong>${fromStop.terminal_name || 'N/A'} (${fromStop.terminal_code || 'N/A'})</strong></td>
                                <td>To Terminal:</td>
                                <td><strong>${toStop.terminal_name || 'N/A'} (${toStop.terminal_code || 'N/A'})</strong></td>
                            </tr>
                        ` : ''}
                        <tr>
                            <td>Total Passengers:</td>
                            <td><strong>${tripPassengers.length}</strong></td>
                            <td>Printed On:</td>
                            <td>${new Date().toLocaleString()}</td>
                        </tr>
                    </table>
                </div>
                
                <div class="trip-info" style="margin-top: 15px;">
                    <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Bus Information</h3>
                    <table>
                        <tr>
                            <td>Bus Name:</td>
                            <td><strong>${tripData?.bus ? (tripData.bus.name || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                            <td>Registration Number:</td>
                            <td><strong>${tripData?.bus ? (tripData.bus.registration_number || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                        </tr>
                        <tr>
                            <td>Bus Model:</td>
                            <td><strong>${tripData?.bus ? (tripData.bus.model || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                            <td>Total Seats:</td>
                            <td><strong>${tripData?.bus?.bus_layout ? (tripData.bus.bus_layout.total_seats || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                        </tr>
                    </table>
                </div>
                
                <div class="trip-info" style="margin-top: 15px;">
                    <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Driver Information</h3>
                    <table>
                        <tr>
                            <td>Driver Name:</td>
                            <td><strong>${tripData?.driver_name || 'N/A (Not Assigned)'}</strong></td>
                            <td>Driver Phone:</td>
                            <td><strong>${tripData?.driver_phone || 'N/A (Not Assigned)'}</strong></td>
                        </tr>
                        ${tripData?.driver_address ? `
                            <tr>
                                <td>Driver Address:</td>
                                <td colspan="3"><strong>${tripData.driver_address}</strong></td>
                            </tr>
                        ` : ''}
                        ${routeData ? `
                            <tr>
                                <td>Route:</td>
                                <td colspan="3"><strong>${routeData.name || 'N/A'}</strong></td>
                            </tr>
                        ` : ''}
                    </table>
                </div>
                
                <div class="trip-info" style="margin-top: 15px;">
                    <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Host/Hostess Information</h3>
                    <table>
                        <tr>
                            <td>Host Name:</td>
                            <td><strong>${hostInfo ? (hostInfo.name !== 'N/A' ? hostInfo.name : 'N/A (Not Assigned)') : 'N/A (Not Assigned)'}</strong></td>
                            <td>Host Phone:</td>
                            <td><strong>${hostInfo ? (hostInfo.phone || 'N/A (Not Assigned)') : 'N/A (Not Assigned)'}</strong></td>
                        </tr>
                    </table>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">#</th>
                            <th style="width: 12%;">Booking #</th>
                            <th style="width: 8%;">Seat</th>
                            <th style="width: 18%;">Passenger Name</th>
                            <th style="width: 18%;">CNIC</th>
                            <th style="width: 15%;">Phone</th>
                            <th style="width: 12%;">From</th>
                            <th style="width: 12%;">To</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tripPassengers.map((passenger, index) => `
                            <tr>
                                <td class="text-center">${index + 1}</td>
                                <td class="text-center"><strong>${passenger.booking_number || 'N/A'}</strong></td>
                                <td class="text-center"><strong>${passenger.seat_number || 'N/A'}</strong></td>
                                <td><strong>${passenger.name || 'N/A'}</strong></td>
                                <td>${passenger.cnic || 'N/A'}</td>
                                <td>${passenger.phone || 'N/A'}</td>
                                <td>${passenger.from_code || 'N/A'}</td>
                                <td>${passenger.to_code || 'N/A'}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
                
                <div class="footer-note">
                    <strong>NOTE:</strong> This voucher is for police record purposes only. No financial information is included.
                </div>
                
                <div class="print-date">
                    Printed on: ${new Date().toLocaleString()}
                </div>
            </body>
            </html>
        `;

            // Open print window
            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Popup Blocked',
                    text: 'Please allow popups for this site to print the voucher.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            printWindow.document.write(voucherContent);
            printWindow.document.close();

            // Wait for content to load, then print
            printWindow.onload = function() {
                setTimeout(function() {
                    printWindow.print();
                }, 250);
            };
        };

        // Define printPassengerList directly on window object
        window.printPassengerList = function() {
            const table = document.getElementById('passengerListTable');
            if (!table) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Passenger list table not found.',
                    confirmButtonColor: '#d33'
                });
                return;
            }

            // Get trip information dynamically from Livewire component
            const tripData = $wire.get('tripDataForJs') || null;
            const travelDate = $wire.get('travelDate') || '';
            const routeData = $wire.get('routeData') || null;
            const fromStop = $wire.get('fromStop') || null;
            const toStop = $wire.get('toStop') || null;
            const tripPassengers = $wire.get('tripPassengers') || [];
            const tripStops = tripData?.stops || [];
            const totalPassengers = tripPassengers.length;
            const totalEarnings = $wire.get('totalEarnings') || 0;

            console.log(tripData, 'tripData');
            console.log(travelDate, 'travelDate');
            console.log(routeData, 'routeData');
            console.log(fromStop, 'fromStop');
            console.log(toStop, 'toStop');
            console.log(tripPassengers, 'tripPassengers');
            console.log(tripStops, 'tripStops');
            console.log(totalPassengers, 'totalPassengers');
            console.log(totalEarnings, 'totalEarnings');

            // Get fare information
            const fareData = $wire.get('fareData');
            const baseFare = $wire.get('baseFare') || 0;
            const discountAmount = $wire.get('discountAmount') || 0;
            const taxAmount = $wire.get('taxAmount') || 0;

            // Extract host information from trip notes
            let hostInfo = null;
            if (tripData?.notes) {
                const hostMatch = tripData.notes.match(/Host:\s*([^(]+)(?:\s*\(([^)]+)\))?/i);
                if (hostMatch) {
                    hostInfo = {
                        name: hostMatch[1]?.trim() || 'N/A',
                        phone: hostMatch[2]?.trim() || null
                    };
                }
            }

            // Get departure and arrival times
            let departureTime = 'N/A';
            let arrivalTime = 'N/A';

            if (fromStop?.departure_at) {
                const depDate = new Date(fromStop.departure_at);
                departureTime = depDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            } else if (tripData?.departure_datetime) {
                const depDate = new Date(tripData.departure_datetime);
                departureTime = depDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }

            if (toStop?.arrival_at) {
                const arrDate = new Date(toStop.arrival_at);
                arrivalTime = arrDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            } else if (tripData?.estimated_arrival_datetime) {
                const arrDate = new Date(tripData.estimated_arrival_datetime);
                arrivalTime = arrDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }

            // Removed complete stop-to-stop information section as per user request

            // Create print window content
            const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Passenger List - Trip Report</title>
                <style>
                    @media print {
                        @page {
                            margin: 1cm;
                            size: A4 landscape;
                        }
                    }
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 12px;
                        margin: 0;
                        padding: 20px;
                    }
                    .header {
                        text-align: center;
                        margin-bottom: 20px;
                        border-bottom: 2px solid #000;
                        padding-bottom: 10px;
                    }
                    .header h1 {
                        margin: 0;
                        font-size: 20px;
                        font-weight: bold;
                    }
                    .header h2 {
                        margin: 5px 0;
                        font-size: 16px;
                    }
                    .trip-info {
                        margin-bottom: 15px;
                        padding: 10px;
                        background-color: #f5f5f5;
                        border: 1px solid #ddd;
                    }
                    .trip-info table {
                        width: 100%;
                        border-collapse: collapse;
                    }
                    .trip-info td {
                        padding: 5px;
                        border: none;
                    }
                    .trip-info td:first-child {
                        font-weight: bold;
                        width: 150px;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 10px;
                    }
                    th, td {
                        border: 1px solid #ddd;
                        padding: 8px;
                        text-align: left;
                    }
                    th {
                        background-color: #f8f9fa;
                        font-weight: bold;
                        text-align: center;
                    }
                    tfoot {
                        font-weight: bold;
                        background-color: #f8f9fa;
                    }
                    .badge {
                        padding: 3px 6px;
                        border-radius: 3px;
                        font-size: 10px;
                    }
                    .text-center {
                        text-align: center;
                    }
                    .text-end {
                        text-align: right;
                    }
                    .text-success {
                        color: #28a745;
                    }
                    .print-date {
                        margin-top: 20px;
                        font-size: 10px;
                        text-align: right;
                        color: #666;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>PASSENGER LIST REPORT</h1>
                    <h2>Trip Passenger Details</h2>
                </div>
                
                <div class="trip-info" style="margin-top: 0;">
                    <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Bus Information</h3>
                    <table>
                        <tr>
                            <td>Bus Name:</td>
                            <td><strong>${tripData?.bus ? (tripData.bus.name || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                            <td>Registration Number:</td>
                            <td><strong>${tripData?.bus ? (tripData.bus.registration_number || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                        </tr>
                        <tr>
                            <td>Bus Model:</td>
                            <td><strong>${tripData?.bus ? (tripData.bus.model || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                            <td>Total Seats:</td>
                            <td><strong>${tripData?.bus?.bus_layout ? (tripData.bus.bus_layout.total_seats || 'N/A') : 'N/A (Bus Not Assigned)'}</strong></td>
                        </tr>
                    </table>
                </div>
                
                <div class="trip-info" style="margin-top: 15px;">
                    <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Driver Information</h3>
                    <table>
                        <tr>
                            <td>Driver Name:</td>
                            <td><strong>${tripData?.driver_name || 'N/A (Not Assigned)'}</strong></td>
                            <td>Driver Phone:</td>
                            <td><strong>${tripData?.driver_phone || 'N/A (Not Assigned)'}</strong></td>
                        </tr>
                        ${tripData?.driver_address ? `
                            <tr>
                                <td>Driver Address:</td>
                                <td colspan="3"><strong>${tripData.driver_address}</strong></td>
                            </tr>
                        ` : ''}
                        ${routeData ? `
                            <tr>
                                <td>Route:</td>
                                <td colspan="3"><strong>${routeData.name || 'N/A'}</strong></td>
                            </tr>
                        ` : ''}
                    </table>
                </div>
                
                <div class="trip-info" style="margin-top: 15px;">
                    <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Host/Hostess Information</h3>
                    <table>
                        <tr>
                            <td>Host Name:</td>
                            <td><strong>${hostInfo ? (hostInfo.name !== 'N/A' ? hostInfo.name : 'N/A (Not Assigned)') : 'N/A (Not Assigned)'}</strong></td>
                            <td>Host Phone:</td>
                            <td><strong>${hostInfo ? (hostInfo.phone || 'N/A (Not Assigned)') : 'N/A (Not Assigned)'}</strong></td>
                        </tr>
                    </table>
                </div>
                
                <div class="trip-info" style="margin-top: 15px;">
                    <h3 style="font-size: 12px; margin-bottom: 8px; font-weight: bold; text-transform: uppercase;">Fare Information</h3>
                    <table>
                        <tr>
                            <td>Base Fare (Per Seat):</td>
                            <td><strong>PKR ${parseFloat(baseFare || 0).toFixed(2)}</strong></td>
                            <td>Discount (Per Seat):</td>
                            <td><strong>PKR ${parseFloat(discountAmount || 0).toFixed(2)}</strong></td>
                        </tr>
                        <tr>
                            <td>Tax/Charges:</td>
                            <td><strong>PKR ${parseFloat(taxAmount || 0).toFixed(2)}</strong></td>
                            <td>Total Earnings:</td>
                            <td class="text-success"><strong>PKR ${parseFloat(totalEarnings || 0).toFixed(2)}</strong></td>
                        </tr>
                        ${fareData && fareData.from_terminal ? `
                            <tr>
                                <td>Fare Route:</td>
                                <td colspan="3"><strong>${fareData.from_terminal.name || 'N/A'} (${fareData.from_terminal.code || 'N/A'}) → ${fareData.to_terminal?.name || 'N/A'} (${fareData.to_terminal?.code || 'N/A'})</strong></td>
                            </tr>
                        ` : ''}
                    </table>
                </div>
                
                <h3 style="font-size: 14px; margin-bottom: 10px;">Passenger Details</h3>
                ${(() => {
                    // Clone the table and remove the Actions column (last column)
                    const clonedTable = table.cloneNode(true);
                    const rows = clonedTable.querySelectorAll('tr');
                    rows.forEach(row => {
                        const cells = row.querySelectorAll('th, td');
                        if (cells.length > 0) {
                            // Remove the last cell (Actions column)
                            cells[cells.length - 1].remove();
                        }
                    });
                    // Update footer colspan if exists
                    const footerRow = clonedTable.querySelector('tfoot tr');
                    if (footerRow) {
                        const footerCells = footerRow.querySelectorAll('td');
                        if (footerCells.length > 0) {
                            // Update the last footer cell colspan
                            const lastCell = footerCells[footerCells.length - 1];
                            if (lastCell.hasAttribute('colspan')) {
                                lastCell.setAttribute('colspan', '2');
                            }
                        }
                    }
                    return clonedTable.outerHTML;
                })()}
                
                <div class="print-date">
                    Printed on: ${new Date().toLocaleString()}
                </div>
            </body>
            </html>
        `;

            // Open print window
            const printWindow = window.open('', '_blank');
            if (!printWindow) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Popup Blocked',
                    text: 'Please allow popups for this site to print the passenger list.',
                    confirmButtonColor: '#3085d6'
                });
                return;
            }

            printWindow.document.write(printContent);
            printWindow.document.close();

            // Wait for content to load, then print
            printWindow.onload = function() {
                setTimeout(function() {
                    printWindow.print();
                }, 250);
            };
        };

// Make printBooking, printTicket, and printBothTickets available globally
window.printBooking = printBooking;
window.printTicket = printTicket;
window.printBothTickets = printBothTickets; // Legacy function for backward compatibility

