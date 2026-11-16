@php
    $user = auth()->user();
    $role = $user->role;
    $nav = match ($role->value) {
        'student' => [
            ['student.dashboard', 'bi-speedometer2', 'Dashboard'],
            ['student.menu', 'bi-grid', 'Food Menu'],
            ['student.cart', 'bi-cart3', 'My Cart'],
            ['student.orders', 'bi-bag', 'My Orders'],
            ['student.tokens', 'bi-ticket-perforated', 'My Food Tokens'],
            ['student.transfers.sent', 'bi-send', 'Sent Transfer Requests'],
            ['student.transfers.received', 'bi-inbox', 'Received Transfer Requests'],
            ['student.notifications', 'bi-bell', 'Notifications'],
            ['student.payments', 'bi-wallet2', 'Payments and Dues'],
            ['student.profile', 'bi-person', 'My Profile'],
        ],
        'staff' => [
            ['staff.dashboard', 'bi-speedometer2', 'Dashboard'],
            ['staff.orders', 'bi-list-check', 'Order Queue'],
            ['staff.verify', 'bi-qr-code-scan', 'Verify Token'],
        ],
        'manager' => [
            ['manager.dashboard', 'bi-speedometer2', 'Dashboard'],
            ['manager.foods', 'bi-egg-fried', 'Food Items'],
            ['manager.orders', 'bi-bag', 'All Orders'],
            ['manager.dues', 'bi-cash-stack', 'Outstanding Dues'],
        ],
        'admin' => [
            ['admin.dashboard', 'bi-speedometer2', 'Dashboard'],
            ['admin.users', 'bi-people', 'Users'],
        ],
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Manrope:wght@600;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
<div class="app">
    <aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" aria-label="Main navigation">
        <div class="sidebar-inner w-100">
            <div class="d-flex justify-content-between align-items-start">
                <div class="sidebar-brand">🍽️ Campus Cafe</div>
                <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Close"></button>
            </div>
            <div class="sidebar-role">{{ $role->label() }}</div>
            <nav class="nav flex-column gap-1">
                @foreach ($nav as [$route, $icon, $label])
                    @if (Route::has($route))
                        <a class="nav-link {{ request()->routeIs($route) ? 'active' : '' }}" href="{{ route($route) }}">
                            <i class="bi {{ $icon }}"></i> {{ $label }}
                        </a>
                    @endif
                @endforeach
            </nav>
        </div>
    </aside>

    <div class="main">
        <header class="topbar">
            <button class="btn d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-label="Open menu">
                <i class="bi bi-list fs-4"></i>
            </button>
            <h2 class="h5 mb-0 me-auto">@yield('title', 'Dashboard')</h2>

            @if ($role->value === 'student')
                <a class="btn btn-light position-relative" aria-label="Notifications"
                   href="{{ Route::has('student.notifications') ? route('student.notifications') : '#' }}">
                    <i class="bi bi-bell"></i>
                    @if (($unread = $user->unreadNotifications()->count()) > 0)
                        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">{{ $unread }}</span>
                    @endif
                </a>
            @endif

            <div class="dropdown">
                <button class="btn btn-light dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i> {{ $user->name }}
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><span class="dropdown-item-text text-muted small">{{ $role->label() }}</span></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right"></i> Log out</button>
                        </form>
                    </li>
                </ul>
            </div>
        </header>

        <main class="content">
            @if (session('status'))
                <div class="alert alert-success">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif
                        @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>