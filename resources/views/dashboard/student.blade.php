@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="hero mb-4">
        <h1 class="h3">Hello, {{ auth()->user()->name }}</h1>
        <p class="mb-0">Student ID {{ auth()->user()->student_id }}</p>
    </div>
    <div class="row g-3">
        <x-stat-card label="Active orders" :value="$activeOrders" icon="bi-bag" />
        <x-stat-card label="Food tokens you hold" :value="$tokensHeld" icon="bi-ticket-perforated" />
        <x-stat-card label="Outstanding dues" :value="'৳'.number_format((float) $outstandingDues, 2)" icon="bi-wallet2" />
    </div>
@endsection