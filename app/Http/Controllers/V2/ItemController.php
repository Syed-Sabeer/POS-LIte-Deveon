<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Brand;
use App\Models\V2\Category;
use App\Models\V2\Item;
use App\Services\V2\NumberService;
use Illuminate\Http\Request;

class ItemController extends Controller
{
    public function __construct(private readonly NumberService $numbers)
    {
    }

    public function index(Request $request)
    {
        $items = Item::with(['category', 'brand'])
            ->when($request->search, fn ($q, $search) => $q->where('description', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
            ->when($request->category_id, fn ($q, $id) => $q->where('category_id', $id))
            ->when($request->brand_id, fn ($q, $id) => $q->where('brand_id', $id))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('v2.items.index', [
            'items' => $items,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['code'] = $this->numbers->itemCode(Item::class);
        $data['created_by'] = $request->user()?->id;
        $data['is_active'] = $request->boolean('is_active', true);

        $item = Item::create($data);

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Item saved.',
                'item' => $item,
            ]);
        }

        return back()->with('success', 'Item saved.');
    }

    public function edit(Item $item)
    {
        return view('v2.items.edit', [
            'item' => $item,
            'categories' => Category::where('is_active', true)->orderBy('name')->get(),
            'brands' => Brand::where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $item->update($this->validated($request) + [
            'updated_by' => $request->user()?->id,
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('v2.items.index')->with('success', 'Item updated.');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return back()->with('success', 'Item deleted.');
    }

    private function validated(Request $request): array
    {
        $unknownCategory = Category::firstOrCreate(['name' => 'Unknown'], ['is_active' => true]);
        $unknownBrand = Brand::firstOrCreate(['name' => 'Unknown'], ['is_active' => true]);

        return $request->validate([
            'category_id' => ['nullable', 'exists:v2_categories,id'],
            'brand_id' => ['nullable', 'exists:v2_brands,id'],
            'nick' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:255'],
            'bf_qty' => ['nullable', 'numeric'],
            'minimum_qty' => ['nullable', 'numeric'],
            'maximum_qty' => ['nullable', 'numeric'],
            'packing' => ['nullable', 'string', 'max:100'],
            'packet_qty' => ['nullable', 'numeric'],
            'opening_cost' => ['nullable', 'numeric', 'min:0'],
            'cost' => ['nullable', 'numeric', 'min:0'],
            'retail_rate' => ['nullable', 'numeric', 'min:0'],
        ]) + [
            'category_id' => $unknownCategory->id,
            'brand_id' => $unknownBrand->id,
            'bf_qty' => 0,
            'minimum_qty' => 0,
            'maximum_qty' => 0,
            'packet_qty' => 0,
            'opening_cost' => 0,
            'cost' => 0,
            'retail_rate' => 0,
        ];
    }
}
