@extends('layouts.app')
@section('title', 'Checkout')
@section('content')
    <div class="row g-4">
        <div class="col-12 col-lg-7">
            @foreach ($groups as $group)
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-header bg-white fw-bold"><i class="bi bi-shop"></i> {{ $group['outlet']->name }}</div>
                    <ul class="list-group list-group-flush">
                        @foreach ($group['items'] as $ci)
                            <li class="list-group-item d-flex justify-content-between align-items-center gap-2">
                                <span class="item-line">
                                    <x-food-thumb :item="$ci->foodItem" :size="44" />
                                    <span>{{ $ci->quantity }} × {{ $ci->foodItem->name }}</span>
                                </span>
                                <span>৳{{ number_format((float) $ci->line_total, 2) }}</span>
                            </li>
                        @endforeach
                        <li class="list-group-item d-flex justify-content-between fw-bold">
                            <span>Order total</span>
                            <span>৳{{ number_format((float) $group['subtotal'], 2) }}</span>
                        </li>
                    </ul>
                </div>
            @endforeach
        </div>

        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="h5">Pickup time</h3>

                    @if (count($slots) === 0)
                        <div class="alert alert-warning">No pickup times are available right now.</div>
                    @else
                        <form method="POST" action="{{ route('student.checkout.place') }}" id="checkoutForm">
                            @csrf
                            <select name="pickup" class="form-select mb-3" required aria-label="Pickup time">
                                <option value="">Choose a time...</option>
                                @foreach ($slots as $day => $times)
                                    <optgroup label="{{ $day }}">
                                        @foreach ($times as $value => $label)
                                            <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>

                            <ul class="small text-muted ps-3">
                                <li>You can cancel until 30 minutes before pickup. A pickup less than 30 minutes away cannot be cancelled.</li>
                                <li>Pay at the counter when you collect. Anything unpaid after collection appears under Outstanding Dues.</li>
                                <li>Each outlet gets its own order and food token.</li>
                            </ul>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="fw-bold">Total</span>
                                <span class="h5 mb-0">৳{{ number_format((float) $total, 2) }}</span>
                            </div>
                            <button class="btn btn-brand w-100" type="submit" id="placeBtn">Place order</button>
                        </form>
                    @endif

                    <a href="{{ route('student.cart') }}" class="btn btn-light w-100 mt-2">Back to cart</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('checkoutForm')?.addEventListener('submit', function () {
            document.getElementById('placeBtn').disabled = true;
        });
    </script>
@endsection