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
    <h2>Daily Sales Report - {{ $reportDate }}</h2>

    <div class="summary">
        <p><strong>Total Orders:</strong> {{ $summary['total_orders'] }}</p>
        <p><strong>Total Items Sold:</strong> {{ $summary['total_items_sold'] }}</p>
        <p><strong>Total Discount:</strong> PKR {{ number_format($summary['total_discount'], 2) }}</p>
        <p><strong>Total Sales Amount:</strong> PKR {{ number_format($summary['total_sales_amount'], 2) }}</p>
        <p><strong>Total Earning (Received):</strong> PKR {{ number_format($summary['total_earning'], 2) }}</p>
        <p><strong>Total Due:</strong> PKR {{ number_format($summary['total_due'], 2) }}</p>
        <p><strong>Total Cost:</strong> PKR {{ number_format($summary['total_cost'], 2) }}</p>
        <p><strong>Total Profit:</strong> PKR {{ number_format($summary['total_profit'], 2) }}</p>
        <p><strong>Total Loss:</strong> PKR {{ number_format($summary['total_loss'], 2) }}</p>
        <p><strong>Net Profit/Loss:</strong> PKR {{ number_format($summary['net_profit_loss'], 2) }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Item Name</th>
                <th class="center">Qty Sold</th>
                <th class="right">Sales Amount</th>
                <th class="right">Earning</th>
                <th class="right">Due</th>
                <th class="right">Discount</th>
                <th class="right">Cost</th>
                <th class="right">Profit/Loss</th>
            </tr>
        </thead>
        <tbody>
            @forelse($itemWiseSales as $item)
                <tr>
                    <td>{{ $item['product_name'] }}</td>
                    <td class="center">{{ $item['quantity_sold'] }}</td>
                    <td class="right">PKR {{ number_format($item['sales_amount'], 2) }}</td>
                    <td class="right">PKR {{ number_format($item['earning_amount'], 2) }}</td>
                    <td class="right">PKR {{ number_format($item['due_amount'], 2) }}</td>
                    <td class="right">PKR {{ number_format($item['discount_amount'], 2) }}</td>
                    <td class="right">PKR {{ number_format($item['cost_amount'], 2) }}</td>
                    <td class="right">PKR {{ number_format($item['profit_or_loss'], 2) }}</td>
                </tr>
            @empty
                <tr><td colspan="8">No sales found for this date.</td></tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr>
                <td class="right">TOTALS</td>
                <td class="center">{{ $summary['total_items_sold'] }}</td>
                <td class="right">PKR {{ number_format($summary['total_sales_amount'], 2) }}</td>
                <td class="right">PKR {{ number_format($summary['total_earning'], 2) }}</td>
                <td class="right">PKR {{ number_format($summary['total_due'], 2) }}</td>
                <td class="right">PKR {{ number_format($summary['total_discount'], 2) }}</td>
                <td class="right">PKR {{ number_format($summary['total_cost'], 2) }}</td>
                <td class="right">PKR {{ number_format($summary['net_profit_loss'], 2) }}</td>
            </tr>
        </tfoot>
    </table>

    <table>
        <thead>
            <tr>
                <th>Order #</th>
                <th>Customer</th>
                <th>Payment</th>
                <th class="right">Sales</th>
                <th class="center">Qty</th>
                <th class="right">Paid</th>
                <th class="right">Due</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyOrders as $order)
                <tr>
                    <td>{{ $order->order_number }}</td>
                    <td>{{ $order->customer?->full_name ?: $order->customer_name }}</td>
                    <td class="center">{{ strtoupper($order->payment_method) }}</td>
                    <td class="right">PKR {{ number_format($order->total, 2) }}</td>
                    <td class="center">{{ $order->items->sum('quantity') }}</td>
                    <td class="right">PKR {{ number_format($order->paid_amount, 2) }}</td>
                    <td class="right">PKR {{ number_format($order->due_amount, 2) }}</td>
                    <td>{{ $order->invoice_date?->format('Y-m-d') }}</td>
                </tr>
            @empty
                <tr><td colspan="8">No orders found for this date.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
