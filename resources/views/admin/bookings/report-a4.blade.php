<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Report - {{ $booking->booking_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        @media print {
            @page {
                size: A4;
                margin: 15mm;
            }
            
            .no-print {
                display: none !important;
            }
        }
        
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            line-height: 1.5;
            color: #000;
            background: #fff;
            padding: 20px;
        }
        
        .report-container {
            max-width: 210mm;
            margin: 0 auto;
            background: #fff;
        }
        
        .header {
            text-align: center;
            border-bottom: 3px solid #000;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 5px;
            text-transform: uppercase;
        }
        
        .report-title {
            font-size: 18px;
            font-weight: bold;
            margin-top: 10px;
        }
        
        .report-info {
            display: flex;
            justify-content: space-between;
            margin-top: 15px;
            font-size: 11px;
        }
        
        .section {
            margin: 20px 0;
            page-break-inside: avoid;
        }
        
        .section-title {
            font-size: 14px;
            font-weight: bold;
            border-bottom: 2px solid #000;
            padding-bottom: 5px;
            margin-bottom: 10px;
            text-transform: uppercase;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin: 10px 0;
        }
        
        .info-item {
            display: flex;
            margin: 5px 0;
        }
        
        .info-label {
            font-weight: bold;
            width: 150px;
            flex-shrink: 0;
        }
        
        .info-value {
            flex: 1;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        
        .table th,
        .table td {
            border: 1px solid #000;
            padding: 8px;
            text-align: left;
        }
        
        .table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        
        .table td {
            font-size: 11px;
        }
        
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #000;
            text-align: center;
            font-size: 10px;
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
        
        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }
        
        .signature-box {
            width: 200px;
            border-top: 1px solid #000;
            padding-top: 5px;
            text-align: center;
            font-size: 10px;
        }
    </style>
</head>
<body>
    <button class="print-btn no-print" onclick="window.print()">
        <i class="fas fa-print"></i> Print Report
    </button>

    <div class="report-container">
        @php
            $settings = \App\Models\GeneralSetting::first();
            $fromTripStop = $booking->trip->stops->firstWhere('terminal_id', $booking->fromStop->terminal_id);
            $toTripStop = $booking->trip->stops->firstWhere('terminal_id', $booking->toStop->terminal_id);
            
            $departureDate = $fromTripStop?->departure_at?->format('d M Y') ?? $booking->trip->departure_datetime->format('d M Y');
            $departureTime = $fromTripStop?->departure_at?->format('h:i A') ?? $booking->trip->departure_datetime->format('h:i A');
            $arrivalTime = $toTripStop?->arrival_at?->format('h:i A');
        @endphp

        <div class="header">
            <div class="company-name">{{ $settings->company_name ?? 'TRANSPORT SERVICE' }}</div>
            <div class="report-title">BOOKING REPORT</div>
            <div class="report-info">
                <div>Report Date: {{ now()->format('d M Y, h:i A') }}</div>
                <div>Booking Number: <strong>{{ $booking->booking_number }}</strong></div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Booking Information</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">Booking Number:</span>
                    <span class="info-value">{{ $booking->booking_number }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Booking Date:</span>
                    <span class="info-value">{{ $booking->created_at->format('d M Y, h:i A') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status:</span>
                    <span class="info-value">{{ ucfirst($booking->status) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Channel:</span>
                    <span class="info-value">{{ ucfirst($booking->channel ?? 'N/A') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Method:</span>
                    <span class="info-value">{{ ucfirst($booking->payment_method ?? 'Cash') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Payment Status:</span>
                    <span class="info-value">{{ ucfirst($booking->payment_status) }}</span>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-title">Route & Trip Details</div>
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">From Terminal:</span>
                    <span class="info-value">{{ $booking->fromStop->terminal->name }} ({{ $booking->fromStop->terminal->code }})</span>
                </div>
                <div class="info-item">
                    <span class="info-label">To Terminal:</span>
                    <span class="info-value">{{ $booking->toStop->terminal->name }} ({{ $booking->toStop->terminal->code }})</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Route:</span>
                    <span class="info-value">{{ $booking->trip->route->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Travel Date:</span>
                    <span class="info-value">{{ $departureDate }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Departure Time:</span>
                    <span class="info-value">{{ $departureTime }}</span>
                </div>
                @if($arrivalTime)
                <div class="info-item">
                    <span class="info-label">Arrival Time:</span>
                    <span class="info-value">{{ $arrivalTime }}</span>
                </div>
                @endif
                @if($booking->trip->bus)
                <div class="info-item">
                    <span class="info-label">Bus:</span>
                    <span class="info-value">{{ $booking->trip->bus->name ?? 'N/A' }}</span>
                </div>
                @endif
                @if($booking->trip->driver_name)
                <div class="info-item">
                    <span class="info-label">Driver:</span>
                    <span class="info-value">{{ $booking->trip->driver_name }}</span>
                </div>
                @endif
                @if($booking->trip->driver_phone)
                <div class="info-item">
                    <span class="info-label">Driver Phone:</span>
                    <span class="info-value">{{ $booking->trip->driver_phone }}</span>
                </div>
                @endif
            </div>
        </div>

        <div class="section">
            <div class="section-title">Passengers & Seats</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Sr#</th>
                        <th>Name</th>
                        <th>Gender</th>
                        <th>Age</th>
                        <th>Seat Number</th>
                        <th>CNIC</th>
                        <th>Phone</th>
                        <th>Email</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($booking->passengers as $index => $passenger)
                    @php
                        $seat = $booking->seats->skip($index)->first();
                    @endphp
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $passenger->name }}</td>
                        <td>{{ ucfirst($passenger->gender->value ?? 'N/A') }}</td>
                        <td>{{ $passenger->age ?? 'N/A' }}</td>
                        <td>{{ $seat->seat_number ?? 'N/A' }}</td>
                        <td>{{ $passenger->cnic ?? 'N/A' }}</td>
                        <td>{{ $passenger->phone ?? 'N/A' }}</td>
                        <td>{{ $passenger->email ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="section">
            <div class="section-title">Fare Details</div>
            <table class="table">
                <thead>
                    <tr>
                        <th>Description</th>
                        <th style="text-align: right;">Amount (PKR)</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $baseFare = $booking->total_fare + ($booking->discount_amount ?? 0);
                    @endphp
                    <tr>
                        <td>Base Fare</td>
                        <td style="text-align: right;">{{ number_format($baseFare, 2) }}</td>
                    </tr>
                    @if($booking->discount_amount > 0)
                    <tr>
                        <td>Discount</td>
                        <td style="text-align: right;">-{{ number_format($booking->discount_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr>
                        <td>Subtotal</td>
                        <td style="text-align: right;">{{ number_format($booking->total_fare, 2) }}</td>
                    </tr>
                    @if($booking->tax_amount > 0)
                    <tr>
                        <td>Tax/Charges</td>
                        <td style="text-align: right;">{{ number_format($booking->tax_amount, 2) }}</td>
                    </tr>
                    @endif
                    <tr style="font-weight: bold; background-color: #f0f0f0;">
                        <td>Total Amount</td>
                        <td style="text-align: right;">{{ number_format($booking->final_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($booking->notes)
        <div class="section">
            <div class="section-title">Notes</div>
            <div style="padding: 10px; background-color: #f9f9f9; border: 1px solid #ddd;">
                {{ $booking->notes }}
            </div>
        </div>
        @endif

        <div class="footer">
            <div style="margin-bottom: 10px;">
                <strong>{{ $settings->company_name ?? 'TRANSPORT SERVICE' }}</strong>
            </div>
            <div>Address: {{ $settings->address ?? 'N/A' }}</div>
            <div>Phone: {{ $settings->phone ?? 'N/A' }}</div>
            @if($settings->support_phone)
            <div>Support: {{ $settings->support_phone }}</div>
            @endif
            @if($settings->email)
            <div>Email: {{ $settings->email }}</div>
            @endif
            <div style="margin-top: 15px; font-size: 9px;">
                This is a computer-generated report. Valid only for the date and route mentioned.
            </div>
        </div>

        <div class="signature-section">
            <div class="signature-box">
                <div>Prepared By</div>
                <div style="margin-top: 30px;">{{ $booking->user->name ?? 'N/A' }}</div>
            </div>
            <div class="signature-box">
                <div>Authorized Signature</div>
                <div style="margin-top: 30px;">_________________</div>
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

