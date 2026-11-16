@extends('layouts.app')
@section('title', 'My Cart')
@section('content')
    @if ($groups->isEmpty())
        <div class="text-center py-5">
            <div class="fs-1">🛒</div>
            <p class="text-muted">Your cart is empty.</p>
            <a href="{{ route('student.menu') }}" class="btn btn-brand">Browse the menu</a>
        </div>
    @else
        <p class="text-muted">Each outlet prepares its own food, so checkout creates a separate order and token for every outlet below.</p>

        @foreach ($groups as $group)
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white fw-bold">{{ $group['outlet']->icon }} {{ $group['outlet']->name }}</div>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr><th>Item</th><th>Price</th><th>Quantity</th><th class="text-end">Total</th><th></th></tr>
                        </thead>
                        <tbody>
                        @foreach ($group['items'] as $ci)
                            @php $food = $ci->foodItem; @endphp
                            <tr>
                                <td>
                                    {{ $food->icon }} {{ $food->name }}
                                    @unless ($food->isOrderable())
                                        <span class="badge bg-danger">Unavailable</span>
                                    @endunless
                                </td>
                                <td>৳{{ number_format((float) $food->price, 2) }}</td>
                                <td>
                                    <form method="POST" action="{{ route('student.cart.update', $ci->id) }}" class="d-flex gap-2">
                                        @csrf @method('PATCH')
                                        <input type="number" name="quantity" value="{{ $ci->quantity }}" min="1" max="20"
                                               class="form-control form-control-sm" style="width:75px" aria-label="Quantity">
                                        <button class="btn btn-light btn-sm" type="submit">Update</button>
                                    </form>
                                </td>
                                <td class="text-end">৳{{ number_format((float) $ci->line_total, 2) }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ route('student.cart.remove', $ci->id) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm" type="submit" aria-label="Remove">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-end fw-bold">Subtotal</td>
                                <td class="text-end fw-bold">৳{{ number_format((float) $group['subtotal'], 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @endforeach

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="h5 mb-0">Total: ৳{{ number_format((float) $total, 2) }}</div>
            @if ($blocked)
                <div class="text-danger">Remove the unavailable items to continue.</div>
            @elseif (Route::has('student.checkout'))
                <a href="{{ route('student.checkout') }}" class="btn btn-brand">Proceed to checkout</a>
            @else
                <button class="btn btn-brand" disabled>Checkout is coming in the next step</button>
            @endif
        </div>
    @endif
@endsection