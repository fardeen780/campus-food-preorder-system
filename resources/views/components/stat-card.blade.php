@props(['label', 'value', 'icon' => 'bi-circle'])
<div class="col-12 col-md-4">
    <div class="stat-card">
        <i class="bi {{ $icon }}"></i>
        <div class="stat-value">{{ $value }}</div>
        <div class="stat-label">{{ $label }}</div>
    </div>
</div>