<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Outlet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ManagerOrderController extends Controller
{
    public function index(Request $request): View
    {
        $f = $request->validate([
            'q' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', Rule::in(['pending', 'confirmed', 'preparing', 'ready', 'completed', 'cancelled'])],
            'payment' => ['nullable', Rule::in(['unpaid', 'partially_paid', 'paid', 'due'])],
            'outlet' => ['nullable', 'integer'],
        ]);

        $query = Order::query()->with(['outlet', 'originalStudent', 'token.currentOwner']);

        if (! empty($f['q'])) {
            $query->whereRaw('order_number ilike ?', ['%'.addcslashes($f['q'], '%_\\').'%']);
        }
        if (! empty($f['status'])) {
            $query->where('order_status', $f['status']);
        }
        if (! empty($f['payment'])) {
            $query->where('payment_status', $f['payment']);
        }
        if (! empty($f['outlet'])) {
            $query->where('outlet_id', $f['outlet']);
        }

        return view('manager.orders', [
            'orders' => $query->latest('id')->paginate(15)->withQueryString(),
            'outlets' => Outlet::orderBy('name')->get(),
        ]);
    }

    public function show(int $id): View
    {
        $order = Order::with(['outlet', 'items', 'originalStudent', 'token.currentOwner', 'payments'])->findOrFail($id);

        return view('manager.order-show', ['order' => $order]);
    }

    public function dues(): View
    {
        $students = User::query()
            ->select('users.*')
            ->selectRaw('sum(orders.total_amount - orders.paid_amount) as due_total, count(orders.id) as due_orders')
            ->join('orders', 'orders.original_student_id', '=', 'users.id')
            ->where('orders.payment_status', 'due')
            ->groupBy('users.id')
            ->orderByDesc('due_total')
            ->paginate(15);

        $grandTotal = Order::where('payment_status', 'due')->sum(DB::raw('total_amount - paid_amount'));

        return view('manager.dues', ['students' => $students, 'grandTotal' => $grandTotal]);
    }

    public function studentDues(int $id): View
    {
        $student = User::where('role', 'student')->findOrFail($id);

        $orders = Order::where('original_student_id', $student->id)
            ->where('payment_status', 'due')
            ->with(['outlet', 'items', 'token.currentOwner'])
            ->latest('completed_at')
            ->get();

        return view('manager.dues-student', ['student' => $student, 'orders' => $orders]);
    }
}