<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Tickets - {{ $booking->booking_number }}</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Source+Code+Pro:wght@400;700&display=swap');

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

            html {
                width: 80mm !important;
                max-width: 80mm !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body {
                width: 80mm !important;
                max-width: 80mm !important;
                min-width: 80mm !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden;
                font-family: 'Source Code Pro', 'Courier New', Courier, monospace;
                background-color: #f0f0f0;
                color: #000;
            }

            .no-print {
                display: none !important;
            }

            .ticket {
                width: 80mm !important;
                max-width: 80mm !important;
                min-width: 80mm !important;
                margin: 0 !important;
                padding: 0 !important;
                background: #ffffff;
                border: 1px solid #ccc;
                page-break-after: always;
            }

            .ticket:last-child {
                page-break-after: auto;
            }
        }

        @media screen {
            body {
                font-family: 'Source Code Pro', 'Courier New', Courier, monospace;
                background-color: #f0f0f0;
                padding: 20px;
                color: #000;
            }

            .ticket {
                width: 320px;
                background: #ffffff;
                border: 1px solid #ccc;
                /* box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); */
                margin-bottom: 20px;
            }
        }

        body {
            font-family: 'Source Code Pro', 'Courier New', Courier, monospace;
            background-color: #f0f0f0;
            padding: 20px;
            color: #000;
        }

        .ticket {
            width: 320px;
            background: #ffffff;
            border: 1px solid #ccc;
            /* box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1); */
            margin-bottom: 20px;
        }

        .ticket-main {
            padding: 20px;
            text-align: center;
        }

        .ticket-main h2 {
            margin: 0;
            font-size: 20px;
        }

        .ticket-main p {
            margin: 4px 0;
            font-size: 12px;
        }

        .daewoo-bus-service {
            font-weight: 800;
            font-size: 16px !important;
        }

        .route-info {
            margin: 15px 0;
            font-weight: 700;
        }

        .route-info p {
            font-size: 13px;
        }

        .details-top,
        .footer-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 12px;
        }

        .details-top {
            font-weight: 800;
        }

        .details-top .seat-info {
            font-size: larger;
        }

        .seat-info {
            font-weight: 700;
            font-size: 14px;
        }

        .details-body {
            text-align: left;
            margin: 10px 0;
            font-size: 12px;
        }

        .details-body p {
            margin: 5px 0;
        }

        .urdu-text {
            font-family: 'Arial', sans-serif;
            font-size: 14px;
            text-align: center;
            margin: 15px 0;
            font-weight: 900;
        }

        .contact-info {
            margin-top: 15px;
            border-top: 1px solid #eee;
            padding-top: 10px;
        }

        .website-link {
            text-decoration: none;
            color: #000;
            font-weight: 900;
        }

        .contact-info .uan {
            font-size: 18px;
            font-weight: 700;
            margin: 5px 0;
        }

        hr {
            border: none;
            border-top: 2px dashed #888;
            margin: 0;
        }

        .ticket-stub {
            padding: 20px;
            text-align: center;
        }

        .ticket-stub p {
            margin: 4px 0;
            font-size: 13px;
        }

        .stub-footer {
            text-align: left;
            margin-top: 20px;
            font-size: 12px;
        }

        .stub-footer .seat-info {
            font-size: 12px;
        }

        .stub-prepared {
            text-align: left;
            font-size: 12px;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            z-index: 1000;
            /* box-shadow: 0 2px 8px rgba(0,0,0,0.2); */
            transition: all 0.3s;
        }

        .print-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
            /* box-shadow: 0 4px 12px rgba(0,0,0,0.3); */
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print All Tickets ({{ $seats->count() }})
    </button>

    @foreach($seats as $index => $seat)
        @php
            $settings = \App\Models\GeneralSetting::first();
            $fromTripStop = $booking->trip->stops->firstWhere('terminal_id', $booking->fromStop->terminal_id);
            $toTripStop = $booking->trip->stops->firstWhere('terminal_id', $booking->toStop->terminal_id);
            $departureDateTime = $fromTripStop?->departure_at ?? $booking->trip->departure_datetime;
            $departureDate = $departureDateTime ? $departureDateTime->format('d-m-Y') : 'N/A';
            $departureTime = $departureDateTime ? $departureDateTime->format('h:i A') : 'N/A';
            $departureDateShort = $departureDateTime ? $departureDateTime->format('d/m/Y') : 'N/A';
            
            // Get passenger for this seat
            // If there's only one passenger, use that passenger's info for all tickets
            // Otherwise, match passenger to seat by index
            if ($passengers->count() === 1) {
                $passenger = $passengers->first();
            } else {
                $passenger = $passengers->get($index);
            }
            
            $bus = $booking->trip->bus;
            $busName = $bus->name ?? 'N/A';
            $busRegistration = $bus->registration_number ?? '';
            $busCode = $booking->fromStop->terminal->code ?? '';
            $routeCode = $busRegistration && $busCode ? $busRegistration . ' ' . $busCode : ($busRegistration ?: ($busCode ?: 'N/A'));
            $fromTerminalName = strtoupper($booking->fromStop->terminal->name ?? 'N/A');
            $toTerminalName = strtoupper($booking->toStop->terminal->name ?? 'N/A');
            $fromTerminalCode = strtoupper($booking->fromStop->terminal->code ?? '');
            $toTerminalCode = strtoupper($booking->toStop->terminal->code ?? '');
            $routeDisplay = $fromTerminalCode . '-' . $toTerminalCode . ' ' . $departureTime;
            $bookedByUser = $booking->bookedByUser ?? $booking->user;
            $bookedByName = $bookedByUser ? strtoupper($bookedByUser->name) : 'N/A';
            
            // Single ticket price (from seat's final_amount)
            $singleTicketPrice = number_format($seat->final_amount, 0);
            
            // Single seat number
            $seatDisplay = 'SEAT NO. ' . str_pad($seat->seat_number, 2, '0', STR_PAD_LEFT);
            if ($seat->gender) {
                $seatDisplay .= ' (' . strtoupper($seat->gender->value) . ')';
            }
            
            $isDuplicate = $booking->status === 'cancelled' || $booking->created_at->lt(now()->subDay());
        @endphp

        <div class="ticket">
            <div class="ticket-main">
                <h2>{{ $settings->company_name ?? 'BS/Niazi' }}</h2>
                <p class="daewoo-bus-service">{{ $settings->tagline ?? 'Bashir Sons Group' }}</p>

                <div class="route-info">
                    <p>{{ $routeDisplay }}</p>
                    <p>From {{ $fromTerminalName }} To {{ $toTerminalName }}</p>
                    <p>{{ $departureDate }} {{ $departureTime }}</p>
                    <p>{{ $routeCode }}</p>
                </div>

                <div class="details-top">
                    <p>
                        @if($isDuplicate)
                            Duplicate
                        @endif
                    </p>
                    <p class="seat-info">{{ $seatDisplay }}</p>
                </div>

                <div class="details-body">
                    <p>Sr #&nbsp;&nbsp; 1 &nbsp;&nbsp;&nbsp; Pax&nbsp;&nbsp; 1 &nbsp;&nbsp;&nbsp; Total&nbsp;&nbsp; {{ $singleTicketPrice }}</p>
                    <p>PNR&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $booking->booking_number }}</p>
                    <p>Name&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ strtoupper($passenger->name ?? 'N/A') }}</p>
                    <p>CNIC&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $passenger->cnic ?? 'N/A' }}</p>
                    <p>Cell&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; {{ $passenger->phone ?? 'N/A' }}</p>
                </div>

                <div class="footer-info">
                    <p>Prepared By {{ $bookedByName }}</p>
                    <p>{{ $booking->created_at->format('n/j/Y g:i a') }}</p>
                </div>

                <p class="urdu-text" dir="rtl">
                    گاڑی روانگی ٹائم سے 15 منٹ پہلے تشریف لائیں شکریہ
                </p>

                <div class="contact-info">
                    <p>For Booking, Complaints & Suggestions</p>
                    <p class="uan">UAN: {{ $settings->phone ?? '041 111 737 737' }}</p>
                    <p>For Online Ticket Booking Please Visit</p>
                    <p class="website-link">{{ $settings->website_url ?? 'www.bashirsonsgroup.com' }}</p>
                </div>
            </div>

            <hr>

            <div class="ticket-stub">
                <p class="route-info">{{ $routeDisplay }}</p>
                <p class="route-info">From {{ $fromTerminalName }} To {{ $toTerminalName }}</p>
                <p class="route-info">{{ $departureDate }} {{ $departureTime }}</p>
                <p class="route-info">{{ $routeCode }}</p>

                <p class="stub-footer">
                    {{ $booking->created_at->format('d/m/Y g:i a') }}
                    <span class="seat-info">{{ $seatDisplay }}</span>
                </p>

                <p class="stub-prepared">Prepared By {{ $bookedByName }}</p>
            </div>
        </div>
    @endforeach

    <script>
        window.addEventListener('beforeprint', function() {
            document.body.style.width = '80mm';
            document.body.style.maxWidth = '80mm';
            const tickets = document.querySelectorAll('.ticket');
            tickets.forEach(ticket => {
                ticket.style.width = '80mm';
                ticket.style.maxWidth = '80mm';
            });
        });

        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>

