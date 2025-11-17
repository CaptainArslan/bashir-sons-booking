<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Passenger Information Report</title>

    <style>
        /* PRINT SETTINGS */
        @media print {
            @page {
                size: A4 landscape;
                margin: 0.7cm;
            }
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: "Segoe UI", Arial, sans-serif;
            background: #fff;
            color: #000;
            padding: 10px;
        }

        /* REPORT TITLE */
        .report-title {
            text-align: center;
            font-size: 20px;
            font-weight: 800;
            padding-bottom: 6px;
            border-bottom: 2px solid #000;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        /* HEADER META INFORMATION */
        .report-meta {
            display: flex;
            justify-content: space-between;
            font-size: 11px;
            padding: 6px 0;
            border-bottom: 1px solid #000;
            margin-bottom: 10px;
        }

        .report-meta div {
            width: 33%;
        }
        .meta-center {
            text-align: center;
            font-weight: 700;
        }
        .meta-right {
            text-align: right;
        }

        .meta-label {
            font-weight: 700;
        }

        /* FILTER BOX */
        .filters-box {
            background: #f1f1f1;
            border: 1px solid #333;
            padding: 8px;
            font-size: 10px;
            margin-bottom: 12px;
        }
        .filters-title {
            font-weight: 700;
            margin-bottom: 4px;
            text-transform: uppercase;
        }
        .filters-list span {
            margin-right: 10px;
        }

        /* TABLE STYLING */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        th {
            background: #003f7f;
            color: #fff;
            font-weight: 700;
            text-align: center;
            padding: 6px 4px;
            font-size: 9px;
            border: 1px solid #000;
        }

        td {
            border: 0.5px solid #555;
            padding: 5px 4px;
            font-size: 9px;
        }

        tbody tr:nth-child(even) {
            background: #f7faff;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left  { text-align: left; }

        /* TOTAL FOOTER */
        tfoot td {
            font-weight: 700;
            background: #e0e0e0;
            border: 1px solid #000;
            padding: 6px;
        }

        /* SUMMARY BAR */
        .summary-bar {
            margin-top: 15px;
            padding: 10px;
            background: #003f7f;
            color: #fff;
            font-size: 12px;
            text-align: center;
            font-weight: 700;
            border-radius: 4px;
        }

    </style>
</head>

<body>

    <!-- TITLE -->
    <div class="report-title">
        {{ $company_name ?? 'Bashir Sons Travel' }}
    </div>

    <!-- META HEADER -->
    <div class="report-meta">
        <div class="meta-left">
            <div><span class="meta-label">Report Name: </span>Passenger Information Report (Marketing)</div>
        </div>

        <div class="meta-center">
            From {{ $start_date->format('Y-m-d') }}
            @if(isset($filters['start_time'])) {{ $filters['start_time'] }} @endif
            <br>
            To {{ $end_date->format('Y-m-d') }}
            @if(isset($filters['end_time'])) {{ $filters['end_time'] }} @endif
        </div>

        <div class="meta-right">
            <div><span class="meta-label">Generated At: </span>{{ $generated_at }}</div>
        </div>
    </div>

    <!-- FILTERS -->
    @if(isset($filters) && count(array_filter($filters, fn($v) => $v !== null)))
    <div class="filters-box">
        <div class="filters-title">Applied Filters:</div>
        <div class="filters-list">
            @if($filters['from_terminal_name'] ?? false) <span>From Terminal: <strong>{{ $filters['from_terminal_name'] }}</strong></span>@endif
            @if($filters['to_terminal_name'] ?? false)   <span>To Terminal: <strong>{{ $filters['to_terminal_name'] }}</strong></span>@endif
            @if($filters['status'] ?? false)             <span>Status: <strong>{{ ucfirst($filters['status']) }}</strong></span>@endif
            @if($filters['payment_status'] ?? false)     <span>Payment: <strong>{{ ucfirst($filters['payment_status']) }}</strong></span>@endif
            @if($filters['channel'] ?? false)            <span>Channel: <strong>{{ ucfirst($filters['channel']) }}</strong></span>@endif
            @if(isset($filters['is_advance']) && $filters['is_advance'] !== '') 
                <span>Type: <strong>{{ $filters['is_advance'] == '1' ? 'Advance' : 'Regular' }}</strong></span>
            @endif
            @if($filters['employee_name'] ?? false)      <span>Employee: <strong>{{ $filters['employee_name'] }}</strong></span>@endif
            @if($filters['customer_name'] ?? false)      <span>Customer: <strong>{{ $filters['customer_name'] }}</strong></span>@endif
            @if($filters['booking_number'] ?? false)     <span>Booking #: <strong>{{ $filters['booking_number'] }}</strong></span>@endif
        </div>
    </div>
    @endif


    <!-- TABLE -->
    <table>
        <thead>
            <tr>
                <th>Booking #</th>
                <th>Name</th>
                <th>CNIC</th>
                <th>Phone</th>
                <th>Email</th>
                <th>From Terminal</th>
                <th>To Terminal</th>
                <th>Booking Date</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Channel</th>
            </tr>
        </thead>

        <tbody>
            @php $totalAmount = 0; @endphp

            @foreach($passengers as $p)
                @php $totalAmount += $p['amount']; @endphp
                <tr>
                    <td class="text-center">{{ $p['booking_number'] }}</td>
                    <td>{{ $p['name'] }}</td>
                    <td>{{ $p['cnic'] }}</td>
                    <td>{{ $p['phone'] }}</td>
                    <td>{{ $p['email'] }}</td>
                    <td class="text-center">{{ $p['from_terminal'] }}</td>
                    <td class="text-center">{{ $p['to_terminal'] }}</td>
                    <td class="text-center">{{ $p['booking_date'] }}</td>
                    <td class="text-right">{{ number_format($p['amount'], 0) }}</td>
                    <td class="text-center">{{ ucfirst($p['status']) }}</td>
                    <td class="text-center">{{ ucfirst($p['channel']) }}</td>
                </tr>
            @endforeach
        </tbody>

        <tfoot>
            <tr>
                <td colspan="8" class="text-right">Total Passengers: {{ count($passengers) }}</td>
                <td class="text-right">{{ number_format($totalAmount, 0) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>

    <!-- SUMMARY BAR -->
    <div class="summary-bar">
        Total Passengers: {{ count($passengers) }} |
        Total Revenue: {{ number_format($totalAmount, 0) }} PKR |
        Generated On: {{ $generated_at }}
    </div>

</body>
</html>
