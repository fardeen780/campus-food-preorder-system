@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="hero mb-4">
        <h1 class="h3">Cafeteria overview</h1>
        <p class="mb-0">Manager: {{ auth()->user()->name }}</p>
    </div>
    <div class="row g-3">
        <x-stat-card label="Active outlets" :value="$outlets" icon="bi-shop" />
        <x-stat-card label="Food items" :value="$foodItems" icon="bi-egg-fried" />
        <x-stat-card label="Orders placed today" :value="$todayOrders" icon="bi-bag" />
        <x-stat-card label="Total outstanding dues" :value="'৳'.number_format((float) $totalDue, 2)" icon="bi-cash-stack" />
    </div>
@endsection