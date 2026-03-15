<?php

namespace App\Exports;

use App\Models\PosOrder;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DailySalesExport implements FromArray, WithHeadings
{
    public function __construct(private string $date)
    {
    }

    public function array(): array
    {
        $orders = PosOrder::with('items')
            ->whereDate('created_at', $this->date)
            ->latest()
            ->get();

        $rows = [];

        foreach ($orders as $order) {
            $rows[] = [
                $order->order_number,
                $order->customer_name,
                strtoupper((string) $order->payment_method),
                (int) $order->items->sum('quantity'),
                number_format((float) $order->items->sum('discount_amount'), 2, '.', ''),
                number_format((float) $order->total, 2, '.', ''),
                $order->created_at->format('Y-m-d H:i:s'),
            ];
        }

        $rows[] = ['', '', '', '', '', '', ''];
        $rows[] = ['SUMMARY', '', '', '', '', '', ''];
        $rows[] = ['Total Orders', $orders->count(), '', '', '', '', ''];
        $rows[] = ['Total Items Sold', $orders->sum(fn ($order) => $order->items->sum('quantity')), '', '', '', '', ''];
        $rows[] = ['Total Discount (PKR)', number_format((float) $orders->sum(fn ($order) => $order->items->sum('discount_amount')), 2, '.', ''), '', '', '', '', ''];
        $rows[] = ['Total Earning (PKR)', number_format((float) $orders->sum('total'), 2, '.', ''), '', '', '', '', ''];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Customer',
            'Payment Method',
            'Items Count',
            'Order Discount (PKR)',
            'Order Total (PKR)',
            'Order Date',
        ];
    }
}
