<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MenuItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['items', 'payments'])->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        return $query->paginate(25);
    }

    public function show(Order $order)
    {
        return $order->load(['items', 'payments']);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'channel' => 'nullable|string|max:50',
            'customer_name' => 'nullable|string|max:255',
            'customer_phone' => 'nullable|string|max:32',
            'items' => 'required|array|min:1',
            'items.*.menu_item_id' => 'required|exists:menu_items,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'nullable|numeric|min:0',
            'payment' => 'nullable|array',
            'payment.amount' => 'required_with:payment|numeric|min:0',
            'payment.method' => 'required_with:payment|string|max:50',
            'payment.reference' => 'nullable|string|max:255',
            'discount' => 'nullable|numeric|min:0',
            'tax' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $itemsData = [];
            $subtotal = 0;

            foreach ($data['items'] as $itemInput) {
                $menuItem = MenuItem::findOrFail($itemInput['menu_item_id']);
                $price = $itemInput['price'] ?? $menuItem->price;
                $lineTotal = $price * $itemInput['quantity'];

                if ($menuItem->is_sold_out) {
                    throw ValidationException::withMessages([
                        'items' => ["{$menuItem->name} is sold out."],
                    ]);
                }

                $itemsData[] = [
                    'menu_item_id' => $menuItem->id,
                    'name' => $menuItem->name,
                    'quantity' => $itemInput['quantity'],
                    'unit_price' => $price,
                    'total' => $lineTotal,
                ];

                $subtotal += $lineTotal;
            }

            $discount = $data['discount'] ?? 0;
            $tax = $data['tax'] ?? 0;
            $total = max(0, $subtotal + $tax - $discount);

            $order = Order::create([
                'channel' => $data['channel'] ?? 'pos',
                'customer_name' => $data['customer_name'] ?? null,
                'customer_phone' => $data['customer_phone'] ?? null,
                'subtotal' => $subtotal,
                'tax' => $tax,
                'discount' => $discount,
                'total' => $total,
                'status' => $data['payment'] ? 'paid' : 'pending',
                'paid_at' => $data['payment'] ? now() : null,
            ]);

            foreach ($itemsData as $item) {
                $order->items()->create($item);
            }

            if (!empty($data['payment'])) {
                $order->payments()->create([
                    'amount' => $data['payment']['amount'],
                    'method' => $data['payment']['method'],
                    'reference' => $data['payment']['reference'] ?? null,
                    'paid_at' => now(),
                ]);
            }

            return $order->load(['items', 'payments']);
        });
    }
}
