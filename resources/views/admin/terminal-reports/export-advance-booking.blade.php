<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terminal Booking Report</title>

    <style>
        /* Base Styles - Font Size 14px */
        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 14px;
            margin: 20px auto;
            padding: 10px;
            background-color: #fff;
            color: #000;
        }

        strong {
            font-weight: 900;
        }

        .text-right {
            text-align: right !important;
        }

        /* --- Header Section (B. S / Daewoo) --- */
        .report-header {
            text-align: center;
            line-height: 1.1;
            margin-bottom: 5px;
        }

        .report-header .bs {
            font-size: 30px;
            font-weight: bold;
        }

        .report-header .title {
            font-size: 30px;
            font-weight: 900;
        }

        .page-num {
            position: absolute;
            right: 20px;
            top: 10px;
            font-size: 10px;
        }

        /* --- Filter and Date Row --- */
        .filter-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            margin-bottom: 10px;
            border-bottom: 1px solid #000;
            padding-bottom: 5px;
        }

        .filter-row .adv-booking {
            color: green;
            font-weight: bold;
            font-size: 18px;
        }

        .filter-row .terminal-info {
            display: flex;
            gap: 25px;
            font-size: 14px;
            font-weight: bold;
        }

        .filter-row span {
            font-weight: normal;
        }

        /* --- Main Table Structure (Maximum Spacing) --- */
        .booking-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .booking-table th,
        .booking-table td {
            /* MAXIMUM HORIZONTAL PADDING applied here */
            padding: 12px 20px;
            text-align: left;
            white-space: nowrap;
        }

        /* Table Headers */
        .booking-table thead th {
            font-weight: bold;
            border-bottom: 2px solid #000;
        }

        /* Column Widths adjusted for maximum separation */
        .booking-table th:nth-child(1) {
            width: 8%;
        }

        /* Time */
        .booking-table th:nth-child(2) {
            width: 5%;
        }

        /* Seat */
        .booking-table th:nth-child(3) {
            width: 14%;
        }

        /* Name */
        .booking-table th:nth-child(4) {
            width: 23%;
        }

        /* CNIC (Widest) */
        .booking-table th:nth-child(5) {
            width: 18%;
        }

        /* Cell */
        .booking-table th:nth-child(6) {
            width: 10%;
        }

        /* By */
        .booking-table th:nth-child(7) {
            width: 8%;
        }

        /* To */
        .booking-table th:nth-child(8) {
            width: 14%;
        }

        /* Fare */

        /* Alignment of specific header columns */
        .booking-table th:nth-child(4),
        /* CNIC */
        .booking-table th:nth-child(5),
        /* Cell */
        .booking-table th:last-child {
            /* Fare */
            text-align: right;
        }

        /* --- Date Separator (RIGHT ALIGNED) --- */
        .date-separator {
            text-align: right;
            font-weight: 900;
            color: maroon;
            padding: 10px 20px 10px 0;
            border-bottom: 1px solid #000;
        }
        
        /* --- Time Category Header (Past/Present/Future) --- */
        .time-category-header {
            text-align: left;
            font-weight: 900;
            color: #000;
            padding: 10px 0 10px 20px;
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            background-color: #f0f0f0;
        }

        /* --- Route Header (ALIGNED LEFT) --- */
        .route-header-cell {
            /* Padding-left ensures text starts far left */
            padding: 10px 0 10px 20px !important;
            /* Adjusted padding-left */
            font-weight: bold;
            border-top: 2px solid #000;
            border-bottom: 1px solid #000;
            text-align: left;
        }

        /* --- Data Rows --- */
        .booking-table tbody tr {
            border-bottom: 1px solid #000;
        }

        .booking-table tbody tr:last-child {
            border-bottom: none;
        }

        /* Data Alignment */
        .booking-table td:nth-child(4),
        /* CNIC */
        .booking-table td:nth-child(5),
        /* Cell */
        .booking-table td:last-child {
            /* Fare */
            text-align: right;
        }

        /* --- Route Footer Summary (Total Pax / Fare) --- */
        .route-footer {
            border-top: 1px solid #000;
            font-weight: bold;
            font-size: 14px;
        }

        .route-footer td {
            padding-top: 15px;
            padding-bottom: 15px;
        }

        .route-footer .total-label {
            text-align: right;
            padding-right: 10px;
        }

        .route-footer .total-pax-fare {
            font-weight: 900;
            text-align: right;
        }

        /* --- Next Route Header --- */
        .next-route-header {
            padding: 15px 0 10px 20px !important;
            /* Adjusted padding-left */
            font-weight: bold;
            border-top: 2px solid #000;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="page-num">Page 1</div>
    <div class="report-container">

        <div class="report-header">
            <div class="bs">{{ $company_initials }}</div>
            <div class="title">{{ $company_tagline }}</div>
        </div>

        <div class="filter-row">
            <div class="terminal-info">
                <span>Terminal <span style="font-size: 18px;">{{ $terminal->code }}</span></span>
                <span>From <span>{{ $start_date->format('Y-m-d') }}</span></span>
                <span>To <span>{{ $end_date->format('Y-m-d') }}</span></span>
            </div>
        </div>

        <table class="booking-table">
            <thead>
                <tr>
                    <th style="font-weight: 900;">Time</th>
                    <th>Seat</th>
                    <th>Name</th>
                    <th>CNIC</th>
                    <th>Cell</th>
                    <th>By</th>
                    <th>To</th>
                    <th>Fare</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $isFirstCategory = true;
                    $categoryLabels = [
                        'past' => 'Past Bookings',
                        'present' => 'Today\'s Bookings',
                        'future' => 'Future Bookings'
                    ];
                @endphp

                @foreach($grouped_seats as $timeCategory => $dateGroups)
                    @if(!$isFirstCategory)
                        <tr>
                            <td colspan="8" style="padding: 20px 0;"></td>
                        </tr>
                    @endif

                    <tr>
                        <td colspan="8" class="time-category-header">{{ $categoryLabels[$timeCategory] ?? ucfirst($timeCategory) }}</td>
                    </tr>

                    @php
                        $isFirstDate = true;
                    @endphp

                    @foreach($dateGroups as $date => $routeGroups)
                        @if(!$isFirstDate)
                            <tr>
                                <td colspan="8" style="padding: 10px 0;"></td>
                            </tr>
                        @endif

                        <tr>
                            <td colspan="8" class="date-separator">{{ Carbon\Carbon::parse($date)->format('d-m-Y') }}</td>
                        </tr>

                        @php
                            $isFirstRoute = true;
                        @endphp

                        @foreach($routeGroups as $routeTime => $seats)
                            @if(!$isFirstRoute)
                                <tr>
                                    <td colspan="8" class="next-route-header">{{ $routeTime }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="8" class="route-header-cell">{{ $routeTime }}</td>
                                </tr>
                            @endif

                            @php
                                $groupTotalPax = 0;
                                $groupTotalFare = 0;
                            @endphp

                            @foreach($seats as $seat)
                                @php
                                    $groupTotalPax++;
                                    $groupTotalFare += $seat['fare'];
                                @endphp
                                <tr>
                                    <td></td>
                                    <td>{{ $seat['seat_number'] }}</td>
                                    <td>{{ $seat['passenger_name'] }}@if($seat['is_advance']) <span style="color: green; font-weight: bold;">(Adv)</span>@endif</td>
                                    <td>{{ $seat['passenger_cnic'] }}</td>
                                    <td>{{ $seat['passenger_phone'] }}</td>
                                    <td>{{ $seat['booked_by'] }}</td>
                                    <td>{{ $seat['to_terminal_code'] }}</td>
                                    <td>{{ number_format($seat['fare'], 0) }}</td>
                                </tr>
                            @endforeach

                            <tr class="route-footer">
                                <td colspan="5" style="border-bottom: none;"></td>
                                <td colspan="2" class="total-label">Total</td>
                                <td class="total-pax-fare"><strong>{{ $groupTotalPax }} Pax {{ number_format($groupTotalFare, 0) }}</strong></td>
                            </tr>

                            @php
                                $isFirstRoute = false;
                            @endphp
                        @endforeach

                        @php
                            $isFirstDate = false;
                        @endphp
                    @endforeach

                    @php
                        $isFirstCategory = false;
                    @endphp
                @endforeach

            </tbody>
        </table>

    </div>
</body>

</html>
