@props(['order', 'payment' => true])
@php
    $orderColor = match ($order->order_status->value) {
        'pending' => 'secondary',
        'confirmed' => 'info',
        'preparing' => 'warning',
        'ready' => 'success',
        'completed' => 'dark',
        'cancelled' => 'danger',
    };
    $payColor = match ($order->payment_status->value) {
        'unpaid' => 'secondary',
        'partially_paid' => 'warning',
        'paid' => 'success',
        'due' => 'danger',
    };
@endphp
<span class="badge text-bg-{{ $orderColor }}">{{ $order->order_status->label() }}</span>
@if ($payment)
    <span class="badge text-bg-{{ $payColor }}">{{ $order->payment_status->label() }}</span>
@endif