<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Daily Sales Report</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
        .title { margin-bottom: 10px; }
        .summary { margin: 12px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        th { background: #f3f3f3; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h2 class="title">Daily Sales Report ({{ $reportDate }})</h2>

    <div class="summary">
        <p><strong>Total Orders:</strong> {{ $summary['total_orders'] }}</p>
        <p><strong>Total Items Sold:</strong> {{ $summary['total_items_sold'] }}</p>
        <p><strong>Total Discount:</strong> PKR {{ number_format($summary['total_discount'], 2) }}</p>
        <p><strong>Total Earning:</strong> PKR {{ number_format($summary['total_earning'], 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Payment</th>
                <th class="right">Items</th>
                <th class="right">Discount</th>
                <th class="right">Total</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyOrders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer?->full_name ?: $order->customer_name }}</td>
                    <td>{{ strtoupper($order->payment_method) }}</td>
                    <td class="right">{{ $order->items->sum('quantity') }}</td>
                    <td class="right">PKR {{ number_format($order->items->sum('discount_amount'), 2) }}</td>
                    <td class="right">PKR {{ number_format($order->total, 2) }}</td>
                    <td>{{ $order->created_at->format('Y-m-d H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">No sales found for this date.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
