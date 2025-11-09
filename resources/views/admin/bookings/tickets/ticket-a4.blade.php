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
                size: A4;
                margin: 10mm;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            background: #fff;
            padding: 20px;
        }
        
        .ticket {
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
            border: 2px solid #000;
            padding: 15mm;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
            color: #000;
        }
        
        .ticket-title {
            font-size: 18px;
            font-weight: bold;
            color: #666;
        }
        
        .booking-number {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin: 15px 0;
            padding: 10px;
            background: #f0f0f0;
            border: 2px solid #000;
            letter-spacing: 2px;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        
        .section {
            margin-bottom: 20px;
        }
        
        .section-title {
            font-weight: bold;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #000;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 8px 0;
            padding: 5px 0;
            border-bottom: 1px dotted #ccc;
        }
        
        .label {
            font-weight: bold;
            min-width: 120px;
        }
        
        .value {
            text-align: right;
            flex: 1;
        }
        
        .divider {
            border-top: 2px solid #000;
            margin: 20px 0;
        }
        
        .passengers-section {
            margin: 20px 0;
        }
        
        .passenger-item {
            background: #f9f9f9;
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #007bff;
        }
        
        .passenger-item .info-row {
            border-bottom: none;
            margin: 5px 0;
        }
        
        .seats-section {
            background: #f0f0f0;
            padding: 15px;
            margin: 15px 0;
            border: 1px solid #000;
        }
        
        .seat-list {
            font-size: 14px;
            font-weight: bold;
            text-align: center;
            padding: 10px;
        }
        
        .fare-section {
            background: #fff;
            border: 2px solid #000;
            padding: 15px;
            margin: 20px 0;
        }
        
        .fare-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .fare-row.total {
            font-weight: bold;
            font-size: 16px;
            border-top: 2px solid #000;
            border-bottom: 2px solid #000;
            padding: 10px 0;
            margin-top: 10px;
        }
        
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 2px solid #000;
            font-size: 11px;
        }
        
        .footer-title {
            font-weight: bold;
            font-size: 14px;
            margin-bottom: 10px;
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
        
        @media screen {
            .ticket {
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
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

        <!-- Main Content Grid -->
        <div class="content-grid">
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
                <div class="info-row">
                    <span class="label">Driver Phone:</span>
                    <span class="value">{{ $booking->trip->driver_phone }}</span>
                </div>
                @endif
                @endif
            </div>
        </div>

        <div class="divider"></div>

        <!-- Seats -->
        <div class="seats-section">
            <div class="section-title" style="margin-bottom: 10px;">Seats</div>
            <div class="seat-list">
                @foreach($booking->seats as $seat)
                    Seat {{ $seat->seat_number }}@if(!$loop->last), @endif
                @endforeach
            </div>
        </div>

        <div class="divider"></div>

        <!-- Passengers -->
        <div class="passengers-section">
            <div class="section-title">Passengers</div>
            @foreach($booking->passengers as $index => $passenger)
            <div class="passenger-item">
                <div class="info-row">
                    <span class="label">Passenger {{ $index + 1 }}:</span>
                    <span class="value">{{ $passenger->name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Gender:</span>
                    <span class="value">{{ ucfirst($passenger->gender->value) }}</span>
                </div>
                @if($passenger->age)
                <div class="info-row">
                    <span class="label">Age:</span>
                    <span class="value">{{ $passenger->age }} years</span>
                </div>
                @endif
                @if($passenger->phone)
                <div class="info-row">
                    <span class="label">Phone:</span>
                    <span class="value">{{ $passenger->phone }}</span>
                </div>
                @endif
                @if($ticketType === 'host' && $passenger->cnic)
                <div class="info-row">
                    <span class="label">CNIC:</span>
                    <span class="value">{{ $passenger->cnic }}</span>
                </div>
                @endif
                @if($passenger->email)
                <div class="info-row">
                    <span class="label">Email:</span>
                    <span class="value">{{ $passenger->email }}</span>
                </div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="divider"></div>

        @if($ticketType === 'host')
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
        <div class="fare-section">
            <div class="section-title">Fare Details</div>
            @php
                $baseFare = $booking->total_fare + ($booking->discount_amount ?? 0);
            @endphp
            <div class="fare-row">
                <span class="label">Base Fare:</span>
                <span class="value">PKR {{ number_format($baseFare, 2) }}</span>
            </div>
            @if($booking->discount_amount > 0)
            <div class="fare-row" style="color: #dc3545;">
                <span class="label">Discount:</span>
                <span class="value">-PKR {{ number_format($booking->discount_amount, 2) }}</span>
            </div>
            @endif
            <div class="fare-row">
                <span class="label">Total Fare:</span>
                <span class="value">PKR {{ number_format($booking->total_fare, 2) }}</span>
            </div>
            @if($booking->tax_amount > 0)
            <div class="fare-row">
                <span class="label">Tax/Charge:</span>
                <span class="value">+PKR {{ number_format($booking->tax_amount, 2) }}</span>
            </div>
            @endif
            <div class="fare-row total">
                <span class="label">Final Amount:</span>
                <span class="value">PKR {{ number_format($booking->final_amount, 2) }}</span>
            </div>
            <div class="fare-row" style="margin-top: 10px;">
                <span class="label">Payment Method:</span>
                <span class="value">{{ ucfirst($booking->payment_method ?? 'Cash') }}</span>
            </div>
            <div class="fare-row">
                <span class="label">Payment Status:</span>
                <span class="value">{{ ucfirst($booking->payment_status) }}</span>
            </div>
        </div>

        <div class="divider"></div>

        <!-- Status -->
        <div class="section">
            <div class="section-title">Booking Information</div>
            <div class="info-row">
                <span class="label">Status:</span>
                <span class="value">{{ ucfirst($booking->status) }}</span>
            </div>
            <div class="info-row">
                <span class="label">Booked On:</span>
                <span class="value">{{ $booking->created_at->format('d M Y, h:i A') }}</span>
            </div>
            @if($booking->channel)
            <div class="info-row">
                <span class="label">Channel:</span>
                <span class="value">{{ ucfirst($booking->channel) }}</span>
            </div>
            @endif
            @if($booking->booking_type)
            <div class="info-row">
                <span class="label">Booking Type:</span>
                <span class="value">{{ ucfirst($booking->booking_type) }}</span>
            </div>
            @endif
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="footer-title">Thank You for Choosing Us!</div>
            <div style="margin: 10px 0;">
                <strong>For queries call:</strong> {{ $settings->phone ?? 'N/A' }}
            </div>
            @if($settings->support_phone)
            <div style="margin: 5px 0;">
                <strong>Support:</strong> {{ $settings->support_phone }}
            </div>
            @endif
            @if($settings->email)
            <div style="margin: 5px 0;">
                <strong>Email:</strong> {{ $settings->email }}
            </div>
            @endif
            <div style="margin-top: 15px; font-size: 10px; color: #666;">
                This ticket is valid only for the date and route mentioned above. Please arrive at least 15 minutes before departure time.
            </div>
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

