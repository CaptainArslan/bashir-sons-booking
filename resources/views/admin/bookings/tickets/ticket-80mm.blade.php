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
                font-family: 'Arial', 'Helvetica', sans-serif;
                font-size: 7px;
                line-height: 1.2;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            
            .no-print {
                display: none !important;
            }
            
            .ticket-wrapper {
                width: 80mm !important;
                max-width: 80mm !important;
                min-width: 80mm !important;
                margin: 0 !important;
                padding: 1.5mm 2mm !important;
                box-sizing: border-box;
            }
        }
        
        @media screen {
            body {
                font-family: 'Arial', 'Helvetica', sans-serif;
                font-size: 10px;
                width: 80mm;
                max-width: 80mm;
                padding: 5mm;
                margin: 0 auto;
                background: #f5f5f5;
            }
        }
        
        body {
            font-family: 'Arial', 'Helvetica', sans-serif;
            font-size: 8px;
            width: 80mm;
            max-width: 80mm;
            padding: 3mm;
            margin: 0 auto;
            background: #fff;
        }
        
        .ticket-wrapper {
            width: 100%;
            max-width: 100%;
            background: #fff;
        }
        
        .customer-ticket {
            width: 100%;
            border: 1.5px solid #000;
            border-radius: 1px;
            padding: 1.5mm;
            margin-bottom: 0;
            background: #fff;
        }
        
        .header {
            text-align: center;
            margin-bottom: 1mm;
            padding-bottom: 0.5mm;
        }
        
        .company-name {
            font-size: 10px;
            font-weight: 900;
            margin-bottom: 0.5mm;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000;
            line-height: 1.1;
        }
        
        .uan {
            font-size: 7px;
            margin-bottom: 0.5mm;
            font-weight: 600;
            color: #000;
        }
        
        .urdu-text {
            font-size: 6px;
            text-align: center;
            margin: 0.5mm 0 0 0;
            direction: rtl;
            font-family: 'Arial Unicode MS', 'Tahoma', 'Nafees Web Naskh', sans-serif;
            line-height: 1.2;
            padding: 0.8mm;
            background: #fff;
            border: 0.5px solid #000;
            border-radius: 1px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.8mm 1.2mm;
            margin: 1mm 0;
            font-size: 7px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
            padding: 0.3mm 0;
        }
        
        .info-label {
            font-weight: 700;
            font-size: 6px;
            margin-bottom: 0.2mm;
            color: #000;
            text-transform: uppercase;
            letter-spacing: 0.2px;
        }
        
        .info-value {
            font-size: 7px;
            font-weight: 600;
            color: #000;
            word-break: break-word;
            line-height: 1.2;
        }
        
        .perforated-line {
            border-top: 1px dashed #000;
            margin: 1mm 0;
            text-align: center;
            position: relative;
            padding: 0.5mm 0;
        }
        
        .perforated-line::before {
            content: 'CUT';
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 0 2mm;
            font-size: 5px;
            font-weight: bold;
            top: -4px;
            letter-spacing: 0.3px;
        }
        
        .boarding-coupon {
            width: 100%;
            border: 1.5px solid #000;
            border-radius: 1px;
            padding: 1.5mm;
            margin-top: 1mm;
            background: #fff;
        }
        
        .boarding-header {
            text-align: center;
            padding-bottom: 0.5mm;
            margin-bottom: 1mm;
        }
        
        .boarding-company-name {
            font-size: 8px;
            font-weight: 800;
            margin-bottom: 0.3mm;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .boarding-title {
            font-size: 8px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #000;
        }
        
        .instructions {
            font-size: 6px;
            margin: 1mm 0;
            direction: rtl;
            text-align: right;
            font-family: 'Arial Unicode MS', 'Tahoma', 'Nafees Web Naskh', sans-serif;
            line-height: 1.3;
            padding: 0.8mm;
            background: #fff;
            border: 0.5px solid #000;
            border-radius: 1px;
        }
        
        .contact-info {
            text-align: center;
            font-size: 6px;
            margin-top: 1mm;
            padding-top: 0.5mm;
            line-height: 1.2;
        }
        
        .contact-info > div:first-child {
            font-weight: 700;
            margin-bottom: 0.3mm;
            font-size: 6px;
        }
        
        .contact-info > div:last-child {
            font-size: 6px;
            font-weight: 600;
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
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            transition: all 0.3s;
        }
        
        .print-btn:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.3);
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.5mm 1.5mm;
            background: #000;
            color: #fff;
            font-weight: 900;
            font-size: 6px;
            border-radius: 1px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }
        
        .highlight-box {
            background: #f0f0f0;
            border: 0.5px solid #000;
            padding: 0.8mm;
            margin: 0.5mm 0;
            border-radius: 1px;
        }
        
        .section-heading {
            font-size: 7px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin: 1mm 0 0.5mm 0;
            color: #000;
        }
        
        .info-item.full-width {
            grid-column: 1 / -1;
        }
        
        @media print {
            .customer-ticket,
            .boarding-coupon {
                border: 2px solid #000 !important;
                background: #fff !important;
            }
            
            .urdu-text {
                background: #fff !important;
                border: 1px solid #000 !important;
            }
            
            .instructions {
                background: #fff !important;
                border: 1px solid #000 !important;
            }
            
            .status-badge {
                background: #000 !important;
                color: #fff !important;
                border: 1px solid #000 !important;
            }
            
            .section-heading {
                color: #000 !important;
            }
            
            .info-label,
            .info-value {
                color: #000 !important;
            }
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
            $bookedByUser = $booking->bookedByUser ?? $booking->user;
            $bookedByName = $bookedByUser ? $bookedByUser->name : 'N/A';
        @endphp

        <!-- Customer Ticket Section -->
        <div class="customer-ticket">
            <!-- Header -->
            <div class="header">
                <div class="company-name">{{ $settings->company_name ?? 'BALOCH TRANSPORT SEF' }}</div>
                <div class="uan">UAN: {{ $settings->phone ?? '03-111-155-255' }}</div>
                <div class="urdu-text">
                    Download {{ $settings->company_name ?? 'Bashir Sons Travels' }} App & Buy Tickets Online<br>
                    اب گھر بیٹھے آپ سے ٹکٹ خریدے اور اپنی پسند کی سیٹ بک کریں
                </div>
            </div>

            <!-- Passenger Details Section -->
            <div class="section-heading">Passenger Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Passenger Name:</span>
                    <span class="info-value">{{ $firstPassenger->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Seat No.:</span>
                    <span class="info-value">{{ $firstSeat->seat_number ?? 'N/A' }}@if($firstPassenger && $firstPassenger->gender) ({{ ucfirst($firstPassenger->gender->value ?? $firstPassenger->gender) }})@endif</span>
                </div>
            </div>

            <!-- Journey Details Section -->
            <div class="section-heading">Journey Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">From City:</span>
                    <span class="info-value">{{ $booking->fromStop->terminal->city->name ?? $booking->fromStop->terminal->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Destination:</span>
                    <span class="info-value">{{ $booking->toStop->terminal->city->name ?? $booking->toStop->terminal->name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Departure Time:</span>
                    <span class="info-value">{{ $departureTime }} {{ $departureDate }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Terminal:</span>
                    <span class="info-value">{{ strtoupper($booking->fromStop->terminal->name) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Bus #:</span>
                    <span class="info-value">{{ $booking->trip->bus->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Class:</span>
                    <span class="info-value">{{ strtoupper($busType) }}</span>
                </div>
            </div>

            <!-- Payment Details Section -->
            <div class="section-heading">Payment Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Total Fare:</span>
                    <span class="info-value">PKR {{ number_format($booking->final_amount, 0) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Method:</span>
                    <span class="info-value">{{ strtoupper($booking->payment_method ?? 'CASH') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Status:</span>
                    <span class="info-value">{{ strtoupper($booking->payment_status ?? 'UNPAID') }}</span>
                </div>
            </div>

            <!-- Booking Details Section -->
            <div class="section-heading">Booking Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Ticket #:</span>
                    <span class="info-value">{{ $booking->booking_number }}-{{ $firstSeat->seat_number ?? '' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Booked By:</span>
                    <span class="info-value">{{ $bookedByName }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Channel:</span>
                    <span class="info-value">{{ strtoupper($booking->channel ?? 'COUNTER') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="info-value">{{ strtoupper($booking->status) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Print Time:</span>
                    <span class="info-value">{{ $booking->created_at->format('h:i A d/m/Y') }}</span>
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
                <div>For any Complaint Call or Whatsapp Us</div>
                <div>Call: {{ $settings->phone ?? '03-111-155-255' }} SMS: {{ $settings->support_phone ?? '0300-8439655' }}</div>
            </div>
        </div>

        <!-- Perforated Line -->
        <div class="perforated-line"></div>

        <!-- Boarding Coupon (Host Copy) -->
        <div class="boarding-coupon">
            <div class="boarding-header">
                <div class="boarding-company-name">{{ $settings->company_name ?? 'Bas Transport Services' }}</div>
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
                    <span class="info-value">PKR {{ number_format($booking->final_amount, 0) }}</span>
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
