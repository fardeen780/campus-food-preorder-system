@extends('layouts.app')
@section('title', 'Dashboard')
@section('content')
    <div class="hero mb-4">
        <h1 class="h3">System administration</h1>
        <p class="mb-0">Administrator: {{ auth()->user()->name }}</p>
    </div>
    <div class="row g-3">
        <x-stat-card label="Students" :value="$byRole['student'] ?? 0" icon="bi-mortarboard" />
        <x-stat-card label="Cafeteria staff" :value="$byRole['staff'] ?? 0" icon="bi-person-badge" />
        <x-stat-card label="Managers" :value="$byRole['manager'] ?? 0" icon="bi-briefcase" />
        <x-stat-card label="Administrators" :value="$byRole['admin'] ?? 0" icon="bi-shield-lock" />
        <x-stat-card label="Deactivated accounts" :value="$inactive" icon="bi-person-x" />
    </div>
@endsection