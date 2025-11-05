<script>
    {{-- Utility Functions --}}

    // ========================================
    // RELOAD PAGE (Refresh all data)
    // ========================================
    function reloadPage() {
        // Refresh the entire page to clear all data and reload fresh
        window.location.reload();
    }

    // ========================================
    // RESET FORM (Legacy - now redirects to reload)
    // ========================================
    function resetForm() {
        reloadPage();
    }

    // ========================================
    // PRINT PASSENGER LIST FOR MOTORWAY POLICE
    // ========================================
    function printPassengerList() {
        const passengers = window.currentTripPassengers || [];
        const tripData = window.currentTripData || {};

        if (!passengers || passengers.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'No Passengers',
                text: 'No passengers to print. Please load a trip first.',
                confirmButtonColor: '#ffc107'
            });
            return;
        }

        // Create print window
        const printWindow = window.open('', '_blank');
        
        const date = new Date(tripData.date).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });

        printWindow.document.write(`
            <!DOCTYPE html>
            <html>
            <head>
                <title>Passenger List - Motorway Police</title>
                <style>
                    @media print {
                        @page {
                            size: A4;
                            margin: 1cm;
                        }
                        body {
                            margin: 0;
                            padding: 0;
                        }
                    }
                    * {
                        margin: 0;
                        padding: 0;
                        box-sizing: border-box;
                    }
                    body {
                        font-family: Arial, sans-serif;
                        font-size: 12px;
                        padding: 20px;
                        color: #000;
                    }
                    .header {
                        text-align: center;
                        border-bottom: 3px solid #000;
                        padding-bottom: 15px;
                        margin-bottom: 20px;
                    }
                    .header h1 {
                        font-size: 24px;
                        font-weight: bold;
                        margin-bottom: 5px;
                        text-transform: uppercase;
                    }
                    .header h2 {
                        font-size: 18px;
                        font-weight: bold;
                        margin-bottom: 10px;
                    }
                    .trip-info {
                        display: flex;
                        justify-content: space-between;
                        margin-bottom: 20px;
                        padding: 10px;
                        background-color: #f5f5f5;
                        border: 1px solid #ddd;
                    }
                    .trip-info div {
                        flex: 1;
                    }
                    .trip-info strong {
                        display: block;
                        margin-bottom: 5px;
                        font-size: 11px;
                        color: #666;
                    }
                    .trip-info span {
                        font-size: 14px;
                        font-weight: bold;
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        margin-top: 10px;
                        page-break-inside: auto;
                    }
                    thead {
                        background-color: #333;
                        color: #fff;
                    }
                    thead th {
                        padding: 10px 8px;
                        text-align: left;
                        font-weight: bold;
                        border: 1px solid #000;
                        font-size: 11px;
                    }
                    tbody tr {
                        page-break-inside: avoid;
                        page-break-after: auto;
                    }
                    tbody td {
                        padding: 8px;
                        border: 1px solid #000;
                        font-size: 11px;
                    }
                    tbody tr:nth-child(even) {
                        background-color: #f9f9f9;
                    }
                    .text-center {
                        text-align: center;
                    }
                    .footer {
                        margin-top: 30px;
                        padding-top: 20px;
                        border-top: 2px solid #000;
                        display: flex;
                        justify-content: space-between;
                    }
                    .footer div {
                        flex: 1;
                    }
                    .signature-line {
                        border-top: 1px solid #000;
                        margin-top: 50px;
                        padding-top: 5px;
                    }
                    .no-print {
                        display: none;
                    }
                </style>
            </head>
            <body>
                <div class="header">
                    <h1>Bashir Sons Transport</h1>
                    <h2>Passenger List for Motorway Police</h2>
                </div>

                <div class="trip-info">
                    <div>
                        <strong>Route:</strong>
                        <span>${tripData.route}</span>
                    </div>
                    <div>
                        <strong>Date:</strong>
                        <span>${date}</span>
                    </div>
                    <div>
                        <strong>Departure Time:</strong>
                        <span>${tripData.time}</span>
                    </div>
                    <div>
                        <strong>Bus:</strong>
                        <span>${tripData.bus}</span>
                    </div>
                    <div>
                        <strong>Total Passengers:</strong>
                        <span>${passengers.length}</span>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr>
                            <th style="width: 5%;">Sr#</th>
                            <th style="width: 18%;">Passenger Name</th>
                            <th style="width: 15%;">CNIC</th>
                            <th style="width: 8%;">Gender</th>
                            <th style="width: 8%;">Seat</th>
                            <th style="width: 12%;">Phone</th>
                            <th style="width: 12%;">From Terminal</th>
                            <th style="width: 12%;">To Terminal</th>
                            <th style="width: 10%;">Booking #</th>
                        </tr>
                    </thead>
                    <tbody>
        `);

        passengers.forEach((passenger, index) => {
            const gender = passenger.gender ? String(passenger.gender).charAt(0).toUpperCase() + String(passenger.gender).slice(1) : 'N/A';
            const seats = passenger.seats_display || 'N/A';
            const cnic = passenger.cnic || 'N/A';
            const phone = passenger.phone || 'N/A';
            
            printWindow.document.write(`
                        <tr>
                            <td class="text-center">${index + 1}</td>
                            <td>${passenger.name || 'N/A'}</td>
                            <td>${cnic}</td>
                            <td class="text-center">${gender}</td>
                            <td class="text-center">${seats}</td>
                            <td>${phone}</td>
                            <td>${passenger.from_stop || 'N/A'}</td>
                            <td>${passenger.to_stop || 'N/A'}</td>
                            <td>${passenger.booking_number || 'N/A'}</td>
                        </tr>
            `);
        });

        printWindow.document.write(`
                    </tbody>
                </table>

                <div class="footer">
                    <div>
                        <div class="signature-line">
                            <strong>Driver Signature:</strong>
                        </div>
                    </div>
                    <div>
                        <div class="signature-line">
                            <strong>Conductor Signature:</strong>
                        </div>
                    </div>
                    <div>
                        <div class="signature-line">
                            <strong>Motorway Police Verification:</strong>
                        </div>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: center; font-size: 10px; color: #666;">
                    <p>Generated on: ${new Date().toLocaleString('en-US', { dateStyle: 'full', timeStyle: 'short' })}</p>
                    <p>This is a computer-generated document for motorway police verification purposes.</p>
                </div>
            </body>
            </html>
        `);

        printWindow.document.close();
        
        // Wait for content to load, then print
        setTimeout(() => {
            printWindow.print();
        }, 250);
    }
</script>
