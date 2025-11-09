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
                font-family: 'Arial', sans-serif;
                font-size: 9px;
                line-height: 1.3;
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
                font-family: 'Arial', sans-serif;
                font-size: 11px;
                width: 80mm;
                max-width: 80mm;
                padding: 8mm;
                margin: 0 auto;
                background: #fff;
            }
        }
        
        body {
            font-family: 'Arial', sans-serif;
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
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 1mm;
            text-transform: uppercase;
        }
        
        .ticket-title {
            font-size: 10px;
            font-weight: bold;
            margin-top: 1mm;
        }
        
        .main-section {
            margin: 2mm 0;
        }
        
        .info-block {
            margin: 1.5mm 0;
        }
        
        .info-label {
            font-weight: bold;
            font-size: 8px;
            display: inline-block;
            width: 40%;
        }
        
        .info-value {
            font-size: 9px;
            display: inline-block;
            width: 58%;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 2mm 0;
        }
        
        .footer {
            text-align: center;
            margin-top: 2mm;
            padding-top: 1mm;
            border-top: 1px dashed #000;
            font-size: 7px;
            line-height: 1.3;
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
            
            $departureDate = $fromTripStop?->departure_at?->format('d-M-Y') ?? $booking->trip->departure_datetime->format('d-M-Y');
            $departureTime = $fromTripStop?->departure_at?->format('h:i A') ?? $booking->trip->departure_datetime->format('h:i A');
            $arrivalTime = $toTripStop?->arrival_at?->format('h:i A');
        @endphp
        
        <div class="header">
            <div class="company-name">{{ $settings->company_name ?? 'TRANSPORT SERVICE' }}</div>
            <div class="ticket-title">{{ $ticketType === 'host' ? 'HOST TICKET' : 'BUS TICKET' }}</div>
        </div>

        <div class="main-section">
            <div class="info-block">
                <span class="info-label">Seat No:</span>
                <span class="info-value">
                    @foreach($booking->seats as $seat)
                        {{ $seat->seat_number }}@if($seat->gender) ({{ ucfirst($seat->gender->value) }})@endif@if(!$loop->last), @endif
                    @endforeach
                </span>
            </div>
            
            <div class="info-block">
                <span class="info-label">Dep Time:</span>
                <span class="info-value">{{ $departureTime }} {{ $departureDate }}</span>
            </div>
            
            <div class="info-block">
                <span class="info-label">Name:</span>
                <span class="info-value">{{ $booking->passengers->first()->name ?? 'N/A' }}</span>
            </div>
            
            <div class="info-block">
                <span class="info-label">From City:</span>
                <span class="info-value">{{ $booking->fromStop->terminal->name }}</span>
            </div>
            
            <div class="info-block">
                <span class="info-label">Destination:</span>
                <span class="info-value">{{ $booking->toStop->terminal->name }}</span>
            </div>
            
            <div class="info-block">
                <span class="info-label">Fare:</span>
                <span class="info-value">{{ number_format($booking->final_amount, 0) }} PKR</span>
            </div>
            
            <div class="info-block">
                <span class="info-label">Route:</span>
                <span class="info-value">{{ $booking->trip->route->name ?? 'N/A' }}</span>
            </div>
            
            @if($booking->trip->bus)
            <div class="info-block">
                <span class="info-label">Bus:</span>
                <span class="info-value">{{ $booking->trip->bus->name ?? 'N/A' }}</span>
            </div>
            @endif
            
            <div class="info-block">
                <span class="info-label">Ticket #:</span>
                <span class="info-value">{{ $booking->booking_number }}</span>
            </div>
            
            <div class="info-block">
                <span class="info-label">Print Time:</span>
                <span class="info-value">{{ now()->format('h:i A d/m/Y') }}</span>
            </div>
            
            @if($ticketType === 'host')
            <div class="info-block">
                <span class="info-label">User ID:</span>
                <span class="info-value">{{ $booking->user->id ?? 'N/A' }}</span>
            </div>
            @endif
        </div>

        <div class="divider"></div>

        @if($ticketType === 'host')
        <div class="main-section">
            @foreach($booking->passengers as $passenger)
            @if($passenger->cnic)
            <div class="info-block">
                <span class="info-label">CNIC:</span>
                <span class="info-value">{{ $passenger->cnic }}</span>
            </div>
            @endif
            @if($passenger->phone)
            <div class="info-block">
                <span class="info-label">Phone:</span>
                <span class="info-value">{{ $passenger->phone }}</span>
            </div>
            @endif
            @endforeach
        </div>
        <div class="divider"></div>
        @endif

        <div class="footer">
            <div style="font-weight: bold; margin-bottom: 0.5mm;">Thank You!</div>
            <div>For queries call: {{ $settings->phone ?? 'N/A' }}</div>
            @if($settings->support_phone)
            <div>Support: {{ $settings->support_phone }}</div>
            @endif
            <div style="margin-top: 1mm; font-size: 6px;">
                Please arrive 15 minutes before departure time
            </div>
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

