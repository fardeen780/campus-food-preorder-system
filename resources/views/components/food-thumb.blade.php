@props(['item' => null, 'name' => null, 'size' => 48])
<img src="{{ $item?->image_url ?? asset('images/food-fallback.svg') }}"
     alt="{{ $name ?? $item?->name ?? 'Food item' }}"
     style="width: {{ $size }}px; height: {{ $size }}px;"
     loading="lazy"
     decoding="async"
     onerror="this.onerror=null;this.src='{{ asset('images/food-fallback.svg') }}';"
     {{ $attributes->merge(['class' => 'food-thumb']) }}>