<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Daily Booking and Expense Report</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            color: #000;
            line-height: 1.4;
        }

        .header {
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #000;
        }

        .header h1 {
            font-size: 16px;
            font-weight: 600;
            color: #000;
            margin-bottom: 5px;
        }

        .header .subtitle {
            font-size: 9px;
            color: #000;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 9px;
        }

        thead {
            background-color: #f8f9fa;
        }

        th {
            border: 1px solid #000;
            padding: 6px 4px;
            text-align: left;
            font-weight: 600;
            font-size: 8px;
        }

        td {
            border: 1px solid #000;
            padding: 5px 4px;
            vertical-align: top;
        }

        tbody tr {
            border-bottom: 1px solid #000;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .text-left {
            text-align: left;
        }

        .summary-table {
            margin-top: 20px;
        }

        .summary-table td {
            font-weight: 600;
        }

        .summary-table .total-row {
            background-color: #f8f9fa;
            font-weight: 700;
        }

        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #000;
            text-align: center;
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Daily Booking and Expense Report</h1>
        <div class="subtitle">{{ $terminal->name }} ({{ $terminal->code }}) | Period: {{ $start_date->format('d M Y') }} to {{ $end_date->format('d M Y') }}</div>
        <div class="subtitle">Generated on: {{ $generated_at }}</div>
    </div>

    <!-- Bookings Table -->
    <table>
        <thead>
            <tr>
                <th style="width: 8%;">Booking ID</th>
                <th style="width: 10%;">Date / Time</th>
                <th style="width: 25%;">Customer / Seat Details</th>
                <th style="width: 12%;">Route</th>
                <th style="width: 10%;" class="text-right">Amount (PKR)</th>
                <th style="width: 10%;" class="text-center">Payment Type</th>
                <th style="width: 8%;" class="text-center">Status</th>
                <th style="width: 12%;">Booked By</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($bookings as $index => $booking)
                @php
                    $fromTerminal = $booking->fromStop?->terminal?->code ?? 'N/A';
                    $toTerminal = $booking->toStop?->terminal?->code ?? 'N/A';
                    $route = $fromTerminal . ' → ' . $toTerminal;
                    $seats = $booking->seats->whereNull('cancelled_at');
                    $passengers = $booking->passengers;
                    
                    $customerSeats = [];
                    foreach ($passengers as $idx => $passenger) {
                        $seat = $seats->get($idx);
                        $seatNum = $seat ? $seat->seat_number : 'N/A';
                        $customerSeats[] = $passenger->name . ' (Seat: ' . $seatNum . ')';
                    }
                    
                    if (empty($customerSeats)) {
                        $seatNumbers = $seats->pluck('seat_number')->join(', ');
                        $customerSeats = [$seatNumbers ? 'Seats: ' . $seatNumbers : 'N/A'];
                    }

                    try {
                        $paymentMethod = \App\Enums\PaymentMethodEnum::from($booking->payment_method ?? '');
                        $paymentMethodLabel = $paymentMethod->getLabel();
                    } catch (\ValueError $e) {
                        $paymentMethodLabel = ucfirst($booking->payment_method ?? 'N/A');
                    }

                    try {
                        $status = \App\Enums\BookingStatusEnum::from($booking->status ?? '');
                        $statusLabel = $status->getLabel();
                    } catch (\ValueError $e) {
                        $statusLabel = ucfirst($booking->status ?? 'Unknown');
                    }

                    $employeeName = $booking->bookedByUser?->name ?? 'N/A';
                @endphp
                <tr>
                    <td>#{{ $booking->booking_number }}</td>
                    <td>{{ $booking->created_at->format('d M Y, H:i') }}</td>
                    <td>{{ implode(' | ', $customerSeats) }}</td>
                    <td>{{ $route }}</td>
                    <td class="text-right">{{ number_format($booking->final_amount, 0) }}</td>
                    <td class="text-center">{{ $paymentMethodLabel }}</td>
                    <td class="text-center">{{ $statusLabel }}</td>
                    <td>{{ $employeeName }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="text-center" style="padding: 15px;">No bookings found</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Summary Table -->
    <table class="summary-table">
        <tbody>
            <tr>
                <td style="width: 25%; font-weight: 600;">Employee / Terminal</td>
                <td style="width: 25%;">{{ $request->filled('user_id') ? ($bookings->first()?->bookedByUser?->name ?? 'Selected Employee') : ($terminal->name . ' (' . $terminal->code . ')') }}</td>
                <td style="width: 25%; font-weight: 600;">Total Sales (PKR)</td>
                <td style="width: 25%; text-align: right; font-weight: 600;">{{ number_format($stats['revenue']['total_revenue'], 0) }}</td>
            </tr>
            <tr>
                <td style="font-weight: 600;">Cash in Hand (PKR)</td>
                <td style="text-align: right; font-weight: 600;">{{ number_format($stats['cash']['cash_in_hand'], 0) }}</td>
                <td style="font-weight: 600;">Total Expenses (PKR)</td>
                <td style="text-align: right; font-weight: 600;">{{ number_format($stats['expenses']['total_expenses'], 0) }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="3" style="font-weight: 700;">Net Cash in Hand / Balance (PKR)</td>
                <td style="text-align: right; font-weight: 700;">{{ number_format($stats['cash']['net_balance'], 0) }}</td>
            </tr>
        </tbody>
    </table>

    <!-- Expenses Table (if any) -->
    @if($expenses->count() > 0)
    <table style="margin-top: 15px;">
        <thead>
            <tr>
                <th style="width: 10%;">Date</th>
                <th style="width: 15%;">Expense Type</th>
                <th style="width: 20%;">From → To Terminal</th>
                <th style="width: 10%;" class="text-right">Amount (PKR)</th>
                <th style="width: 30%;">Description</th>
                <th style="width: 15%;">Added By</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($expenses as $expense)
            <tr>
                <td>{{ $expense->expense_date?->format('d M Y') ?? 'N/A' }}</td>
                <td>{{ $expense->expense_type->getLabel() }}</td>
                <td>{{ $expense->fromTerminal?->name ?? 'N/A' }} → {{ $expense->toTerminal?->name ?? 'N/A' }}</td>
                <td class="text-right">{{ number_format($expense->amount, 0) }}</td>
                <td>{{ $expense->description ?? '-' }}</td>
                <td>{{ $expense->user?->name ?? 'N/A' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="total-row">
                <td colspan="3" class="text-right" style="font-weight: 700;">Total Expenses:</td>
                <td class="text-right" style="font-weight: 700;">{{ number_format($stats['expenses']['total_expenses'], 0) }}</td>
                <td colspan="2"></td>
            </tr>
        </tfoot>
    </table>
    @endif

    <div class="footer">
        <div><strong>Terminal: {{ $terminal->name }} ({{ $terminal->code }})</strong></div>
        <div>Period: {{ $start_date->format('d M Y') }} to {{ $end_date->format('d M Y') }} | Total Bookings: {{ $stats['bookings']['total'] }}</div>
        <div>Generated on: {{ $generated_at }}</div>
    </div>
</body>
</html>
