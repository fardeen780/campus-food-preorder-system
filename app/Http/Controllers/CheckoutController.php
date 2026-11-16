<?php

namespace App\Http\Controllers;

use App\Exceptions\OrderException;
use App\Models\CartItem;
use App\Services\OrderService;
use App\Support\PickupSlots;
use Brick\Math\BigDecimal;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function show(Request $request): View|RedirectResponse
    {
        $items = $request->user()->cartItems()->with('foodItem.outlet')->get();

        if ($items->isEmpty()) {
            return redirect()->route('student.cart')->with('error', 'Your cart is empty.');
        }

        if ($items->contains(fn (CartItem $ci) => ! $ci->foodItem->isOrderable())) {
            return redirect()->route('student.cart')->with('error', 'Remove the unavailable items before checkout.');
        }

        $total = BigDecimal::zero();

        $groups = $items
            ->groupBy(fn (CartItem $ci) => $ci->foodItem->outlet_id)
            ->map(function ($rows) use (&$total) {
                $subtotal = BigDecimal::zero();

                foreach ($rows as $ci) {
                    $subtotal = $subtotal->plus($ci->line_total);
                }

                $total = $total->plus($subtotal);

                return [
                    'outlet' => $rows->first()->foodItem->outlet,
                    'items' => $rows,
                    'subtotal' => (string) $subtotal->toScale(2),
                ];
            })
            ->values();

        return view('student.checkout', [
            'groups' => $groups,
            'total' => (string) $total->toScale(2),
            'slots' => PickupSlots::grouped(),
        ]);
    }

    public function store(Request $request, OrderService $orders): RedirectResponse
    {
        $data = $request->validate([
            'pickup' => ['required', 'string', 'max:20'],
        ]);

        try {
            $placed = $orders->place($request->user(), $data['pickup']);
        } catch (OrderException $e) {
            return redirect()->route('student.cart')->with('error', $e->getMessage());
        }

        return redirect()->route('student.orders')
            ->with('status', 'Order placed: '.$placed->pluck('order_number')->implode(', ').'. Your food token is on each order.');
    }
}