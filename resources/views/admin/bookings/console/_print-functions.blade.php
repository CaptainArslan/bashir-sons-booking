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
            // Get current user name
            const currentUserName = '{{ Auth::user()->name ?? "N/A" }}';
            
            // Get company name from settings
            const companyName = '{{ \App\Models\GeneralSetting::first()?->company_name ?? "Bashir Sons Group" }}';
            
            // Get current data from Livewire component dynamically
            let tripPassengers = $wire.get('tripPassengers') || [];
            
            // Filter to only include confirmed bookings (exclude hold, cancelled, expired)
            tripPassengers = tripPassengers.filter(p => p.status === 'confirmed');
            
            const tripData = $wire.get('tripDataForJs') || null;
            const travelDate = $wire.get('travelDate') || '';
            const routeData = $wire.get('routeData') || null;
            const fromStop = $wire.get('fromStop') || null;
            const toStop = $wire.get('toStop') || null;
            const tripStops = tripData?.stops || [];
            
            const totalPassengers = tripPassengers.length;

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

            // Get departure and arrival times
            let departureDate = travelDate || 'N/A';
            
            if (fromStop?.departure_at) {
                const depDate = new Date(fromStop.departure_at);
                departureTime = depDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                departureDate = depDate.toLocaleDateString('en-CA'); // YYYY-MM-DD format
            } else if (tripData?.departure_datetime) {
                const depDate = new Date(tripData.departure_datetime);
                departureTime = depDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                departureDate = depDate.toLocaleDateString('en-CA');
            }

            // Format route code (FROM_CODE-TO_CODE)
            const routeCode = (fromStop?.terminal_code || '') + '-' + (toStop?.terminal_code || '');
            
            // Get vehicle registration
            const vehicleNo = tripData?.bus?.registration_number || 'N/A';
            
            // Get voucher number (use trip ID - this is the trip number)
            const voucherNo = tripData?.id ? `Trip #${tripData.id}` : 'N/A';
            
            // Format payment method for display (for Via column)
            const formatPaymentMethod = (method, channel) => {
                if (!method && !channel) return 'N/A';
                if (channel === 'online') {
                    if (method === 'mobile_wallet') return 'Mobile Wallet (Online)';
                    if (method === 'card') return 'Credit/Debit Card (Online)';
                    if (method === 'bank_transfer') return 'Bank Transfer (Online)';
                    return 'Online Payment';
                }
                if (method === 'cash') return 'Cash';
                if (method === 'card') return 'Credit / Debit Card';
                if (method === 'mobile_wallet') return 'Mobile Wallet (JazzCash/Easypaisa)';
                if (method === 'bank_transfer') return 'Bank Transfer';
                if (method === 'other') return 'Other';
                if (channel === 'counter') return 'Cash (Counter)';
                if (channel === 'phone') return 'Cash (Phone)';
                return 'Cash';
            };

            // Create voucher content (NO fare or financial information for police record)
            const voucherContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Motorway Police Voucher - ${routeCode}</title>
                <style>
                    @media print {
                        @page {
                            margin: 0.5cm;
                            size: A4 landscape;
                        }
                    }
                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        font-size: 10px;
                        margin: 0;
                        padding: 10px;
                        background: #fff;
                    }
                    .header-section {
                        display: grid;
                        grid-template-columns: 1fr 1fr 1fr;
                        gap: 10px;
                        margin-bottom: 8px;
                        border-bottom: 1px solid #000;
                        padding-bottom: 5px;
                        background: linear-gradient(to bottom, #f8f9fa, #fff);
                    }
                    .header-left {
                        text-align: left;
                    }
                    .header-center {
                        text-align: center;
                    }
                    .header-right {
                        text-align: right;
                    }
                    .service-name {
                        font-size: 16px;
                        font-weight: bold;
                        margin-bottom: 8px;
                        color: #1a1a1a;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                    }
                    .header-info {
                        font-size: 9px;
                        line-height: 1.6;
                    }
                    .header-info strong {
                        font-weight: bold;
                        color: #333;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 5px;
                        font-size: 8px;
                    }
                    th, td {
                        border: 0.5px solid #333;
                        padding: 2px 3px;
                        text-align: left;
                    }
                    th {
                        background-color: #2c3e50;
                        color: #fff;
                        font-weight: bold;
                        text-align: center;
                        font-size: 7px;
                        padding: 3px 2px;
                    }
                    .passenger-table th {
                        font-size: 7px;
                        background-color: #34495e;
                        padding: 2px;
                    }
                    .passenger-table td {
                        font-size: 7px;
                        padding: 2px;
                    }
                    .passenger-table tbody tr:nth-child(even) {
                        background-color: #f8f9fa;
                    }
                    .passenger-table tbody tr:hover {
                        background-color: #e8f4f8;
                    }
                    .booking-summary-container {
                        display: grid;
                        grid-template-columns: 1fr 0.4fr;
                        gap: 8px;
                        margin-top: 8px;
                    }
                    .quick-summary-box {
                        border: 1px solid #2c3e50;
                        border-radius: 2px;
                        overflow: hidden;
                        background: #fff;
                    }
                    .quick-summary-header {
                        background-color: #2c3e50;
                        color: #fff;
                        padding: 4px;
                        text-align: center;
                        font-weight: bold;
                        font-size: 9px;
                        text-transform: uppercase;
                    }
                    .quick-summary-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 8px;
                    }
                    .quick-summary-table td {
                        padding: 3px 4px;
                        border-bottom: 0.5px solid #e0e0e0;
                    }
                    .quick-summary-table td:first-child {
                        font-weight: 600;
                        background-color: #f8f9fa;
                        width: 45%;
                        color: #333;
                    }
                    .quick-summary-table td:last-child {
                        text-align: right;
                        font-weight: bold;
                        color: #2c3e50;
                    }
                    .quick-summary-table tr:last-child td {
                        border-bottom: none;
                    }
                    .text-right {
                        text-align: right;
                    }
                    .text-center {
                        text-align: center;
                    }
                    .print-date {
                        margin-top: 8px;
                        font-size: 8px;
                        text-align: right;
                        color: #666;
                        font-style: italic;
                    }
                    .report-title {
                        text-align: center;
                        font-size: 16px;
                        font-weight: bold;
                        margin-bottom: 10px;
                        padding: 5px 0;
                        color: #000;
                        text-transform: uppercase;
                        border-bottom: 2px solid #000;
                    }
                    .footer-note {
                        margin-top: 8px;
                        padding: 5px;
                        background-color: #fff3cd;
                        border: 1px solid #ffc107;
                        font-size: 8px;
                        text-align: center;
                        border-radius: 2px;
                    }
                </style>
            </head>
            <body>
                <div class="report-title">MOTORWAY POLICE VOUCHER</div>
                
                <div class="header-section">
                    <div class="header-left">
                        <div class="service-name">${companyName}</div>
                        <div class="header-info">
                            <div><strong>Route:</strong> ${routeCode}</div>
                            <div><strong>Departure Time:</strong> ${departureTime}</div>
                            <div><strong>Date:</strong> ${departureDate}</div>
                        </div>
                    </div>
                    <div class="header-center">
                        <div class="header-info">
                            <div><strong>Vehicle No.:</strong> ${vehicleNo}</div>
                            <div><strong>ARV/DEP:</strong> ${arrivalTime} DEP: ${departureTime}</div>
                            <div><strong>Voucher No.:</strong> ${voucherNo}</div>
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="header-info">
                            <div><strong>Driver:</strong> ${tripData?.driver_name || 'N/A'}</div>
                            <div><strong>Host:</strong> ${hostInfo?.name || 'N/A'}</div>
                        </div>
                    </div>
                </div>
                
                <table class="passenger-table">
                    <thead>
                        <tr>
                            <th style="width: 6%;">Seat</th>
                            <th style="width: 15%;">Name</th>
                            <th style="width: 18%;">CNIC</th>
                            <th style="width: 12%;">Cell</th>
                            <th style="width: 15%;">By</th>
                            <th style="width: 9%;">From</th>
                            <th style="width: 9%;">Desti.</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tripPassengers.map((passenger, index) => {
                            const passengerName = passenger.name || 'N/A';
                            return `
                                <tr>
                                    <td class="text-center"><strong>${passenger.seat_number || 'N/A'}</strong></td>
                                    <td>${passengerName}</td>
                                    <td>${passenger.cnic || 'N/A'}</td>
                                    <td>${passenger.phone || 'N/A'}</td>
                                    <td>${passenger.agent_name || 'N/A'}</td>
                                    <td class="text-center">${passenger.from_code || 'N/A'}</td>
                                    <td class="text-center">${passenger.to_code || 'N/A'}</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
                
                <div class="booking-summary-container">
                    <div></div>
                    <div class="quick-summary-box">
                        <div class="quick-summary-header">Trip Summary</div>
                        <table class="quick-summary-table">
                            <tbody>
                                <tr>
                                    <td>Total Passengers:</td>
                                    <td><strong>${totalPassengers}</strong></td>
                                </tr>
                                <tr>
                                    <td>Route:</td>
                                    <td><strong>${routeCode}</strong></td>
                                </tr>
                                <tr>
                                    <td>From Terminal:</td>
                                    <td><strong>${fromStop?.terminal_name || fromStop?.terminal_code || 'N/A'}</strong></td>
                                </tr>
                                <tr>
                                    <td>To Terminal:</td>
                                    <td><strong>${toStop?.terminal_name || toStop?.terminal_code || 'N/A'}</strong></td>
                                </tr>
                                <tr>
                                    <td>Departure Date:</td>
                                    <td><strong>${departureDate}</strong></td>
                                </tr>
                                <tr>
                                    <td>Departure Time:</td>
                                    <td><strong>${departureTime}</strong></td>
                                </tr>
                                <tr>
                                    <td>Arrival Time:</td>
                                    <td><strong>${arrivalTime}</strong></td>
                                </tr>
                                <tr>
                                    <td>Vehicle No.:</td>
                                    <td><strong>${vehicleNo}</strong></td>
                                </tr>
                                <tr>
                                    <td>Driver:</td>
                                    <td><strong>${tripData?.driver_name || 'N/A'}</strong></td>
                                </tr>
                                <tr>
                                    <td>Host:</td>
                                    <td><strong>${hostInfo?.name || 'N/A'}</strong></td>
                                </tr>
                                <tr>
                                    <td>Voucher No.:</td>
                                    <td><strong>${voucherNo}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="footer-note">
                    <strong>NOTE:</strong> This voucher is for Motorway Police record purposes only. No financial information is included.
                </div>
                
                <div class="print-date">
                    Printed on: ${new Date().toLocaleString()} | Generated by: ${currentUserName}
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
            // Get current user name
            const currentUserName = '{{ Auth::user()->name ?? "N/A" }}';
            
            // Get company name from settings
            const companyName = '{{ \App\Models\GeneralSetting::first()?->company_name ?? "Bashir Sons Group" }}';
            
            // Get trip information dynamically from Livewire component
            const tripData = $wire.get('tripDataForJs') || null;
            const travelDate = $wire.get('travelDate') || '';
            const routeData = $wire.get('routeData') || null;
            const fromStop = $wire.get('fromStop') || null;
            const toStop = $wire.get('toStop') || null;
            let tripPassengers = $wire.get('tripPassengers') || [];
            
            // Filter to only include confirmed bookings (exclude hold, cancelled, expired)
            tripPassengers = tripPassengers.filter(p => p.status === 'confirmed');
            
            const totalPassengers = tripPassengers.length;
            
            // Recalculate total earnings from confirmed bookings only
            const totalEarnings = tripPassengers.reduce((sum, p) => sum + (parseFloat(p.final_amount) || 0), 0);

            if (!tripPassengers || tripPassengers.length === 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No passengers found to print.',
                    confirmButtonColor: '#d33'
                });
                return;
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

            // Get departure and arrival times
            let departureTime = 'N/A';
            let arrivalTime = 'N/A';
            let departureDate = travelDate || 'N/A';

            if (fromStop?.departure_at) {
                const depDate = new Date(fromStop.departure_at);
                departureTime = depDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                departureDate = depDate.toLocaleDateString('en-CA'); // YYYY-MM-DD format
            } else if (tripData?.departure_datetime) {
                const depDate = new Date(tripData.departure_datetime);
                departureTime = depDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
                departureDate = depDate.toLocaleDateString('en-CA');
            }

            if (toStop?.arrival_at) {
                const arrDate = new Date(toStop.arrival_at);
                arrivalTime = arrDate.toLocaleTimeString('en-US', {
                    hour: '2-digit',
                    minute: '2-digit',
                    hour12: true
                });
            }

            // Format route code (FROM_CODE-TO_CODE)
            const routeCode = (fromStop?.terminal_code || '') + '-' + (toStop?.terminal_code || '');
            
            // Get vehicle registration
            const vehicleNo = tripData?.bus?.registration_number || 'N/A';
            
            // Get voucher number (use trip ID - this is the trip number)
            const voucherNo = tripData?.id ? `Trip #${tripData.id}` : 'N/A';
            
            // Get expenses from trip data
            const expenses = tripData?.expenses || [];
            
            // Group expenses by terminal and type
            const expensesByTerminal = {};
            const expenseTypeTotals = {};
            
            expenses.forEach(expense => {
                const amount = parseFloat(expense.amount) || 0;
                const fromTerminal = expense.from_terminal?.code || expense.from_terminal?.name || 'N/A';
                const toTerminal = expense.to_terminal?.code || expense.to_terminal?.name || 'N/A';
                const terminalKey = `${fromTerminal}${toTerminal !== 'N/A' ? ' → ' + toTerminal : ''}`;
                const expenseType = expense.expense_type_label || expense.expense_type || 'Other';
                
                // Group by terminal
                if (!expensesByTerminal[terminalKey]) {
                    expensesByTerminal[terminalKey] = [];
                }
                expensesByTerminal[terminalKey].push({
                    type: expenseType,
                    amount: amount,
                    description: expense.description || ''
                });
                
                // Calculate totals by expense type
                if (!expenseTypeTotals[expenseType]) {
                    expenseTypeTotals[expenseType] = 0;
                }
                expenseTypeTotals[expenseType] += amount;
            });
            
            // Calculate expense totals
            let addaExpense = expenseTypeTotals['Commission'] || 0;
            let hakriExpense = expenseTypeTotals['Ghakri'] || 0;
            let otherExpense = Object.keys(expenseTypeTotals)
                .filter(type => type !== 'Commission' && type !== 'Ghakri')
                .reduce((sum, type) => sum + expenseTypeTotals[type], 0);
            
            const totalExpenses = Math.round(addaExpense + hakriExpense + otherExpense);
            
            // Format payment method for display with complete names
            const formatPaymentMethod = (method, channel) => {
                // Handle null/undefined
                if (!method && !channel) return 'N/A';
                
                // If channel is online, show online payment method
                if (channel === 'online') {
                    if (method === 'mobile_wallet') return 'Mobile Wallet (Online)';
                    if (method === 'card') return 'Credit/Debit Card (Online)';
                    if (method === 'bank_transfer') return 'Bank Transfer (Online)';
                    return 'Online Payment';
                }
                
                // Format based on payment method
                if (method === 'cash') return 'Cash';
                if (method === 'card') return 'Credit / Debit Card';
                if (method === 'mobile_wallet') return 'Mobile Wallet (JazzCash/Easypaisa)';
                if (method === 'bank_transfer') return 'Bank Transfer';
                if (method === 'other') return 'Other';
                
                // Fallback based on channel
                if (channel === 'counter') return 'Cash (Counter)';
                if (channel === 'phone') return 'Cash (Phone)';
                
                return 'Cash'; // Default
            };
            
            // Format booking type/channel
            const formatBookingType = (channel) => {
                if (channel === 'counter') return 'Counter';
                if (channel === 'phone') return 'Phone';
                if (channel === 'online') return 'Online';
                return channel || 'Counter';
            };
            
            // Calculate payment method breakdowns (by actual payment method, not formatted)
            const paymentMethodBreakdown = {
                'cash': { count: 0, total: 0 },
                'mobile_wallet': { count: 0, total: 0 },
                'bank_transfer': { count: 0, total: 0 },
                'card': { count: 0, total: 0 },
                'other': { count: 0, total: 0 }
            };
            
            // Group passengers by payment method (Via) for summary
            const viaBreakdown = {};
            const bookingTypeBreakdown = {};
            
            tripPassengers.forEach(passenger => {
                const paymentMethod = formatPaymentMethod(passenger.payment_method, passenger.channel);
                const bookingType = formatBookingType(passenger.channel);
                const amount = parseFloat(passenger.final_amount) || 0;
                const actualMethod = passenger.payment_method || 'cash';
                
                // Payment method breakdown (by actual method)
                if (paymentMethodBreakdown[actualMethod]) {
                    paymentMethodBreakdown[actualMethod].count++;
                    paymentMethodBreakdown[actualMethod].total += amount;
                } else {
                    paymentMethodBreakdown['other'].count++;
                    paymentMethodBreakdown['other'].total += amount;
                }
                
                // Via breakdown (formatted)
                if (!viaBreakdown[paymentMethod]) {
                    viaBreakdown[paymentMethod] = { count: 0, total: 0 };
                }
                viaBreakdown[paymentMethod].count++;
                viaBreakdown[paymentMethod].total += amount;
                
                // Booking type breakdown
                if (!bookingTypeBreakdown[bookingType]) {
                    bookingTypeBreakdown[bookingType] = { count: 0, total: 0 };
                }
                bookingTypeBreakdown[bookingType].count++;
                bookingTypeBreakdown[bookingType].total += amount;
            });
            
            // Calculate totals
            const cashTotal = paymentMethodBreakdown['cash'].total;
            const mobileWalletTotal = paymentMethodBreakdown['mobile_wallet'].total;
            const bankTransferTotal = paymentMethodBreakdown['bank_transfer'].total;
            const cardTotal = paymentMethodBreakdown['card'].total;
            const otherTotal = paymentMethodBreakdown['other'].total;
            
            // Calculate online payments total (mobile wallet + bank transfer + card when online)
            const onlineTotal = tripPassengers
                .filter(p => p.payment_method === 'mobile_wallet' || p.payment_method === 'bank_transfer' || 
                            p.payment_method === 'card' || p.channel === 'online')
                .reduce((sum, p) => sum + (parseFloat(p.final_amount) || 0), 0);
            
            // Calculate cash in hand (cash payments - expenses)
            const cashInHand = Math.round(cashTotal) - totalExpenses;
            
            // Calculate balance (total earnings - expenses - online payments)
            const balance = Math.round(totalEarnings) - totalExpenses - Math.round(onlineTotal);
            
            // Group passengers by agent for fare distribution
            const agentFares = {};
            tripPassengers.forEach(passenger => {
                const agent = passenger.agent_name || 'N/A';
                if (!agentFares[agent]) {
                    agentFares[agent] = 0;
                }
                agentFares[agent] += parseFloat(passenger.final_amount) || 0;
            });
            
            // Get unique agents
            const agents = Object.keys(agentFares).filter(a => a !== 'N/A').sort();
            
            // Format fare (show as integer without decimals)
            const formatFare = (amount) => {
                return Math.round(parseFloat(amount || 0)).toLocaleString();
            };

            // Create print window content
            const printContent = `
            <!DOCTYPE html>
            <html>
            <head>
                <title>Head Office Report - ${routeCode}</title>
                <style>
                    @media print {
                        @page {
                            margin: 0.5cm;
                            size: A4 landscape;
                        }
                    }
                    body {
                        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
                        font-size: 10px;
                        margin: 0;
                        padding: 10px;
                        background: #fff;
                    }
                    .header-section {
                        display: grid;
                        grid-template-columns: 1fr 1fr 1fr;
                        gap: 10px;
                        margin-bottom: 8px;
                        border-bottom: 1px solid #000;
                        padding-bottom: 5px;
                        background: linear-gradient(to bottom, #f8f9fa, #fff);
                    }
                    .header-left {
                        text-align: left;
                    }
                    .header-center {
                        text-align: center;
                    }
                    .header-right {
                        text-align: right;
                    }
                    .service-name {
                        font-size: 16px;
                        font-weight: bold;
                        margin-bottom: 8px;
                        color: #1a1a1a;
                        text-transform: uppercase;
                        letter-spacing: 1px;
                    }
                    .header-info {
                        font-size: 9px;
                        line-height: 1.6;
                    }
                    .header-info strong {
                        font-weight: bold;
                        color: #333;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 5px;
                        font-size: 8px;
                    }
                    th, td {
                        border: 0.5px solid #333;
                        padding: 2px 3px;
                        text-align: left;
                    }
                    th {
                        background-color: #2c3e50;
                        color: #fff;
                        font-weight: bold;
                        text-align: center;
                        font-size: 7px;
                        padding: 3px 2px;
                    }
                    .passenger-table th {
                        font-size: 7px;
                        background-color: #34495e;
                        padding: 2px;
                    }
                    .passenger-table td {
                        font-size: 7px;
                        padding: 2px;
                    }
                    .passenger-table tbody tr:nth-child(even) {
                        background-color: #f8f9fa;
                    }
                    .passenger-table tbody tr:hover {
                        background-color: #e8f4f8;
                    }
                    .financial-summary {
                        display: grid;
                        grid-template-columns: 1fr 1fr;
                        gap: 8px;
                        margin-top: 8px;
                    }
                    .booking-summary-container {
                        display: grid;
                        grid-template-columns: 1fr 0.4fr;
                        gap: 8px;
                        margin-top: 8px;
                    }
                    .quick-summary-box {
                        border: 1px solid #2c3e50;
                        border-radius: 2px;
                        overflow: hidden;
                        background: #fff;
                    }
                    .quick-summary-header {
                        background-color: #2c3e50;
                        color: #fff;
                        padding: 4px;
                        text-align: center;
                        font-weight: bold;
                        font-size: 9px;
                        text-transform: uppercase;
                    }
                    .quick-summary-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 8px;
                    }
                    .quick-summary-table td {
                        padding: 3px 4px;
                        border-bottom: 0.5px solid #e0e0e0;
                    }
                    .quick-summary-table td:first-child {
                        font-weight: 600;
                        background-color: #f8f9fa;
                        width: 45%;
                        color: #333;
                    }
                    .quick-summary-table td:last-child {
                        text-align: right;
                        font-weight: bold;
                        color: #2c3e50;
                    }
                    .quick-summary-table tr:last-child td {
                        border-bottom: none;
                    }
                    .summary-section {
                        border: 1px solid #2c3e50;
                        border-radius: 2px;
                        overflow: hidden;
                    }
                    .summary-header {
                        background-color: #2c3e50;
                        color: #fff;
                        padding: 4px;
                        text-align: center;
                        font-weight: bold;
                        font-size: 9px;
                        text-transform: uppercase;
                    }
                    .summary-table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 8px;
                    }
                    .summary-table th {
                        background-color: #2c3e50;
                        color: #fff;
                        padding: 3px 4px;
                        font-weight: bold;
                        border: 0.5px solid #333;
                        font-size: 7px;
                    }
                    .summary-table td {
                        padding: 3px 4px;
                        border: 0.5px solid #ddd;
                    }
                    .summary-table td:first-child {
                        font-weight: 600;
                        background-color: #f8f9fa;
                        width: 50%;
                    }
                    .summary-table td:last-child {
                        text-align: right;
                        font-weight: bold;
                        color: #2c3e50;
                    }
                    .summary-table tr:last-child td {
                        border-top: 2px solid #2c3e50;
                        background-color: #e8f4f8;
                        font-weight: bold;
                    }
                    .terminal-count-box {
                        border: 2px solid #2c3e50;
                        border-radius: 4px;
                        padding: 10px;
                        text-align: center;
                        font-size: 10px;
                        background: linear-gradient(to bottom, #f8f9fa, #fff);
                        margin-bottom: 10px;
                    }
                    .terminal-count-box div:first-child {
                        font-weight: bold;
                        font-size: 12px;
                        color: #2c3e50;
                        margin-bottom: 5px;
                    }
                    .terminal-count-box div:nth-child(2) {
                        font-weight: bold;
                        font-size: 12px;
                        color: #2c3e50;
                        margin-bottom: 10px;
                    }
                    .terminal-count-box div:last-child {
                        font-size: 18px;
                        font-weight: bold;
                        color: #27ae60;
                        margin-top: 5px;
                    }
                    .agent-table {
                        margin-top: 10px;
                        border: 2px solid #2c3e50;
                    }
                    .agent-table th {
                        background-color: #2c3e50;
                        color: #fff;
                        padding: 6px;
                        font-size: 9px;
                    }
                    .agent-table td {
                        padding: 5px;
                        text-align: right;
                    }
                    .agent-table tbody tr:last-child {
                        background-color: #e8f4f8;
                        font-weight: bold;
                    }
                    .via-summary-table {
                        margin-top: 8px;
                        border: 1px solid #2c3e50;
                    }
                    .via-summary-table th {
                        background-color: #34495e;
                        color: #fff;
                        padding: 3px 4px;
                        font-size: 7px;
                    }
                    .via-summary-table td {
                        padding: 3px 4px;
                        text-align: right;
                        font-size: 7px;
                    }
                    .expenses-table th {
                        background-color: #34495e;
                        color: #fff;
                        padding: 3px 4px;
                        font-size: 7px;
                    }
                    .expenses-table td {
                        padding: 3px 4px;
                        font-size: 7px;
                    }
                    .expenses-table tbody tr:last-child {
                        background-color: #e8f4f8;
                        font-weight: bold;
                    }
                    .text-right {
                        text-align: right;
                    }
                    .text-center {
                        text-align: center;
                    }
                    .print-date {
                        margin-top: 8px;
                        font-size: 8px;
                        text-align: right;
                        color: #666;
                        font-style: italic;
                    }
                    .report-title {
                        text-align: center;
                        font-size: 16px;
                        font-weight: bold;
                        margin-bottom: 10px;
                        padding: 5px 0;
                        color: #000;
                        text-transform: uppercase;
                        border-bottom: 2px solid #000;
                    }
                </style>
            </head>
            <body>
                <div class="report-title">HEAD OFFICE REPORT</div>
                
                <div class="header-section">
                    <div class="header-left">
                        <div class="service-name">${companyName}</div>
                        <div class="header-info">
                            <div><strong>Route:</strong> ${routeCode}</div>
                            <div><strong>Departure Time:</strong> ${departureTime}</div>
                            <div><strong>Date:</strong> ${departureDate}</div>
                        </div>
                    </div>
                    <div class="header-center">
                        <div class="header-info">
                            <div><strong>Vehicle No.:</strong> ${vehicleNo}</div>
                            <div><strong>ARV/DEP:</strong> ${arrivalTime} DEP: ${departureTime}</div>
                            <div><strong>Voucher No.:</strong> ${voucherNo}</div>
                        </div>
                    </div>
                    <div class="header-right">
                        <div class="header-info">
                            <div><strong>Driver:</strong> ${tripData?.driver_name || 'N/A'}</div>
                            <div><strong>Host:</strong> ${hostInfo?.name || 'N/A'}</div>
                        </div>
                    </div>
                </div>
                
                <table class="passenger-table">
                    <thead>
                        <tr>
                            <th style="width: 5%;">Seat</th>
                            <th style="width: 12%;">Name</th>
                            <th style="width: 13%;">CNIC</th>
                            <th style="width: 9%;">Cell</th>
                            <th style="width: 16%;">Via</th>
                            <th style="width: 11%;">By</th>
                            <th style="width: 7%;">From</th>
                            <th style="width: 7%;">Desti.</th>
                            <th style="width: 10%;" class="text-right">Fare</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${tripPassengers.map((passenger, index) => {
                            const passengerName = passenger.name || 'N/A';
                            const fare = formatFare(passenger.final_amount || 0);
                            const paymentVia = formatPaymentMethod(passenger.payment_method, passenger.channel);
                            return `
                                <tr>
                                    <td class="text-center"><strong>${passenger.seat_number || 'N/A'}</strong></td>
                                    <td>${passengerName}</td>
                                    <td>${passenger.cnic || 'N/A'}</td>
                                    <td>${passenger.phone || 'N/A'}</td>
                                    <td>${paymentVia}</td>
                                    <td>${passenger.agent_name || 'N/A'}</td>
                                    <td class="text-center">${passenger.from_code || 'N/A'}</td>
                                    <td class="text-center">${passenger.to_code || 'N/A'}</td>
                                    <td class="text-right">${fare}</td>
                                </tr>
                            `;
                        }).join('')}
                    </tbody>
                </table>
                
                <div class="booking-summary-container">
                    <div class="summary-section">
                        <div class="summary-header">Payment Method Summary</div>
                        <table class="summary-table">
                            <thead>
                                <tr>
                                    <th style="width: 30%;">Payment Method</th>
                                    <th style="width: 15%;" class="text-center">Count</th>
                                    <th style="width: 25%;" class="text-right">Total Amount</th>
                                    <th style="width: 30%;" class="text-right">Percentage</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${paymentMethodBreakdown['cash'].count > 0 ? `
                                    <tr>
                                        <td><strong>Cash</strong></td>
                                        <td class="text-center">${paymentMethodBreakdown['cash'].count}</td>
                                        <td class="text-right">${Math.round(paymentMethodBreakdown['cash'].total).toLocaleString()}</td>
                                        <td class="text-right">${((paymentMethodBreakdown['cash'].total / totalEarnings) * 100).toFixed(2)}%</td>
                                    </tr>
                                ` : ''}
                                ${paymentMethodBreakdown['mobile_wallet'].count > 0 ? `
                                    <tr>
                                        <td><strong>Mobile Wallet (JazzCash/Easypaisa)</strong></td>
                                        <td class="text-center">${paymentMethodBreakdown['mobile_wallet'].count}</td>
                                        <td class="text-right">${Math.round(paymentMethodBreakdown['mobile_wallet'].total).toLocaleString()}</td>
                                        <td class="text-right">${((paymentMethodBreakdown['mobile_wallet'].total / totalEarnings) * 100).toFixed(2)}%</td>
                                    </tr>
                                ` : ''}
                                ${paymentMethodBreakdown['bank_transfer'].count > 0 ? `
                                    <tr>
                                        <td><strong>Bank Transfer</strong></td>
                                        <td class="text-center">${paymentMethodBreakdown['bank_transfer'].count}</td>
                                        <td class="text-right">${Math.round(paymentMethodBreakdown['bank_transfer'].total).toLocaleString()}</td>
                                        <td class="text-right">${((paymentMethodBreakdown['bank_transfer'].total / totalEarnings) * 100).toFixed(2)}%</td>
                                    </tr>
                                ` : ''}
                                ${paymentMethodBreakdown['card'].count > 0 ? `
                                    <tr>
                                        <td><strong>Credit / Debit Card</strong></td>
                                        <td class="text-center">${paymentMethodBreakdown['card'].count}</td>
                                        <td class="text-right">${Math.round(paymentMethodBreakdown['card'].total).toLocaleString()}</td>
                                        <td class="text-right">${((paymentMethodBreakdown['card'].total / totalEarnings) * 100).toFixed(2)}%</td>
                                    </tr>
                                ` : ''}
                                ${paymentMethodBreakdown['other'].count > 0 ? `
                                    <tr>
                                        <td><strong>Other</strong></td>
                                        <td class="text-center">${paymentMethodBreakdown['other'].count}</td>
                                        <td class="text-right">${Math.round(paymentMethodBreakdown['other'].total).toLocaleString()}</td>
                                        <td class="text-right">${((paymentMethodBreakdown['other'].total / totalEarnings) * 100).toFixed(2)}%</td>
                                    </tr>
                                ` : ''}
                                <tr style="background-color: #e8f4f8; font-weight: bold;">
                                    <td><strong>Total</strong></td>
                                    <td class="text-center"><strong>${totalPassengers}</strong></td>
                                    <td class="text-right"><strong>${Math.round(totalEarnings).toLocaleString()}</strong></td>
                                    <td class="text-right"><strong>100.00%</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="quick-summary-box">
                        <div class="quick-summary-header">Trip Summary</div>
                        <table class="quick-summary-table">
                            <tbody>
                                <tr>
                                    <td>Total Passengers:</td>
                                    <td><strong>${totalPassengers}</strong></td>
                                </tr>
                                <tr>
                                    <td>Route:</td>
                                    <td><strong>${routeCode}</strong></td>
                                </tr>
                                <tr>
                                    <td>From Terminal:</td>
                                    <td><strong>${fromStop?.terminal_name || fromStop?.terminal_code || 'N/A'}</strong></td>
                                </tr>
                                <tr>
                                    <td>To Terminal:</td>
                                    <td><strong>${toStop?.terminal_name || toStop?.terminal_code || 'N/A'}</strong></td>
                                </tr>
                                <tr>
                                    <td>Departure Date:</td>
                                    <td><strong>${departureDate}</strong></td>
                                </tr>
                                <tr>
                                    <td>Departure Time:</td>
                                    <td><strong>${departureTime}</strong></td>
                                </tr>
                                <tr>
                                    <td>Arrival Time:</td>
                                    <td><strong>${arrivalTime}</strong></td>
                                </tr>
                                <tr>
                                    <td>Vehicle No.:</td>
                                    <td><strong>${vehicleNo}</strong></td>
                                </tr>
                                <tr>
                                    <td>Driver:</td>
                                    <td><strong>${tripData?.driver_name || 'N/A'}</strong></td>
                                </tr>
                                <tr>
                                    <td>Host:</td>
                                    <td><strong>${hostInfo?.name || 'N/A'}</strong></td>
                                </tr>
                                <tr>
                                    <td>Voucher No.:</td>
                                    <td><strong>${voucherNo}</strong></td>
                                </tr>
                                <tr>
                                    <td>Total Fare:</td>
                                    <td><strong>${Math.round(totalEarnings).toLocaleString()}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                ${Object.keys(expensesByTerminal).length > 0 ? `
                    <div style="margin-top: 8px;">
                        <div class="summary-section">
                            <div class="summary-header">Expenses by Terminal</div>
                            <table class="summary-table expenses-table">
                                <thead>
                                    <tr>
                                        <th style="width: 25%;">Terminal</th>
                                        <th style="width: 20%;">Expense Type</th>
                                        <th style="width: 45%;">Description</th>
                                        <th style="width: 10%;" class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${Object.keys(expensesByTerminal).map(terminalKey => {
                                        const terminalExpenses = expensesByTerminal[terminalKey];
                                        return terminalExpenses.map((expense, idx) => `
                                            <tr>
                                                <td>${idx === 0 ? `<strong>${terminalKey}</strong>` : ''}</td>
                                                <td>${expense.type}</td>
                                                <td>${expense.description || '-'}</td>
                                                <td class="text-right">${Math.round(expense.amount).toLocaleString()}</td>
                                            </tr>
                                        `).join('')
                                    }).join('')}
                                    <tr>
                                        <td colspan="3"><strong>Total Expenses</strong></td>
                                        <td class="text-right"><strong>${totalExpenses.toLocaleString()}</strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                ` : ''}
                
                <div style="margin-top: 15px;">
                    <div class="summary-section">
                        <div class="summary-header">Cash & Payment Calculation</div>
                        <table class="summary-table">
                            <tbody>
                                <tr>
                                    <td>Total Cash Payments:</td>
                                    <td class="text-right"><strong>${Math.round(cashTotal).toLocaleString()}</strong></td>
                                </tr>
                                <tr>
                                    <td>Total Mobile Wallet Payments:</td>
                                    <td class="text-right"><strong>${Math.round(mobileWalletTotal).toLocaleString()}</strong></td>
                                </tr>
                                <tr>
                                    <td>Total Bank Transfer Payments:</td>
                                    <td class="text-right"><strong>${Math.round(bankTransferTotal).toLocaleString()}</strong></td>
                                </tr>
                                <tr>
                                    <td>Total Card Payments:</td>
                                    <td class="text-right"><strong>${Math.round(cardTotal).toLocaleString()}</strong></td>
                                </tr>
                                <tr>
                                    <td>Total Online Payments:</td>
                                    <td class="text-right"><strong>${Math.round(onlineTotal).toLocaleString()}</strong></td>
                                </tr>
                                <tr style="border-top: 2px solid #2c3e50;">
                                    <td><strong>Total Expenses:</strong></td>
                                    <td class="text-right"><strong>${totalExpenses.toLocaleString()}</strong></td>
                                </tr>
                                <tr style="background-color: #e8f4f8; font-weight: bold;">
                                    <td><strong>Cash in Hand (Cash - Expenses):</strong></td>
                                    <td class="text-right"><strong>${cashInHand.toLocaleString()}</strong></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="print-date">
                    Printed on: ${new Date().toLocaleString()} | Generated by: ${currentUserName}
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

