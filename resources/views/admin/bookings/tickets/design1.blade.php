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
                font-family: 'Courier New', monospace;
                font-size: 10px;
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
                padding: 3mm !important;
                border: none !important;
                box-sizing: border-box;
            }
        }
        
        @media screen {
            body {
                font-family: 'Courier New', monospace;
                font-size: 12px;
                width: 80mm;
                max-width: 80mm;
                padding: 8mm;
                margin: 0 auto;
                background: #fff;
            }
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 80mm;
            max-width: 80mm;
            padding: 8mm;
            margin: 0 auto;
            background: #fff;
        }
        
        .ticket {
            width: 100%;
            max-width: 100%;
            border: 1px dashed #000;
            padding: 5mm;
        }
        
        .header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 3mm;
            margin-bottom: 3mm;
        }
        
        .company-name {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 2mm;
            text-transform: uppercase;
            line-height: 1.2;
        }
        
        .ticket-title {
            font-size: 10px;
            font-weight: bold;
            margin-top: 2mm;
        }
        
        .divider {
            border-top: 1px dashed #000;
            margin: 3mm 0;
        }
        
        .section {
            margin: 3mm 0;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 9px;
            text-transform: uppercase;
            margin-bottom: 1mm;
            border-bottom: 1px solid #000;
            padding-bottom: 0.5mm;
        }
        
        @media print {
            .section-title {
                font-size: 8px;
            }
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 1mm 0;
            font-size: 10px;
            line-height: 1.2;
        }
        
        .label {
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .value {
            text-align: right;
            flex: 1;
            margin-left: 3mm;
            word-break: break-word;
        }
        
        .seats {
            margin: 2mm 0;
        }
        
        .seat-list {
            font-size: 10px;
            margin-top: 1mm;
            line-height: 1.3;
        }
        
        @media print {
            .seat-list {
                font-size: 9px;
            }
        }
        
        .footer {
            text-align: center;
            margin-top: 3mm;
            padding-top: 2mm;
            border-top: 1px dashed #000;
            font-size: 8px;
            line-height: 1.3;
        }
        
        @media print {
            .footer {
                font-size: 7px;
                margin-top: 2mm;
                padding-top: 1.5mm;
            }
        }
        
        .booking-number {
            font-size: 14px;
            font-weight: bold;
            letter-spacing: 1px;
            text-align: center;
            margin: 2mm 0;
            padding: 2mm;
            background: #f0f0f0;
            border: 1px solid #000;
        }
        
        @media print {
            .booking-number {
                font-size: 12px;
                padding: 1.5mm;
                margin: 2mm 0;
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
            <div class="ticket-title">{{ $ticketType === 'host' ? 'BUS HOST TICKET' : 'BUS TICKET' }}</div>
        </div>

        <!-- Booking Number -->
        <div class="booking-number">
            PNR: {{ $booking->booking_number }}
        </div>

        <!-- Route Information -->
        <div class="section">
            <div class="section-title">Route Details</div>
            <div class="info-row">
                <span class="label">From:</span>
                <span class="value">{{ $booking->fromStop->terminal->name }} ({{ $booking->fromStop->terminal->code }})</span>
            </div>
            <div class="info-row">
                <span class="label">To:</span>
                <span class="value">{{ $booking->toStop->terminal->name }} ({{ $booking->toStop->terminal->code }})</span>
            </div>
            <div class="info-row">
                <span class="label">Route:</span>
                <span class="value">{{ $booking->trip->route->name ?? 'N/A' }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Trip Information -->
        <div class="section">
            <div class="section-title">Trip Details</div>
            @php
                $fromTripStop = $booking->trip->stops->firstWhere('terminal_id', $booking->fromStop->terminal_id);
                $toTripStop = $booking->trip->stops->firstWhere('terminal_id', $booking->toStop->terminal_id);
                
                $departureDate = $fromTripStop?->departure_at?->format('d M Y') ?? $booking->trip->departure_datetime->format('d M Y');
                $departureTime = $fromTripStop?->departure_at?->format('h:i A') ?? $booking->trip->departure_datetime->format('h:i A');
                $arrivalTime = $toTripStop?->arrival_at?->format('h:i A');
            @endphp
            <div class="info-row">
                <span class="label">Date:</span>
                <span class="value">{{ $departureDate }}</span>
            </div>
            <div class="info-row">
                <span class="label">Departure:</span>
                <span class="value">{{ $departureTime }}</span>
            </div>
            @if($arrivalTime)
            <div class="info-row">
                <span class="label">Arrival:</span>
                <span class="value">{{ $arrivalTime }}</span>
            </div>
            @endif
            @if($booking->trip->bus)
            <div class="info-row">
                <span class="label">Bus:</span>
                <span class="value">{{ $booking->trip->bus->name ?? 'N/A' }}</span>
            </div>
            @endif
            @if($ticketType === 'host' && $booking->trip->driver_name)
            <div class="info-row">
                <span class="label">Driver:</span>
                <span class="value">{{ $booking->trip->driver_name }}</span>
            </div>
            @if($booking->trip->driver_phone)
            <div class="info-row" style="font-size: 10px;">
                <span class="label">Driver Phone:</span>
                <span class="value">{{ $booking->trip->driver_phone }}</span>
            </div>
            @endif
            @endif
        </div>

        <div class="divider"></div>

        <!-- Seats -->
        <div class="section">
            <div class="section-title">Seats</div>
            <div class="seat-list">
                @foreach($booking->seats as $seat)
                    Seat {{ $seat->seat_number }}@if(!$loop->last), @endif
                @endforeach
            </div>
        </div>

        <div class="divider"></div>

        <!-- Passengers -->
        <div class="section">
            <div class="section-title">Passengers</div>
            @foreach($booking->passengers as $index => $passenger)
            <div style="margin-bottom: 2mm;">
                <div class="info-row">
                    <span class="label">{{ $index + 1 }}. {{ $passenger->name }}</span>
                    <span class="value">{{ ucfirst($passenger->gender->value) }}</span>
                </div>
                @if($passenger->phone)
                <div class="info-row" style="font-size: 10px;">
                    <span>Phone:</span>
                    <span class="value">{{ $passenger->phone }}</span>
                </div>
                @endif
                @if($ticketType === 'host' && $passenger->cnic)
                <div class="info-row" style="font-size: 10px;">
                    <span>CNIC:</span>
                    <span class="value">{{ $passenger->cnic }}</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="divider"></div>

        @if($ticketType === 'host')
        <!-- Host Information Section -->
        @php
            $hostInfo = null;
            if($booking->trip->notes) {
                if(preg_match('/Host:\s*([^(]+)(?:\s*\(([^)]+)\))?/i', $booking->trip->notes, $matches)) {
                    $hostInfo = [
                        'name' => trim($matches[1] ?? ''),
                        'phone' => trim($matches[2] ?? ''),
                    ];
                }
            }
        @endphp
        @if($hostInfo && ($hostInfo['name'] !== 'N/A' || $hostInfo['phone']))
        <div class="section">
            <div class="section-title">Host Information</div>
            <div class="info-row">
                <span class="label">Host Name:</span>
                <span class="value">{{ $hostInfo['name'] !== 'N/A' ? $hostInfo['name'] : 'N/A' }}</span>
            </div>
            @if($hostInfo['phone'])
            <div class="info-row">
                <span class="label">Host Phone:</span>
                <span class="value">{{ $hostInfo['phone'] }}</span>
            </div>
            @endif
        </div>
        <div class="divider"></div>
        @endif
        @endif

        <!-- Fare Information -->
        <div class="section">
            <div class="section-title">Fare</div>
            @php
                $baseFare = $booking->total_fare + ($booking->discount_amount ?? 0);
            @endphp
            <div class="info-row">
                <span class="label">Base Fare:</span>
                <span class="value">PKR {{ number_format($baseFare, 2) }}</span>
            </div>
            @if($booking->discount_amount > 0)
            <div class="info-row" style="color: #dc3545;">
                <span class="label">Discount:</span>
                <span class="value">-PKR {{ number_format($booking->discount_amount, 2) }}</span>
            </div>
            @endif
            <div class="info-row">
                <span class="label">Total Fare:</span>
                <span class="value">PKR {{ number_format($booking->total_fare, 2) }}</span>
            </div>
            @if($booking->tax_amount > 0)
            <div class="info-row">
                <span class="label">Tax/Charge:</span>
                <span class="value">+PKR {{ number_format($booking->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="info-row" style="font-weight: bold; border-top: 1px solid #000; padding-top: 1mm; margin-top: 1mm;">
                <span class="label">Final Amount:</span>
                <span class="value">PKR {{ number_format($booking->final_amount, 2) }}</span>
            </div>
            <div class="info-row" style="font-size: 10px; margin-top: 1mm;">
                <span class="label">Payment:</span>
                <span class="value">{{ ucfirst($booking->payment_method ?? 'Cash') }} - {{ ucfirst($booking->payment_status) }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Status -->
        <div class="section">
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="value">{{ ucfirst($booking->status) }}</span>
            </div>
            <div class="info-row" style="font-size: 10px;">
                <span class="label">Booked On:</span>
                <span class="value">{{ $booking->created_at->format('d M Y, h:i A') }}</span>
            </div>
            @if($booking->channel)
            <div class="info-row" style="font-size: 10px;">
                <span class="label">Channel:</span>
                <span class="value">{{ ucfirst($booking->channel) }}</span>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div style="font-weight: bold; margin-bottom: 1mm;">Thank You!</div>
            <div>For queries call: {{ $settings->phone ?? 'N/A' }}</div>
            @if($settings->support_phone)
            <div>Support: {{ $settings->support_phone }}</div>
            @endif
            <div style="margin-top: 2mm; font-size: 9px;">
                Valid only for the date and route mentioned
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('beforeprint', function() {
            document.body.style.width = '80mm';
            document.body.style.maxWidth = '80mm';
            const ticket = document.querySelector('.ticket');
            if (ticket) {
                ticket.style.width = '80mm';
                ticket.style.maxWidth = '80mm';
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

