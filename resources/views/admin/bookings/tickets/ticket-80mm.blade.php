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
            
            .ticket-wrapper {
                width: 80mm !important;
                max-width: 80mm !important;
                min-width: 80mm !important;
                margin: 0 !important;
                padding: 2mm !important;
                box-sizing: border-box;
            }
        }
        
        @media screen {
            body {
                font-family: Arial, sans-serif;
                font-size: 10px;
                width: 80mm;
                max-width: 80mm;
                padding: 5mm;
                margin: 0 auto;
                background: #fff;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            width: 80mm;
            max-width: 80mm;
            padding: 5mm;
            margin: 0 auto;
            background: #fff;
        }
        
        .ticket-wrapper {
            width: 100%;
            max-width: 100%;
        }
        
        .customer-ticket {
            width: 100%;
            border: none;
            padding: 2mm;
            margin-bottom: 0;
        }
        
        .header {
            text-align: center;
            margin-bottom: 2mm;
        }
        
        .company-name {
            font-size: 11px;
            font-weight: bold;
            margin-bottom: 1mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .uan {
            font-size: 8px;
            margin-bottom: 1mm;
        }
        
        .social-media {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 3px;
            margin: 1mm 0;
            font-size: 8px;
        }
        
        .qr-code {
            width: 20px;
            height: 20px;
            background: #000;
            display: inline-block;
            margin-left: 3px;
        }
        
        .urdu-text {
            font-size: 7px;
            text-align: center;
            margin: 1mm 0;
            direction: rtl;
            font-family: 'Arial Unicode MS', 'Tahoma', sans-serif;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1mm;
            margin: 1mm 0;
            font-size: 8px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-weight: bold;
            font-size: 7px;
            margin-bottom: 0.5mm;
        }
        
        .info-value {
            font-size: 8px;
        }
        
        .full-width {
            grid-column: 1 / -1;
        }
        
        .perforated-line {
            border-top: 1px dashed #000;
            margin: 2mm 0;
            text-align: center;
            position: relative;
        }
        
        .perforated-line::before {
            content: '✂️';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 0 2mm;
            font-size: 6px;
        }
        
        .boarding-coupon {
            width: 100%;
            border: none;
            padding: 2mm;
            margin-top: 2mm;
        }
        
        .boarding-header {
            text-align: center;
            border-bottom: 1px solid #000;
            padding-bottom: 1mm;
            margin-bottom: 1mm;
        }
        
        .boarding-title {
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .instructions {
            font-size: 7px;
            margin: 1mm 0;
            direction: rtl;
            text-align: right;
            font-family: 'Arial Unicode MS', 'Tahoma', sans-serif;
        }
        
        .contact-info {
            text-align: center;
            font-size: 7px;
            margin-top: 1mm;
            padding-top: 1mm;
            border-top: 1px dashed #000;
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
        
        .status-badge {
            display: inline-block;
            padding: 1mm 2mm;
            background: #000;
            color: #fff;
            font-weight: bold;
            font-size: 7px;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Ticket
    </button>

    <div class="ticket-wrapper">
        @php
            $settings = \App\Models\GeneralSetting::first();
            $fromTripStop = $booking->trip->stops->firstWhere('terminal_id', $booking->fromStop->terminal_id);
            $toTripStop = $booking->trip->stops->firstWhere('terminal_id', $booking->toStop->terminal_id);
            $departureDateTime = $fromTripStop?->departure_at ?? $booking->trip->departure_datetime;
            $departureDate = $departureDateTime ? $departureDateTime->format('d-M-y') : 'N/A';
            $departureTime = $departureDateTime ? $departureDateTime->format('h:i A') : 'N/A';
            $departureDateShort = $departureDateTime ? $departureDateTime->format('d/m/Y') : 'N/A';
            $firstPassenger = $booking->passengers->first();
            $firstSeat = $booking->seats->whereNull('cancelled_at')->first() ?? $booking->seats->first();
            $busType = $booking->trip->bus->busType->name ?? 'N/A';
            $routeName = $booking->trip->route->name ?? 'N/A';
            $userId = $booking->booked_by_user_id ?? $booking->user_id ?? 'N/A';
        @endphp

        <!-- Customer Ticket Section -->
        <div class="customer-ticket">
            <!-- Header -->
            <div class="header">
                <div class="company-name">{{ $settings->company_name ?? 'BALOCH TRANSPORT SEF' }}</div>
                <div class="uan">UAN: {{ $settings->phone ?? '03-111-155-255' }}</div>
                <div class="social-media">
                    <span>Follow Us On</span>
                    <span>📘</span>
                    <span>📷</span>
                    <span>🎵</span>
                    <span class="qr-code"></span>
                </div>
                <div class="urdu-text">
                    Download Baloch Transport App & Buy Tickets Online<br>
                    اب گھر بیٹھے آپ سے ٹکٹ خریدے اور اپنی پسند کی سیٹ بک کریں
                </div>
            </div>

            <!-- Ticket Information Grid -->
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Seat No.:</span>
                    <span class="info-value">{{ $firstSeat->seat_number ?? 'N/A' }}@if($firstPassenger && $firstPassenger->gender) ({{ ucfirst($firstPassenger->gender->value ?? $firstPassenger->gender) }})@endif</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Dep Time:</span>
                    <span class="info-value">{{ $departureTime }} {{ $departureDate }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $firstPassenger->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">From City:</span>
                    <span class="info-value">{{ $booking->fromStop->terminal->city->name ?? $booking->fromStop->terminal->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Destination:</span>
                    <span class="info-value">{{ $booking->toStop->terminal->city->name ?? $booking->toStop->terminal->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Fare:</span>
                    <span class="info-value">{{ number_format($booking->final_amount, 0) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Class:</span>
                    <span class="info-value">{{ strtoupper($busType) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Terminal:</span>
                    <span class="info-value">{{ strtoupper($booking->fromStop->terminal->name) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Print Time:</span>
                    <span class="info-value">{{ $booking->created_at->format('h:i A d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Route Time:</span>
                    <span class="info-value">{{ $departureTime }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Ticket #:</span>
                    <span class="info-value">{{ $booking->booking_number }}-{{ $firstSeat->seat_number ?? '' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">User ID:</span>
                    <span class="info-value">{{ $userId }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">BUS #:</span>
                    <span class="info-value">{{ $booking->trip->bus->name ?? 'N/A' }}</span>
                </div>
            </div>

            <!-- Instructions in Urdu -->
            <div class="instructions">
                مسافر کو بس کی روانگی سے 10 منٹ پہلے بس میں سوار<br>
                اس کی روانگی کے بعد ٹکٹ واپس یا تبدیل نہیں کی جانے<br>
                ٹکٹ ریفنڈ کرنے پر 0
            </div>

            <!-- Contact Information -->
            <div class="contact-info">
                <div style="font-weight: bold; margin-bottom: 0.5mm;">For any Complaint Call or Whatsapp Us</div>
                <div>Call: {{ $settings->phone ?? '03-111-155-255' }} SMS: {{ $settings->support_phone ?? '0300-8439655' }}</div>
            </div>
        </div>

        <!-- Perforated Line -->
        <div class="perforated-line"></div>

        <!-- Boarding Coupon (Host Copy) -->
        <div class="boarding-coupon">
            <div class="boarding-header">
                <div class="company-name" style="font-size: 9px;">{{ $settings->company_name ?? 'Baloch Transport Services' }}</div>
                <div class="boarding-title">BOARDING COUPON</div>
            </div>

            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Name:</span>
                    <span class="info-value">{{ $firstPassenger->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Dep Time:</span>
                    <span class="info-value">{{ $departureTime }} {{ $departureDateShort }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">FROM:</span>
                    <span class="info-value">{{ $booking->fromStop->terminal->city->name ?? $booking->fromStop->terminal->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">TO:</span>
                    <span class="info-value">{{ $booking->toStop->terminal->city->name ?? $booking->toStop->terminal->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">SEAT NO:</span>
                    <span class="info-value">{{ $firstSeat->seat_number ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">FARE:</span>
                    <span class="info-value">{{ number_format($booking->final_amount, 0) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Terminal:</span>
                    <span class="info-value">{{ strtoupper($booking->fromStop->terminal->name) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">BUS#:</span>
                    <span class="info-value">{{ $booking->trip->bus->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Ticket #:</span>
                    <span class="info-value">{{ $booking->booking_number }}-{{ $firstSeat->seat_number ?? '' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Print Time:</span>
                    <span class="info-value">{{ $booking->created_at->format('h:i A d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">STATUS:</span>
                    <span class="info-value"><span class="status-badge">{{ strtoupper($booking->status) }}</span></span>
                </div>
                <div class="info-item">
                    <span class="info-label">USER ID:</span>
                    <span class="info-value">{{ $userId }}</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.addEventListener('beforeprint', function() {
            document.body.style.width = '80mm';
            document.body.style.maxWidth = '80mm';
            const ticket = document.querySelector('.ticket-wrapper');
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
