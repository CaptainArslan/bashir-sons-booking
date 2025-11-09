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
                font-family: Arial, sans-serif;
                font-size: 9px;
                line-height: 1.2;
            }
            
            .no-print {
                display: none !important;
            }
            
            .ticket {
                width: 80mm !important;
                max-width: 80mm !important;
                min-width: 80mm !important;
                margin: 0 !important;
                padding: 2mm !important;
                border: none !important;
                box-sizing: border-box;
            }
        }
        
        @media screen {
            body {
                font-family: Arial, sans-serif;
                font-size: 11px;
                width: 80mm;
                max-width: 80mm;
                padding: 8mm;
                margin: 0 auto;
                background: #fff;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            width: 80mm;
            max-width: 80mm;
            padding: 8mm;
            margin: 0 auto;
            background: #fff;
        }
        
        .ticket {
            width: 100%;
            max-width: 100%;
            border: 1px solid #000;
            padding: 3mm;
        }
        
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 2mm;
            margin-bottom: 2mm;
        }
        
        .company-name {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 1mm;
            text-transform: uppercase;
        }
        
        .ticket-title {
            font-size: 9px;
            font-weight: bold;
            margin-top: 1mm;
        }
        
        .route-header {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            margin: 2mm 0;
            padding: 1mm;
            background: #f0f0f0;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 0.5mm 0;
            font-size: 8px;
            line-height: 1.2;
        }
        
        .label {
            font-weight: bold;
            flex-shrink: 0;
            width: 35%;
        }
        
        .value {
            text-align: left;
            flex: 1;
            margin-left: 2mm;
        }
        
        .divider {
            border-top: 1px solid #000;
            margin: 1.5mm 0;
        }
        
        .section {
            margin: 1.5mm 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 2mm;
            padding-top: 1mm;
            border-top: 1px solid #000;
            font-size: 7px;
            line-height: 1.2;
        }
        
        .booking-number {
            font-size: 10px;
            font-weight: bold;
            text-align: center;
            margin: 1mm 0;
            padding: 1mm;
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
        @php
            $settings = \App\Models\GeneralSetting::first();
            $fromTripStop = $booking->trip->stops->firstWhere('terminal_id', $booking->fromStop->terminal_id);
            $toTripStop = $booking->trip->stops->firstWhere('terminal_id', $booking->toStop->terminal_id);
            
            $departureDate = $fromTripStop?->departure_at?->format('d-m-Y') ?? $booking->trip->departure_datetime->format('d-m-Y');
            $departureTime = $fromTripStop?->departure_at?->format('h:i A') ?? $booking->trip->departure_datetime->format('h:i A');
        @endphp
        
        <div class="header">
            <div class="company-name">{{ $settings->company_name ?? 'TRANSPORT SERVICE' }}</div>
            <div class="ticket-title">{{ $ticketType === 'host' ? 'HOST COPY' : 'CUSTOMER COPY' }}</div>
        </div>

        <div class="route-header">
            {{ $booking->fromStop->terminal->code }}-{{ $booking->toStop->terminal->code }} {{ $departureTime }}
        </div>

        <div class="section">
            <div class="info-row">
                <span class="label">From:</span>
                <span class="value">{{ $booking->fromStop->terminal->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">To:</span>
                <span class="value">{{ $booking->toStop->terminal->name }}</span>
            </div>
            <div class="info-row">
                <span class="label">Date & Time:</span>
                <span class="value">{{ $departureDate }} {{ $departureTime }}</span>
            </div>
            <div class="info-row">
                <span class="label">PNR:</span>
                <span class="value">{{ $booking->booking_number }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="section">
            <div class="info-row">
                <span class="label">Seat No:</span>
                <span class="value">
                    @foreach($booking->seats as $seat)
                        {{ $seat->seat_number }}@if($seat->gender) ({{ strtoupper($seat->gender->value) }})@endif@if(!$loop->last), @endif
                    @endforeach
                </span>
            </div>
        </div>

        <div class="divider"></div>

        <div class="section">
            @foreach($booking->passengers as $index => $passenger)
            <div class="info-row">
                <span class="label">Name:</span>
                <span class="value">{{ $passenger->name }}</span>
            </div>
            @if($ticketType === 'host' && $passenger->cnic)
            <div class="info-row">
                <span class="label">CNIC:</span>
                <span class="value">{{ $passenger->cnic }}</span>
            </div>
            @endif
            @if($passenger->phone)
            <div class="info-row">
                <span class="label">Cell:</span>
                <span class="value">{{ $passenger->phone }}</span>
            </div>
            @endif
            @if(!$loop->last)
            <div class="divider"></div>
            @endif
            @endforeach
        </div>

        <div class="divider"></div>

        <div class="section">
            <div class="info-row">
                <span class="label">Fare:</span>
                <span class="value">{{ number_format($booking->total_fare, 0) }}</span>
            </div>
            @if($booking->discount_amount > 0)
            <div class="info-row">
                <span class="label">Discount:</span>
                <span class="value">-{{ number_format($booking->discount_amount, 0) }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="label">Total:</span>
                <span class="value">{{ number_format($booking->final_amount, 0) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Payment:</span>
                <span class="value">{{ ucfirst($booking->payment_method ?? 'Cash') }}</span>
            </div>
        </div>

        @if($ticketType === 'host')
        <div class="divider"></div>
        <div class="section">
            <div class="info-row">
                <span class="label">Prepared By:</span>
                <span class="value">{{ $booking->user->name ?? 'N/A' }}</span>
            </div>
            <div class="info-row">
                <span class="label">Date:</span>
                <span class="value">{{ $booking->created_at->format('m/d/Y h:i A') }}</span>
            </div>
        </div>
        @endif

        <div class="footer">
            <div style="font-weight: bold; margin-bottom: 0.5mm;">Thank You!</div>
            <div>For Booking, Complaints & Suggestions</div>
            <div>Call: {{ $settings->phone ?? 'N/A' }}</div>
            @if($settings->support_phone)
            <div>Support: {{ $settings->support_phone }}</div>
            @endif
        </div>
    </div>

    <script>
        window.addEventListener('beforeprint', function() {
            document.body.style.width = '80mm';
            document.body.style.maxWidth = '80mm';
        });
        
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>

