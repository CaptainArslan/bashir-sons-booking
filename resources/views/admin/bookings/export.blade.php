<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bookings Report</title>
    <style>
        @media print {
            @page {
                margin: 0.5cm;
                size: A4 landscape;
            }
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #000;
            line-height: 1.3;
            margin: 0;
            padding: 10px;
            background: #fff;
        }
        .report-title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 8px;
            padding: 5px 0;
            color: #000;
            text-transform: uppercase;
            border-bottom: 2px solid #000;
        }
        .header-section {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 10px;
            margin-bottom: 8px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }
        .header-center {
            text-align: center;
        }
        .service-name {
            font-size: 16px;
            font-weight: bold;
            margin-bottom: 5px;
            color: #1a1a1a;
            text-transform: uppercase;
        }
        .report-type {
            font-size: 14px;
            color: #000;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .date-range {
            font-size: 11px;
            margin-bottom: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            margin-bottom: 8px;
            font-size: 8px;
        }
        th, td {
            border: 0.5px solid #333;
            padding: 3px 2px;
            text-align: left;
        }
        th {
            background-color: #e0e0e0;
            color: #000;
            font-weight: bold;
            text-align: center;
            font-size: 7px;
            padding: 4px 2px;
            border: 1px solid #000;
        }
        tbody td {
            font-size: 7px;
            padding: 3px 2px;
        }
        tbody tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .overall-total {
            margin-top: 15px;
            text-align: center;
            font-weight: bold;
            font-size: 12px;
            padding: 8px;
            background-color: #e0e0e0;
            border: 1px solid #000;
        }
    </style>
