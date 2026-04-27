<?php

namespace App\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use App\Models\V2\Brand;
use App\Models\V2\Category;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    public function categories()
    {
        return view('v2.masters.index', [
            'title' => 'Category Manager',
            'items' => Category::latest('id')->paginate(20),
            'storeRoute' => route('v2.categories.store'),
            'permissionEdit' => 'v2 edit',
            'permissionDelete' => 'v2 delete',
        ]);
    }

    public function storeCategory(Request $request)
    {
        Category::create($this->validated($request) + ['created_by' => $request->user()?->id]);

        return back()->with('success', 'Category saved.');
    }

    public function updateCategory(Request $request, Category $category)
    {
        $category->update($this->validated($request) + ['updated_by' => $request->user()?->id]);

        return back()->with('success', 'Category updated.');
    }

    public function destroyCategory(Category $category)
    {
        $category->delete();

        return back()->with('success', 'Category deleted.');
    }

    public function brands()
    {
        return view('v2.masters.index', [
            'title' => 'Brand Manager',
            'items' => Brand::latest('id')->paginate(20),
            'storeRoute' => route('v2.brands.store'),
            'permissionEdit' => 'v2 edit',
            'permissionDelete' => 'v2 delete',
        ]);
    }

    public function storeBrand(Request $request)
    {
        Brand::create($this->validated($request) + ['created_by' => $request->user()?->id]);

        return back()->with('success', 'Brand saved.');
    }

    public function updateBrand(Request $request, Brand $brand)
    {
        $brand->update($this->validated($request) + ['updated_by' => $request->user()?->id]);

        return back()->with('success', 'Brand updated.');
    }

    public function destroyBrand(Brand $brand)
    {
        $brand->delete();

        return back()->with('success', 'Brand deleted.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]) + ['is_active' => true];
    }
}
