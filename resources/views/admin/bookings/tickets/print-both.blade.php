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
            
            /* Override any body/html styles from included templates */
            html, body {
                width: auto !important;
                max-width: 100% !important;
                min-width: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                background: #fff !important;
            }
            
            .tickets-wrapper {
                margin: 0;
                padding: 0;
                width: 100%;
            }
            
            .ticket-container {
                margin-bottom: {{ $size === 'a4' ? '15mm' : ($size === '80mm' ? '8mm' : '5mm') }};
                page-break-inside: avoid;
                width: 100%;
            }
            
            .ticket-container:last-child {
                margin-bottom: 0;
            }
            
            /* Override ticket styles from included templates for combined view */
            .ticket-container .ticket {
                width: 100% !important;
                max-width: 100% !important;
                min-width: auto !important;
                margin: 0 !important;
                padding: {{ $size === 'a4' ? '5mm' : ($size === '80mm' ? '3mm' : '2mm') }} !important;
            }
            
            /* Hide print buttons from included templates */
            .ticket-container .print-btn {
                display: none !important;
            }
        }
        
        @media screen {
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 20px;
                background: #f5f5f5;
            }
            
            .tickets-wrapper {
                max-width: {{ $size === 'a4' ? '210mm' : ($size === '80mm' ? '80mm' : '50mm') }};
                margin: 0 auto;
                background: #fff;
                padding: 20px;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
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
        
        .tickets-wrapper {
            width: 100%;
        }
        
        .ticket-container {
            margin-bottom: {{ $size === 'a4' ? '15mm' : ($size === '80mm' ? '8mm' : '5mm') }};
            position: relative;
        }
        
        .ticket-container:last-child {
            margin-bottom: 0;
        }
        
        /* Override styles from included ticket templates */
        .ticket-container html,
        .ticket-container body {
            width: auto !important;
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
        }
        
        .ticket-container .ticket {
            width: 100% !important;
            max-width: 100% !important;
        }
        
        /* Hide print buttons from included templates */
        .ticket-container .print-btn {
            display: none !important;
        }
        
        /* Divider between tickets */
        .ticket-divider {
            height: 2px;
            background: #ddd;
            margin: {{ $size === 'a4' ? '10mm' : ($size === '80mm' ? '5mm' : '3mm') }} 0;
            border: none;
        }
        
        @media print {
            .ticket-divider {
                border-top: 1px dashed #999;
                background: transparent;
            }
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Both Tickets
    </button>

    <div class="tickets-wrapper">
        <!-- Customer Ticket -->
        <div class="ticket-container">
            @include('admin.bookings.tickets.ticket-' . strtolower($size), [
                'booking' => $booking,
                'ticketType' => 'customer'
            ])
        </div>

        <!-- Divider -->
        <hr class="ticket-divider">

        <!-- Host Ticket -->
        <div class="ticket-container">
            @include('admin.bookings.tickets.ticket-' . strtolower($size), [
                'booking' => $booking,
                'ticketType' => 'host'
            ])
        </div>
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

