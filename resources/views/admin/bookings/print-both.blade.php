<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Ticket - {{ $booking->booking_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            body {
                margin: 0;
                padding: 0;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            background: #fff;
        }
        
        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
        }
        
        .print-btn:hover {
            background: #0056b3;
        }
        
        .ticket-wrapper {
            margin-bottom: 5mm;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Both Tickets
    </button>

    <div class="ticket-wrapper">
        @include('admin.bookings.tickets.' . $design, [
            'booking' => $booking,
            'ticketType' => 'customer'
        ])
    </div>

    <div class="ticket-wrapper" style="page-break-before: always;">
        @include('admin.bookings.tickets.' . $design, [
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

