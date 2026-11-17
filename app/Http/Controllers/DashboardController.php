<?php

namespace App\Http\Controllers;

use App\Models\FoodCategory;
use App\Models\FoodItem;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\User;
use App\Services\TransferService;
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

    public function student(Request $request, TransferService $transfers): View
    {
        $transfers->expireDue();

        $user = $request->user();
        $open = ['pending', 'confirmed', 'preparing', 'ready'];

        $menu = FoodItem::available()
            ->with(['outlet', 'category'])
            ->whereHas('outlet', fn ($q) => $q->where('is_active', true))
            ->whereHas('category', fn ($q) => $q->where('is_active', true));

        return view('dashboard.student', [
            'totalOrders' => $user->placedOrders()->count(),
            'activeOrders' => $user->placedOrders()->whereIn('order_status', $open)->count(),
            'tokensHeld' => $user->heldTokens()
                ->whereHas('order', fn ($q) => $q->whereIn('order_status', $open))
                ->count(),
            'outstandingDues' => $user->placedOrders()
                ->where('payment_status', 'due')
                ->sum(DB::raw('total_amount - paid_amount')),
            'recentOrders' => $user->placedOrders()->with('outlet')->latest('id')->take(5)->get(),
            'tokens' => $user->heldTokens()
                ->whereHas('order', fn ($q) => $q->whereIn('order_status', $open))
                ->with('order.outlet')
                ->latest('id')
                ->take(3)
                ->get(),
            'pendingRequests' => $user->receivedTransfers()
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->with(['sender', 'foodToken.order.items', 'foodToken.order.outlet'])
                ->latest('id')
                ->get(),
            'sentTransfers' => $user->sentTransfers()->with(['receiver', 'foodToken.order'])->latest('id')->take(3)->get(),
            'receivedTransfers' => $user->receivedTransfers()->with(['sender', 'foodToken.order'])->latest('id')->take(3)->get(),
            'notifications' => $user->notifications()->take(4)->get(),
            'featured' => $menu->orderBy('name')->take(8)->get(),
            'categories' => FoodCategory::where('is_active', true)->orderBy('sort_order')->get(),
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