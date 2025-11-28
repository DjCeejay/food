<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\PriceHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class MenuItemController extends Controller
{
    public function index(Request $request)
    {
        $query = MenuItem::with('category')
            ->orderBy('sort_order')
            ->orderBy('name');

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->boolean('active_only', false)) {
            $query->where('is_active', true);
        }

        return $query->get();
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'is_sold_out' => 'boolean',
            'stock' => 'nullable|integer|min:0',
            'image_url' => 'nullable|url',
            'image' => 'nullable|image|max:4096',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        if ($request->hasFile('image')) {
            $data['image_url'] = $this->storeImage($request);
        }

        $data['slug'] = Str::slug($data['name'] . '-' . Str::random(6));

        $item = MenuItem::create($data);

        PriceHistory::create([
            'menu_item_id' => $item->id,
            'price' => $item->price,
            'changed_by' => $request->user()->email ?? 'system',
        ]);

        return response()->json($item->load('category'), 201);
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $data = $request->validate([
            'category_id' => 'nullable|exists:categories,id',
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'is_sold_out' => 'boolean',
            'stock' => 'nullable|integer|min:0',
            'image_url' => 'nullable|url',
            'image' => 'nullable|image|max:4096',
            'is_active' => 'boolean',
            'sort_order' => 'integer|min:0',
        ]);

        $priceChanged = array_key_exists('price', $data) && $data['price'] !== null && $data['price'] != $menuItem->price;

        if ($request->hasFile('image')) {
            $data['image_url'] = $this->storeImage($request);
        }

        $menuItem->update($data);

        if ($priceChanged) {
            PriceHistory::create([
                'menu_item_id' => $menuItem->id,
                'price' => $menuItem->price,
                'changed_by' => $request->user()->email ?? 'system',
            ]);
        }

        return response()->json($menuItem->load('category'));
    }

    public function toggleSoldOut(MenuItem $menuItem)
    {
        $menuItem->update(['is_sold_out' => ! $menuItem->is_sold_out]);

        return response()->json($menuItem);
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return response()->noContent();
    }

    private function storeImage(Request $request): string
    {
        $path = $request->file('image')->store('menu', 'public');

        return Storage::disk('public')->url($path);
    }
}
