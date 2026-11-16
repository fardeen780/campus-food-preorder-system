@extends('layouts.app')
@section('title', 'Transfer Token')
@section('content')
    @php $order = $token->order; @endphp
    <div class="row justify-content-center">
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h3 class="h5">{{ $order->outlet->icon }} {{ $order->outlet->name }} · {{ $order->order_number }}</h3>
                    <p class="mb-1"><strong>Food:</strong> {{ $order->items->map(fn ($i) => $i->quantity.' × '.$i->item_name)->implode(', ') }}</p>
                    <p><strong>Pickup:</strong> {{ $order->pickup_datetime->format('D j M, g:i A') }}</p>

                    <form method="POST" action="{{ route('student.tokens.transfer.send', $token->id) }}">
                        @csrf
                        <label class="form-label" for="student_id">Receiving student's ID (7 digits)</label>
                        <input id="student_id" name="student_id" value="{{ old('student_id') }}" required
                               inputmode="numeric" maxlength="7" pattern="\d{7}"
                               class="form-control @error('student_id') is-invalid @enderror">
                        @error('student_id') <div class="invalid-feedback">{{ $message }}</div> @enderror

                        <ul class="small text-muted mt-3 ps-3">
                            <li>The other student must accept before the token moves. Until then it stays yours.</li>
                            <li>You can cancel the request at any time while it is pending.</li>
                            <li>The request expires after {{ $expiryHours }} hours, or at pickup time if that is sooner.</li>
                            <li><strong>You remain responsible for paying for this order,</strong> even after they accept.</li>
                        </ul>

                        <div class="d-flex gap-2">
                            <button class="btn btn-brand" type="submit">Send transfer request</button>
                            <a href="{{ route('student.tokens') }}" class="btn btn-light">Back</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection