@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="hero mb-4">
        <h1 class="h3">{{ $outlet?->name ?? 'No outlet assigned' }}</h1>
        <p class="mb-0">Cafeteria staff: {{ auth()->user()->name }}</p>
    </div>
    @unless ($outlet)
        <div class="alert alert-warning">An administrator must assign you to an outlet before you can see orders.</div>
    @endunless
    <div class="row g-3">
        <x-stat-card label="Orders for pickup today" :value="$todayOrders" icon="bi-calendar-check" />
        <x-stat-card label="Waiting to be prepared" :value="$waiting" icon="bi-hourglass-split" />
        <x-stat-card label="Ready for collection" :value="$ready" icon="bi-check2-circle" />
    </div>
@endsection