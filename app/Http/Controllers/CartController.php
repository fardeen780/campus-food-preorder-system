<?php

namespace App\Http\Controllers;

use App\Models\CartItem;
use App\Models\FoodItem;
use Brick\Math\BigDecimal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request): View
    {
        $items = $request->user()->cartItems()->with('foodItem.outlet')->get();

        $groups = $items
            ->groupBy(fn (CartItem $ci) => $ci->foodItem->outlet_id)
            ->map(function ($rows) {
                $subtotal = BigDecimal::zero();
                foreach ($rows as $ci) {
                    $subtotal = $subtotal->plus($ci->line_total);
                }

                return [
                    'outlet' => $rows->first()->foodItem->outlet,
                    'items' => $rows,
                    'subtotal' => (string) $subtotal->toScale(2),
                ];
            })
            ->values();

        $total = BigDecimal::zero();
        foreach ($groups as $group) {
            $total = $total->plus($group['subtotal']);
        }

        return view('student.cart', [
            'groups' => $groups,
            'total' => (string) $total->toScale(2),
            'blocked' => $items->contains(fn (CartItem $ci) => ! $ci->foodItem->isOrderable()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'food_item_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'between:1,20'],
        ]);

        $item = FoodItem::available()
            ->whereHas('outlet', fn ($q) => $q->where('is_active', true))
            ->whereHas('category', fn ($q) => $q->where('is_active', true))
            ->find($data['food_item_id']);

        if (! $item) {
            return back()->with('error', 'That item is not available right now.');
        }

        $existing = $request->user()->cartItems()->where('food_item_id', $item->id)->first();
        $newQuantity = ($existing?->quantity ?? 0) + $data['quantity'];

        if ($newQuantity > 20) {
            return back()->with('error', 'You can order at most 20 of one item.');
        }

        if ($existing) {
            $existing->update(['quantity' => $newQuantity]);
        } else {
            $request->user()->cartItems()->create([
                'food_item_id' => $item->id,
                'quantity' => $newQuantity,
            ]);
        }

        return back()->with('status', $item->name.' added to your cart.');
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'between:1,20'],
        ]);

        $request->user()->cartItems()->findOrFail($id)->update($data);

        return back()->with('status', 'Cart updated.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $request->user()->cartItems()->findOrFail($id)->delete();

        return back()->with('status', 'Item removed from your cart.');
    }
}