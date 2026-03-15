<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Daily Sales Report</title>
    <style>
        body    { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        h2      { margin-bottom: 6px; }
        .summary { margin: 10px 0; }
        .summary p { margin: 3px 0; }
        table   { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td  { border: 1px solid #aaa; padding: 5px 6px; }
        th      { background: #e8e8e8; text-align: center; }
        .right  { text-align: right; }
        .center { text-align: center; }
        tfoot td { background: #333; color: #fff; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Daily Sales Report &mdash; {{ $reportDate }}</h2>

    <div class="summary">
        <p><strong>Total Orders:</strong> {{ $summary['total_orders'] }}</p>
        <p><strong>Total Items Sold:</strong> {{ $summary['total_items_sold'] }}</p>
        <p><strong>Total Discount:</strong> PKR {{ number_format($summary['total_discount'], 2) }}</p>
        <p><strong>Total Sales:</strong> PKR {{ number_format($summary['total_earning'], 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Payment</th>
                <th>Item Name</th>
                <th class="right">Unit Price</th>
                <th class="center">Qty</th>
                <th class="right">Discount</th>
                <th class="right">Earning</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyOrders as $order)
                @foreach($order->items as $item)
                <tr>
                    <td>{{ $loop->first ? $order->order_number : '' }}</td>
                    <td>{{ $loop->first ? ($order->customer?->full_name ?: $order->customer_name) : '' }}</td>
                    <td class="center">{{ $loop->first ? strtoupper($order->payment_method) : '' }}</td>
                    <td>{{ $item->product_name }}</td>
                    <td class="right">PKR {{ number_format($item->unit_price, 2) }}</td>
                    <td class="center">{{ $item->quantity }}</td>
                    <td class="right">PKR {{ number_format($item->discount_amount, 2) }}</td>
                    <td class="right">PKR {{ number_format($item->line_total, 2) }}</td>
                    <td>{{ $loop->first ? $order->created_at->format('Y-m-d H:i') : '' }}</td>
                </tr>
                @endforeach
            @empty
                <tr><td colspan="9">No sales found for this date.</td></tr>
            @endforelse
        </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" class="right">TOTALS</td>
                    <td></td>
                    <td class="center">{{ $summary['total_items_sold'] }}</td>
                    <td class="right">PKR {{ number_format($summary['total_discount'], 2) }}</td>
                    <td class="right">PKR {{ number_format($summary['total_earning'], 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
    </table>
</body>
</html>
