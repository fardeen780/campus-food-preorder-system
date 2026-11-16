<?php

namespace App\Http\Controllers;

use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Exceptions\OrderException;
use App\Exceptions\PaymentException;
use App\Models\ActivityLog;
use App\Models\FoodToken;
use App\Models\Order;
use App\Services\PaymentService;
use App\Services\StaffOrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StaffOrderController extends Controller
{
    private function scoped(Request $request)
    {
        return Order::query()->where('outlet_id', $request->user()->outlet_id ?? 0);
    }

    public function index(Request $request): View
    {
        $filter = $request->validate([
            'status' => ['nullable', Rule::in(['active', 'today', 'completed', 'cancelled', 'all'])],
        ])['status'] ?? 'active';

        $query = $this->scoped($request)->with(['items', 'token.currentOwner']);

        match ($filter) {
            'active' => $query->whereIn('order_status', ['pending', 'confirmed', 'preparing', 'ready'])->orderBy('pickup_datetime'),
            'today' => $query->whereDate('pickup_datetime', today())->orderBy('pickup_datetime'),
            'completed' => $query->where('order_status', 'completed')->latest('completed_at'),
            'cancelled' => $query->where('order_status', 'cancelled')->latest('cancelled_at'),
            'all' => $query->latest('id'),
        };

        return view('staff.orders', [
            'orders' => $query->paginate(15)->withQueryString(),
            'filter' => $filter,
        ]);
    }

    public function show(Request $request, int $id): View
    {
        $order = $this->scoped($request)
            ->with(['items', 'outlet', 'token.currentOwner', 'originalStudent', 'payments'])
            ->findOrFail($id);

        return view('staff.order-show', ['order' => $order]);
    }

    public function advance(Request $request, StaffOrderService $service, int $id): RedirectResponse
    {
        $data = $request->validate([
            'to' => ['required', Rule::in(['confirmed', 'preparing', 'ready'])],
        ]);

        $this->scoped($request)->findOrFail($id);

        try {
            $order = $service->advance($request->user(), $id, OrderStatus::from($data['to']));
        } catch (OrderException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Order '.$order->order_number.' is now '.strtolower($order->order_status->label()).'.');
    }

    public function collect(Request $request, StaffOrderService $service, int $id): RedirectResponse
    {
        $data = $request->validate([
            'token_code' => ['required', 'string', 'max:20'],
        ]);

        $this->scoped($request)->findOrFail($id);

        try {
            $order = $service->collect($request->user(), $id, $data['token_code']);
        } catch (OrderException $e) {
            return back()->with('error', $e->getMessage());
        }

        $message = 'Order '.$order->order_number.' collected.';

        if ($order->payment_status->value === 'due') {
            $message .= ' A balance of ৳'.number_format((float) $order->due_amount, 2).' is recorded as due on the student who placed the order.';
        }

        return redirect()->route('staff.orders.show', $order->id)->with('status', $message);
    }

    public function pay(Request $request, PaymentService $payments, int $id): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'regex:/^\d{1,8}(\.\d{1,2})?$/'],
            'method' => ['required', Rule::enum(PaymentMethod::class)],
            'reference' => ['nullable', 'string', 'max:100', 'required_unless:method,cash'],
        ], [
            'amount.regex' => 'Enter a valid amount with at most two decimal places.',
            'reference.required_unless' => 'bKash and Nagad payments need a transaction reference.',
        ]);

        $order = $this->scoped($request)->findOrFail($id);
        $method = PaymentMethod::from($data['method']);
        $reference = $method === PaymentMethod::Cash ? null : trim((string) $data['reference']);

        try {
            $payments->record($order, $data['amount'], $method, $request->user(), $reference);
        } catch (PaymentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }

        ActivityLog::record('payment.recorded', $request->user(), $order,
            'Payment recorded for order '.$order->order_number, ['method' => $method->value]);

        return back()->with('status', 'Payment recorded. Amount still due: ৳'.number_format((float) $order->due_amount, 2).'.');
    }

    public function verifyForm(): View
    {
        return view('staff.verify');
    }

    public function verify(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token_code' => ['required', 'string', 'max:20'],
        ]);

        $code = strtoupper((string) preg_replace('/\s+/', '', $data['token_code']));
        $outletId = $request->user()->outlet_id ?? 0;

        $token = FoodToken::query()
            ->with('currentOwner')
            ->where('token_code', $code)
            ->whereHas('order', fn ($q) => $q->where('outlet_id', $outletId))
            ->first();

        if (! $token) {
            ActivityLog::record('token.verify_failed', $request->user(), null, 'Token lookup found nothing');

            return back()->withInput()->with('error', 'No valid token found for your outlet.');
        }

        $holder = $token->currentOwner;

        return redirect()->route('staff.orders.show', $token->order_id)
            ->with('status', 'Token found. Current holder: '.$holder->name.' (ID '.$holder->student_id.').');
    }
}