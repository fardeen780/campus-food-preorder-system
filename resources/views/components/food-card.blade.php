@props(['item'])
@php $orderable = $item->isOrderable(); @endphp
<article class="food-card">
    <div class="food-card-img">
        <x-food-image :item="$item" />
        @if ($orderable)
            <span class="badge text-bg-success">Available</span>
        @else
            <span class="badge text-bg-secondary">Unavailable</span>
        @endif
    </div>
    <div class="food-card-body">
        <div class="food-card-meta">
            <span>{{ $item->outlet?->name }}</span>
            <span class="food-card-cat">{{ $item->category?->name }}</span>
        </div>
        <h3 class="food-card-title">{{ $item->name }}</h3>
        <p class="food-card-desc">{{ $item->description }}</p>
        <div class="food-card-footer">
            <span class="food-price">৳{{ number_format((float) $item->price, 2) }}</span>
            <form method="POST" action="{{ route('student.cart.add') }}" class="d-flex gap-2 align-items-center">
                @csrf
                <input type="hidden" name="food_item_id" value="{{ $item->id }}">
                <input type="number" name="quantity" value="1" min="1" max="20"
                       class="form-control form-control-sm qty-input"
                       aria-label="Quantity of {{ $item->name }}" @disabled(! $orderable)>
                <button class="btn btn-brand btn-sm" type="submit" @disabled(! $orderable)>
                    <i class="bi bi-cart-plus"></i> Add to cart
                </button>
            </form>
        </div>
    </div>
</article>