<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Brand;
use App\Models\V2\Category;
use App\Models\V2\Item;
use App\Models\V2\StockMovement;
use Illuminate\Http\Request;

class StockLedgerController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::with(['category', 'brand'])
            ->when($request->search, fn ($q, $search) => $q->where('description', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->brand_id, fn ($q, $id) => $q->where('brand_id', $id))
            ->orderBy('description')
            ->paginate(30)
            ->withQueryString();

        return view('v2.stock-ledger.index', [
            'items' => $items,
            'categories' => Category::orderBy('name')->get(),
            'brands' => Brand::orderBy('name')->get(),
        ]);
    }

    public function statement(Request $request, Item $item, string $report = 'statement')
    {
        $movements = StockMovement::with('account')
            ->where('item_id', $item->id)
            ->when($request->from_date, fn ($q, $date) => $q->whereDate('movement_date', '>=', $date))
            ->when($request->to_date, fn ($q, $date) => $q->whereDate('movement_date', '<=', $date))
            ->orderBy('movement_date')
            ->orderBy('id')
            ->get();

        return view('v2.stock-ledger.statement', compact('item', 'movements', 'report'));
    }
}
