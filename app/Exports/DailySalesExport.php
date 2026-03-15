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
        $orders = PosOrder::with(['items', 'customer'])
            ->whereDate('created_at', $this->date)
            ->latest()
            ->get();

        $rows = [];
        $grandQty      = 0;
        $grandDiscount = 0.0;
        $grandEarning  = 0.0;

        foreach ($orders as $order) {
            $firstItem = true;
            foreach ($order->items as $item) {
                $rows[] = [
                    $firstItem ? $order->order_number : '',
                    $firstItem ? ($order->customer?->full_name ?: $order->customer_name) : '',
                    $firstItem ? strtoupper((string) $order->payment_method) : '',
                    $item->product_name,
                    number_format((float) $item->unit_price, 2, '.', ''),
                    (int) $item->quantity,
                    number_format((float) $item->discount_amount, 2, '.', ''),
                    number_format((float) $item->line_total, 2, '.', ''),
                    $firstItem ? $order->created_at->format('Y-m-d H:i:s') : '',
                ];
                $grandQty      += $item->quantity;
                $grandDiscount += $item->discount_amount;
                $grandEarning  += $item->line_total;
                $firstItem = false;
            }
        }

        $rows[] = ['', '', '', '', '', '', '', '', ''];
        $rows[] = ['SUMMARY', '', '', '', '', '', '', '', ''];
        $rows[] = ['Total Orders',       $orders->count(),                                      '', '', '', '', '', '', ''];
        $rows[] = ['Total Items Sold',   $grandQty,                                             '', '', '', '', '', '', ''];
        $rows[] = ['Total Discount (PKR)', number_format($grandDiscount, 2, '.', ''),           '', '', '', '', '', '', ''];
        $rows[] = ['Total Sales (PKR)',  number_format($grandEarning,  2, '.', ''),             '', '', '', '', '', '', ''];

        return $rows;
    }

    public function headings(): array
    {
        return [
            'Order Number',
            'Customer',
            'Payment Method',
            'Item Name',
            'Unit Price (PKR)',
            'Qty Sold',
            'Discount (PKR)',
            'Earning (PKR)',
            'Date',
        ];
    }
}