</head>
<body>
    <div class="report-title">{{ $company_name ?? 'Bashir Sons Travel' }}</div>
    
    <div class="header-section">
        <div class="header-center">
            <div class="report-type">Bookings Report</div>
            <div class="date-range">From {{ $start_date->format('Y-m-d') }} To {{ $end_date->format('Y-m-d') }}</div>
        </div>
    </div>

    <!-- Applied Filters Section -->
    <div style="margin-bottom: 15px; padding: 10px; background-color: #e0e0e0; border: 2px solid #000; font-size: 9px;">
        <div style="font-weight: bold; font-size: 10px; margin-bottom: 8px; text-decoration: underline;">APPLIED FILTERS FOR THIS REPORT:</div>
        <table style="width: 100%; border-collapse: collapse; font-size: 8px; margin: 0;">
            <tr>
                <td style="padding: 3px 5px; border: 1px solid #000; width: 20%; font-weight: bold; background-color: #f0f0f0;">Date Range:</td>
                <td style="padding: 3px 5px; border: 1px solid #000;">
                    {{ $start_date->format('Y-m-d') }}
                    @if(isset($filters['start_time']) && $filters['start_time'])
                        {{ $filters['start_time'] }}
                    @else
                        (All Day)
                    @endif
                    to {{ $end_date->format('Y-m-d') }}
                    @if(isset($filters['end_time']) && $filters['end_time'])
                        {{ $filters['end_time'] }}
                    @else
                        (All Day)
                    @endif
                </td>
            </tr>
            @if(isset($filters['from_terminal_name']))
            <tr>
                <td style="padding: 3px 5px; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">From Terminal:</td>
                <td style="padding: 3px 5px; border: 1px solid #000;">{{ $filters['from_terminal_name'] }}</td>
            </tr>
            @endif
            @if(isset($filters['to_terminal_name']))
            <tr>
                <td style="padding: 3px 5px; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">To Terminal:</td>
                <td style="padding: 3px 5px; border: 1px solid #000;">{{ $filters['to_terminal_name'] }}</td>
            </tr>
            @endif
            @if(isset($filters['status']))
            <tr>
                <td style="padding: 3px 5px; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">Booking Status:</td>
                <td style="padding: 3px 5px; border: 1px solid #000;">{{ ucfirst($filters['status']) }}</td>
            </tr>
            @endif
            @if(isset($filters['payment_status']))
            <tr>
                <td style="padding: 3px 5px; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">Payment Status:</td>
                <td style="padding: 3px 5px; border: 1px solid #000;">{{ ucfirst($filters['payment_status']) }}</td>
            </tr>
            @endif
            @if(isset($filters['channel']))
            <tr>
                <td style="padding: 3px 5px; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">Channel:</td>
                <td style="padding: 3px 5px; border: 1px solid #000;">{{ ucfirst($filters['channel']) }}</td>
            </tr>
            @endif
            @if(isset($filters['is_advance']) && $filters['is_advance'] !== '')
            <tr>
                <td style="padding: 3px 5px; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">Booking Type:</td>
                <td style="padding: 3px 5px; border: 1px solid #000;">{{ $filters['is_advance'] === '1' ? 'Advance Bookings Only' : 'Regular Bookings Only' }}</td>
            </tr>
            @endif
            @if(isset($filters['employee_name']))
            <tr>
                <td style="padding: 3px 5px; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">Employee (Booked By):</td>
                <td style="padding: 3px 5px; border: 1px solid #000;">{{ $filters['employee_name'] }}</td>
            </tr>
            @endif
            @if(isset($filters['customer_name']))
            <tr>
                <td style="padding: 3px 5px; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">Customer/User:</td>
                <td style="padding: 3px 5px; border: 1px solid #000;">{{ $filters['customer_name'] }}</td>
            </tr>
            @endif
            @if(isset($filters['booking_number']))
            <tr>
                <td style="padding: 3px 5px; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">Booking Number:</td>
                <td style="padding: 3px 5px; border: 1px solid #000;">{{ $filters['booking_number'] }}</td>
            </tr>
            @endif
            <tr>
                <td style="padding: 3px 5px; border: 1px solid #000; font-weight: bold; background-color: #f0f0f0;">Default Filter:</td>
                <td style="padding: 3px 5px; border: 1px solid #000;">Only Confirmed Bookings (Default)</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 6%;">Booking #</th>
                <th style="width: 8%;">Date & Time</th>
                <th style="width: 8%;">Route</th>
                <th style="width: 12%;">Passengers</th>
                <th style="width: 6%;">Seats</th>
                <th style="width: 6%;">Channel</th>
                <th style="width: 6%;">Status</th>
                <th style="width: 6%;">Type</th>
                <th style="width: 5%;">Advance</th>
                <th style="width: 8%;">Amount</th>
                <th style="width: 6%;">Payment</th>
                <th style="width: 10%;">Employee</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalAmount = 0;
            @endphp
            @foreach($bookings as $booking)
                @php
                    $totalAmount += $booking->final_amount;
                    $passengerNames = $booking->passengers->pluck('name')->join(', ');
                    $seatNumbers = $booking->seats->whereNull('cancelled_at')->pluck('seat_number')->join(', ');
                @endphp
                <tr>
                    <td class="text-center">{{ $booking->booking_number }}</td>
                    <td class="text-center">{{ $booking->created_at->format('d M Y, H:i') }}</td>
                    <td class="text-center">{{ $booking->fromStop?->terminal?->code ?? 'N/A' }} → {{ $booking->toStop?->terminal?->code ?? 'N/A' }}</td>
                    <td>{{ $passengerNames ?: 'N/A' }}</td>
                    <td class="text-center">{{ $seatNumbers ?: '-' }}</td>
                    <td class="text-center">{{ ucfirst($booking->channel ?? 'N/A') }}</td>
                    <td class="text-center">{{ ucfirst($booking->status ?? 'N/A') }}</td>
                    <td class="text-center">{{ ucfirst($booking->booking_type ?? 'Regular') }}</td>
                    <td class="text-center">{{ $booking->is_advance ? 'Yes' : 'No' }}</td>
                    <td class="text-right">{{ number_format($booking->final_amount, 0) }}</td>
                    <td class="text-center">{{ ucfirst($booking->payment_status ?? 'N/A') }}</td>
                    <td>{{ $booking->bookedByUser?->name ?? 'N/A' }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr style="background-color: #e0e0e0; font-weight: bold;">
                <td colspan="9" class="text-right">Total Bookings: {{ $bookings->count() }}</td>
                <td class="text-right">{{ number_format($totalAmount, 0) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <div class="overall-total">
        Generated on: {{ $generated_at }}
    </div>
</body>
</html>

