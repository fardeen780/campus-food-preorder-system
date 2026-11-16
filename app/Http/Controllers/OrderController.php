<?php

namespace App\Http\Controllers;

use App\Exceptions\OrderException;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        return view('student.orders', [
            'orders' => $request->user()->placedOrders()
                ->with(['outlet', 'token'])
                ->latest('id')
                ->paginate(10),
        ]);
    }

    public function show(Request $request, OrderService $service, int $id): View
    {
        $order = $request->user()->placedOrders()
            ->with(['outlet', 'items', 'token.currentOwner'])
            ->findOrFail($id);

        return view('student.order-show', [
            'order' => $order,
            'blockReason' => $service->cancellationBlockReason($order, $request->user()),
        ]);
    }

    public function cancel(Request $request, OrderService $service, int $id): RedirectResponse
    {
        $order = $request->user()->placedOrders()->findOrFail($id);

        try {
            $service->cancel($request->user(), $order);
        } catch (OrderException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('student.orders.show', $order->id)
            ->with('status', 'Order '.$order->order_number.' has been cancelled.');
    }
}