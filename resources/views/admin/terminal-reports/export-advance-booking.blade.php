<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Advance Booking Report</title>
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
        .date-header {
            background-color: #e0e0e0;
            color: #000;
            font-weight: bold;
            text-align: center;
            padding: 5px;
            font-size: 11px;
            margin-top: 10px;
            margin-bottom: 5px;
            border: 1px solid #000;
        }
        .route-time-header {
            background-color: #e0e0e0;
            font-weight: bold;
            padding: 4px;
            font-size: 9px;
            margin-top: 5px;
            margin-bottom: 2px;
        }
        .subtotal-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .subtotal-row td {
            text-align: right;
            padding: 4px;
        }
        .daily-total-row {
            background-color: #e0e0e0;
            color: #000;
            font-weight: bold;
            text-align: right;
            padding: 5px;
            margin-top: 5px;
            margin-bottom: 2px;
            font-size: 10px;
            border: 1px solid #000;
        }
        .daily-total-row-duplicate {
            background-color: #fff;
            color: #000;
            font-weight: bold;
            text-align: right;
            padding: 5px;
            margin-bottom: 10px;
            font-size: 10px;
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
            <div class="report-type">Terminal Report</div>
            <div class="date-range">From {{ $start_date->format('Y-m-d') }} To {{ $end_date->format('Y-m-d') }}</div>
        </div>
    </div>

    @php
        $overallTotalPax = 0;
        $overallTotalFare = 0;
    @endphp

    @foreach($grouped_bookings as $date => $routeGroups)
        @php
            $dateFormatted = Carbon\Carbon::parse($date)->format('d-m-Y');
            $dailyTotalPax = 0;
            $dailyTotalFare = 0;
        @endphp

        <div class="date-header">{{ $dateFormatted }}</div>

        @foreach($routeGroups as $routeTime => $bookingsInGroup)
            @php
                $groupTotalPax = 0;
                $groupTotalFare = 0;
            @endphp

            <div class="route-time-header">{{ $routeTime }}</div>
            
            <table>
                <thead>
                    <tr>
                        <th style="width: 8%;">Time</th>
                        <th style="width: 6%;">Terminal</th>
                        <th style="width: 6%;">GOJ</th>
                        <th style="width: 5%;">Seat</th>
                        <th style="width: 15%;">Name</th>
                        <th style="width: 12%;">CNIC</th>
                        <th style="width: 10%;">Cell</th>
                        <th style="width: 10%;">By</th>
                        <th style="width: 6%;">To</th>
                        <th style="width: 8%;">Fare</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookingsInGroup as $booking)
                        @php
                            $activeSeats = $booking->seats->whereNull('cancelled_at');
                            $passengers = $booking->passengers;
                            $seatCount = $activeSeats->count();
                            $passengerCount = $passengers->count();
                            $routeTimeParts = explode(' ', $routeTime);
                            $timeOnly = isset($routeTimeParts[1]) ? $routeTimeParts[1] : '';
                        @endphp
                        @if($passengerCount > 0)
                            @foreach($passengers as $index => $passenger)
                                @php
                                    $seat = $activeSeats->get($index);
                                    $groupTotalPax++;
                                    $dailyTotalPax++;
                                    $overallTotalPax++;
                                    if($index === 0) {
                                        $groupTotalFare += $booking->final_amount;
                                        $dailyTotalFare += $booking->final_amount;
                                        $overallTotalFare += $booking->final_amount;
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center">{{ $timeOnly }}</td>
                                    <td class="text-center">{{ $booking->fromStop->terminal->code ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $booking->fromStop->terminal->code ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $seat->seat_number ?? '-' }}</td>
                                    <td>{{ $passenger->name ?? 'N/A' }}</td>
                                    <td>{{ $passenger->cnic ?? '-' }}</td>
                                    <td>{{ $passenger->phone ?? '-' }}</td>
                                    <td>{{ $booking->bookedByUser->name ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $booking->toStop->terminal->code ?? 'N/A' }}</td>
                                    <td class="text-right">{{ number_format($booking->final_amount, 0) }}</td>
                                </tr>
                            @endforeach
                        @else
                            @php
                                $groupTotalPax += $seatCount;
                                $groupTotalFare += $booking->final_amount;
                                $dailyTotalPax += $seatCount;
                                $dailyTotalFare += $booking->final_amount;
                                $overallTotalPax += $seatCount;
                                $overallTotalFare += $booking->final_amount;
                            @endphp
                            @foreach($activeSeats as $seat)
                                <tr>
                                    <td class="text-center">{{ $timeOnly }}</td>
                                    <td class="text-center">{{ $booking->fromStop->terminal->code ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $booking->fromStop->terminal->code ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $seat->seat_number ?? '-' }}</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>-</td>
                                    <td>{{ $booking->bookedByUser->name ?? 'N/A' }}</td>
                                    <td class="text-center">{{ $booking->toStop->terminal->code ?? 'N/A' }}</td>
                                    <td class="text-right">{{ number_format($booking->final_amount, 0) }}</td>
                                </tr>
                            @endforeach
                        @endif
                    @endforeach
                    <tr class="subtotal-row">
                        <td colspan="9" class="text-right"><strong>{{ $groupTotalPax }} Pax</strong></td>
                        <td class="text-right"><strong>{{ number_format($groupTotalFare, 0) }}</strong></td>
                    </tr>
                </tbody>
            </table>
        @endforeach

        <div class="daily-total-row">
            <strong>Total {{ $dailyTotalPax }} Pax {{ number_format($dailyTotalFare, 0) }}</strong>
        </div>
        <div class="daily-total-row-duplicate">
            <strong>Total {{ $dailyTotalPax }} Pax {{ number_format($dailyTotalFare, 0) }}</strong>
        </div>
    @endforeach

    <div class="overall-total">
        Overall Total: {{ $overallTotalPax }} Pax, {{ number_format($overallTotalFare, 0) }} PKR
    </div>
</body>
</html>
