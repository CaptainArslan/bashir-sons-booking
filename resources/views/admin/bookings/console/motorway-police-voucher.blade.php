<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Motorway Police Voucher</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            font-weight: bold;
            font-size: 24px;
        }

        .sub-header {
            text-align: center;
            font-size: 25px;
            font-weight: 700;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .info-lines div {
            font-size: 14px;
            margin-bottom: 6px;
        }

        .line-between {
            display: flex;
            justify-content: space-between;
        }

        .line-multi {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .text-align {
            text-align: right;
        }

        .data-table th,
        .data-table td {
            border-bottom: 1px solid #000;
            padding: 6px;
            text-align: left;
        }

        .data-table th {
            font-weight: bold;
        }

        /* Right side summary (Online/Balance) */
        .right-summary {
            width: 200px;
            font-weight: bold;
        }

        .summary-line {
            display: flex;
            justify-content: space-between;
            padding: 2px 0;
        }

        .summary-line.online {
            border-top: 1px dashed #000;
        }

        .summary-line.balance {
            border-top: 2px solid #000;
            font-size: 14px;
        }

        .date-time {
            font-size: 9px;
            font-style: italic;
            text-align: right;
            margin-top: 5px;
        }

        /* --- GOJ/LHR/36 Box Positioning --- */

        .lhr-box {
            border: 1px solid #000;
            width: 50px;
            height: 20px;
            text-align: center;
            font-weight: bold;
            line-height: 20px;
            margin-left: 25px;
        }

        .goj-container {
            display: flex;
            margin-top: -1px;
        }

        .goj-box,
        .goj-value {
            border: 1px solid #000;
            width: 25px;
            height: 20px;
            text-align: center;
            font-weight: bold;
            line-height: 20px;
        }

        .goj-box {
            border-right: none;
        }

        .goj-value {
            width: 50px;
        }

        .route-summary {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 10px;
        }

        /* --- Footer Breakdown Table --- */
        .footer-table-container {
            border: 1px solid #000;
            padding: 5px;
            margin-top: 30px;
            width: 500px;
            align-self: flex-start;
        }

        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        .footer-table th,
        .footer-table td {
            padding: 4px 6px;
            text-align: right;
            border: 1px solid #000;
            font-weight: bold;
        }

        .footer-table thead th {
            font-weight: bold;
            text-align: center;
        }

        .dest-cell {
            text-align: left !important;
            width: 60px;
        }

        .total-col-header,
        .total-col-data {
            width: 15%;
        }

        .final-total td {
            border-bottom: 2px solid #000;
        }

        @media print {
            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>
    <div>
        <div class="header">{{ $companyInitials }}</div>
        <div class="sub-header">{{ $companyTagline }}</div>

        <div class="info-lines">
            <div class="line-between">
                <span><strong>Route:</strong> {{ $routeCode }} <span>{{ $departureTime }}</span></span>
                <span><strong>Date:</strong> {{ $departureDate }}</span>
            </div>

            <div class="line-multi">
                <span><strong>Vehicle No:</strong> {{ $vehicleNo }}</span>
                <span><strong>Arrival Time:</strong> {{ $arrivalTime }}</span>
                <span><strong>Departure Time:</strong> {{ $departureTime }}</span>
                <span><strong>Voucher No:</strong> {{ $voucherNo }}</span>
            </div>

            <div class="line-between">
                <span><strong>Driver:</strong> {{ $driverName }}</span>
                <span><strong>Host:</strong> {{ $hostName }}</span>
            </div>
        </div>

        <br>

        <table class="data-table">
            <tr>
                <th>Seat</th>
                <th>Name</th>
                <th>CNIC</th>
                <th>Cell</th>
                <th>Via</th>
                <th>By</th>
                <th>From</th>
                <th>Desti.</th>
            </tr>

            @foreach ($passengers as $passenger)
                <tr>
                    <td>{{ $passenger['seat_number'] }}</td>
                    <td>{{ strtoupper($passenger['name']) }}</td>
                    <td>{{ $passenger['cnic'] }}</td>
                    <td>{{ $passenger['phone'] }}</td>
                    <td>{{ $passenger['via'] }}</td>
                    <td>{{ strtoupper($passenger['agent_name']) }}</td>
                    <td>{{ $passenger['from_code'] }}</td>
                    <td>{{ $passenger['to_code'] }}</td>
                </tr>
            @endforeach
        </table>

        <br><br>

        <div
            style="font-size:14px; font-weight:bold; border-top:2px solid #000; padding-top:10px; border-bottom: 1px solid #000;">
            <table style="width:100%; font-size:14px; font-weight:bold;">
                <tr>
                    <td class="text-align">Printed By:</td>
                    <td>{{ strtoupper($currentUserName) }}</td>
                    <td class="text-align">Total Pax:</td>
                    <td>{{ $totalPassengers }}</td>
                </tr>
            </table>
            <br>
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
