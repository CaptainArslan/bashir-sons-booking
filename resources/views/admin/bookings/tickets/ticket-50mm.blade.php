<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ticketType === 'host' ? 'Host' : 'Customer' }} Ticket - {{ $booking->booking_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @media print {
            @page {
                size: 50mm auto;
                margin: 0;
                padding: 0;
            }
            
            html {
                width: 50mm !important;
                max-width: 50mm !important;
                margin: 0 !important;
                padding: 0 !important;
            }
            
            body {
                width: 50mm !important;
                max-width: 50mm !important;
                min-width: 50mm !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: hidden;
                font-family: 'Courier New', monospace;
                font-size: 8px;
                line-height: 1.2;
            }
            
            .no-print {
                display: none !important;
            }
            
            .ticket {
                width: 50mm !important;
                max-width: 50mm !important;
                min-width: 50mm !important;
                margin: 0 !important;
                padding: 2mm !important;
                border: none !important;
                box-sizing: border-box;
            }
        }
        
        @media screen {
            body {
                font-family: 'Courier New', monospace;
                font-size: 10px;
                width: 50mm;
                max-width: 50mm;
                padding: 5mm;
                margin: 0 auto;
                background: #fff;
            }
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 10px;
            width: 50mm;
            max-width: 50mm;
            padding: 5mm;
            margin: 0 auto;
            background: #fff;
        }
        
        .ticket {
            width: 100%;
            max-width: 100%;
            border: 1px dashed #000;
            padding: 3mm;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }
        
        .company-name {
            font-size: 9px;
            font-weight: bold;
            margin-bottom: 1mm;
            text-transform: uppercase;
            line-height: 1.1;
        }
        
        .ticket-title {
            font-size: 8px;
            font-weight: bold;
            margin-top: 1mm;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 2mm 0;
        }
        
        .section {
            margin: 2mm 0;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 7px;
            text-transform: uppercase;
            margin-bottom: 0.5mm;
            border-bottom: 1px solid #000;
            padding-bottom: 0.3mm;
        }
        
        @media print {
            .section-title {
                font-size: 6px;
            }
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 0.5mm 0;
            font-size: 8px;
            line-height: 1.1;
        }
        
        .label {
            font-weight: bold;
            flex-shrink: 0;
            font-size: 7px;
        }
        
        .value {
            text-align: right;
            flex: 1;
            margin-left: 2mm;
            word-break: break-word;
            font-size: 7px;
        }
        
        .seat-list {
            font-size: 8px;
            margin-top: 0.5mm;
            line-height: 1.2;
        }
        
        @media print {
            .seat-list {
                font-size: 7px;
            }
        }
        
        .footer {
            text-align: center;
            margin-top: 2mm;
            padding-top: 1.5mm;
            border-top: 1px dashed #000;
            font-size: 6px;
            line-height: 1.2;
        }
        
        @media print {
            .footer {
                font-size: 5px;
                margin-top: 1.5mm;
                padding-top: 1mm;
            }
        }
        
        .booking-number {
            font-size: 10px;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-align: center;
            margin: 1.5mm 0;
            padding: 1.5mm;
            background: #f0f0f0;
            border: 1px solid #000;
        }
        
        @media print {
            .booking-number {
                font-size: 9px;
                padding: 1mm;
                margin: 1.5mm 0;
            }
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
        }
        
        .print-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Ticket
    </button>

    <div class="ticket">
        <!-- Header -->
        @php
            $settings = \App\Models\GeneralSetting::first();
        @endphp
        <div class="header">
            <div class="company-name">
                {{ $settings->company_name ?? 'TRANSPORT SERVICE' }}
            </div>
            <div class="ticket-title">{{ $ticketType === 'host' ? 'HOST TICKET' : 'TICKET' }}</div>
        </div>

        <!-- Booking Number -->
        <div class="booking-number">
            PNR: {{ $booking->booking_number }}
        </div>

        <!-- Route Information -->
        <div class="section">
            <div class="section-title">Route</div>
            <div class="info-row">
                <span class="label">From:</span>
                <span class="value">{{ $booking->fromStop->terminal->code }}</span>
            </div>
            <div class="info-row">
                <span class="label">To:</span>
                <span class="value">{{ $booking->toStop->terminal->code }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Trip Information -->
        <div class="section">
            @php
                $fromTripStop = $booking->trip->stops->firstWhere('terminal_id', $booking->fromStop->terminal_id);
                $toTripStop = $booking->trip->stops->firstWhere('terminal_id', $booking->toStop->terminal_id);
                $departureDate = $fromTripStop?->departure_at?->format('d M Y') ?? $booking->trip->departure_datetime->format('d M Y');
                $departureTime = $fromTripStop?->departure_at?->format('h:i A') ?? $booking->trip->departure_datetime->format('h:i A');
            @endphp
            <div class="info-row">
                <span class="label">Date:</span>
                <span class="value">{{ $departureDate }}</span>
            </div>
            <div class="info-row">
                <span class="label">Time:</span>
                <span class="value">{{ $departureTime }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Seats -->
        <div class="section">
            <div class="section-title">Seats</div>
            <div class="seat-list">
                @foreach($booking->seats as $seat)
                    {{ $seat->seat_number }}@if(!$loop->last), @endif
                @endforeach
            </div>
        </div>

        <div class="divider"></div>

        <!-- Passengers -->
        <div class="section">
            <div class="section-title">Passengers</div>
            @foreach($booking->passengers as $index => $passenger)
            <div style="margin-bottom: 1mm;">
                <div class="info-row">
                    <span class="label">{{ $index + 1 }}. {{ $passenger->name }}</span>
                </div>
            </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <!-- Fare Information -->
        <div class="section">
            <div class="info-row" style="font-weight: bold;">
                <span class="label">Amount:</span>
                <span class="value">PKR {{ number_format($booking->final_amount, 2) }}</span>
            </div>
            <div class="info-row" style="font-size: 6px;">
                <span class="label">Payment:</span>
                <span class="value">{{ ucfirst($booking->payment_method ?? 'Cash') }}</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div style="font-weight: bold; margin-bottom: 0.5mm;">Thank You!</div>
            <div>{{ $settings->phone ?? 'N/A' }}</div>
        </div>
    </div>

    <script>
        window.addEventListener('beforeprint', function() {
            document.body.style.width = '50mm';
            document.body.style.maxWidth = '50mm';
            const ticket = document.querySelector('.ticket');
            if (ticket) {
                ticket.style.width = '50mm';
                ticket.style.maxWidth = '50mm';
            }
        });
        
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>

