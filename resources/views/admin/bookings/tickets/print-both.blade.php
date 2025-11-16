<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Tickets - {{ $booking->booking_number }}</title>
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

            /* Override any body/html styles from included templates */
            html,
            body {
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
                margin-bottom: 8mm;
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
                padding: 3mm !important;
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
                max-width: 80mm;
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
            margin-bottom: 8mm;
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
    </style>
</head>

<body>
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print
    </button>

    <div class="tickets-wrapper">
        <!-- Single Ticket with Customer Section and Boarding Coupon -->
        <div class="ticket-container">
            @include('admin.bookings.tickets.ticket-80mm', [
                'booking' => $booking,
                'ticketType' => 'customer',
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
