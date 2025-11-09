<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Both Tickets - {{ $booking->booking_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @media print {
            @page {
                size: {{ $size === 'a4' ? 'A4' : ($size === '80mm' ? '80mm auto' : '50mm auto') }};
                margin: 0;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .page-break {
                page-break-after: always;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            background: #f5f5f5;
        }
        
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 30px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            z-index: 1000;
        }
        
        .print-btn:hover {
            background: #0056b3;
        }
        
        .ticket-container {
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Both Tickets
    </button>

    <!-- Customer Ticket -->
    <div class="ticket-container">
        @include('admin.bookings.tickets.ticket-' . strtolower($size), [
            'booking' => $booking,
            'ticketType' => 'customer'
        ])
    </div>

    <!-- Page Break -->
    <div class="page-break"></div>

    <!-- Host Ticket -->
    <div class="ticket-container">
        @include('admin.bookings.tickets.ticket-' . strtolower($size), [
            'booking' => $booking,
            'ticketType' => 'host'
        ])
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>

