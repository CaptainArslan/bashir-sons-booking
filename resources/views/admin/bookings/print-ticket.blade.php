<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ticket - {{ $booking->booking_number }}</title>
    <style>
        @media print {
            @page {
                size: 80mm auto;
                margin: 0;
            }
            body {
                margin: 0;
                padding: 0;
            }
            .no-print {
                display: none !important;
            }
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            width: 80mm;
            padding: 8mm;
            margin: 0 auto;
            background: #fff;
        }
        
        .ticket {
            width: 100%;
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
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 2mm;
            text-transform: uppercase;
        }
        
        .ticket-title {
            font-size: 11px;
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
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 1mm;
            border-bottom: 1px solid #000;
            padding-bottom: 1mm;
        }
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin: 1mm 0;
            font-size: 11px;
        }
        
        .label {
            font-weight: bold;
        }
        
        .value {
            text-align: right;
            flex: 1;
            margin-left: 5mm;
        }
        
        .seats {
            margin: 2mm 0;
        }
        
        .seat-list {
            font-size: 11px;
            margin-top: 1mm;
        }
        
        .footer {
            text-align: center;
            margin-top: 4mm;
            padding-top: 3mm;
            border-top: 1px dashed #000;
            font-size: 10px;
        }
        
        .barcode {
            text-align: center;
            margin: 3mm 0;
            font-family: 'Courier New', monospace;
        }
        
        .booking-number {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            text-align: center;
            margin: 3mm 0;
            padding: 2mm;
            background: #f0f0f0;
            border: 1px solid #000;
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
            <div class="ticket-title">BUS TICKET</div>
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
            <div class="info-row">
                <span class="label">Date:</span>
                <span class="value">{{ $booking->trip->departure_datetime->format('d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Departure:</span>
                <span class="value">{{ $booking->trip->departure_datetime->format('h:i A') }}</span>
            </div>
            @if($booking->trip->estimated_arrival_datetime)
            <div class="info-row">
                <span class="label">Arrival:</span>
                <span class="value">{{ $booking->trip->estimated_arrival_datetime->format('h:i A') }}</span>
            </div>
            @endif
            @if($booking->trip->bus)
            <div class="info-row">
                <span class="label">Bus:</span>
                <span class="value">{{ $booking->trip->bus->name ?? 'N/A' }}</span>
            </div>
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
            </div>
            @endforeach
        </div>

        <div class="divider"></div>

        <!-- Fare Information -->
        <div class="section">
            <div class="section-title">Fare</div>
            <div class="info-row">
                <span class="label">Total Fare:</span>
                <span class="value">PKR {{ number_format($booking->total_fare, 2) }}</span>
            </div>
            @if($booking->discount_amount > 0)
            <div class="info-row">
                <span class="label">Discount:</span>
                <span class="value">-PKR {{ number_format($booking->discount_amount, 2) }}</span>
            </div>
            @endif
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
        // Auto print on load
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>

