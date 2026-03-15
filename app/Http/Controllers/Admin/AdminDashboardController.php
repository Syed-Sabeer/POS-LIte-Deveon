<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PosOrder;
use App\Models\PosOrderItem;
use App\Models\Product;


class AdminDashboardController extends Controller
{
    public function index()
    {
        $todaySales = (float) PosOrder::whereDate('created_at', today())->sum('total');
        $monthSales = (float) PosOrder::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('total');

        $totalOrdersToday = PosOrder::whereDate('created_at', today())->count();
        $totalOrders = PosOrder::count();
        $totalItems = Product::count();
        $totalStock = (int) Product::sum('quantity');

        $itemWiseSell = PosOrderItem::selectRaw('product_name, SUM(quantity) as sold_qty, SUM(line_total) as sold_value')
            ->groupBy('product_name')
            ->orderByDesc('sold_qty')
            ->limit(10)
            ->get();

        $currentStock = Product::orderBy('quantity')->limit(10)->get();

        $summary = [
            'today_sales' => $todaySales,
            'month_sales' => $monthSales,
            'total_orders_today' => $totalOrdersToday,
            'total_orders' => $totalOrders,
            'total_items' => $totalItems,
            'total_stock' => $totalStock,
        ];

        return view('admin.dashboard', compact('summary', 'itemWiseSell', 'currentStock'));
    }
}
