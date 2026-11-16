<?php

namespace App\Http\Controllers;

use App\Models\FoodItem;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route($request->user()->role->value.'.dashboard');
    }

    public function student(Request $request): View
    {
        $user = $request->user();
        $open = ['pending', 'confirmed', 'preparing', 'ready'];

        return view('dashboard.student', [
            'activeOrders' => $user->placedOrders()->whereIn('order_status', $open)->count(),
            'tokensHeld' => $user->heldTokens()
                ->whereHas('order', fn ($q) => $q->whereIn('order_status', $open))
                ->count(),
            'outstandingDues' => $user->placedOrders()
                ->where('payment_status', 'due')
                ->sum(DB::raw('total_amount - paid_amount')),
        ]);
    }

    public function staff(Request $request): View
    {
        $outlet = $request->user()->outlet;
        $orders = Order::query()->where('outlet_id', $outlet?->id);

        return view('dashboard.staff', [
            'outlet' => $outlet,
            'todayOrders' => (clone $orders)->whereDate('pickup_datetime', today())->count(),
            'waiting' => (clone $orders)->whereIn('order_status', ['pending', 'confirmed', 'preparing'])->count(),
            'ready' => (clone $orders)->where('order_status', 'ready')->count(),
        ]);
    }

    public function manager(): View
    {
        return view('dashboard.manager', [
            'outlets' => Outlet::where('is_active', true)->count(),
            'foodItems' => FoodItem::count(),
            'todayOrders' => Order::whereDate('created_at', today())->count(),
            'totalDue' => Order::where('payment_status', 'due')->sum(DB::raw('total_amount - paid_amount')),
        ]);
    }

    public function admin(): View
    {
        return view('dashboard.admin', [
            'byRole' => User::query()->selectRaw('role, count(*) as total')->groupBy('role')->pluck('total', 'role'),
            'inactive' => User::where('is_active', false)->count(),
        ]);
    }
}