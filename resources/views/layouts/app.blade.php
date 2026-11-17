@php
    $user = auth()->user();
    $role = $user->role;
    $isStudent = $role->value === 'student';
    $logo = \App\Support\Brand::logoUrl();
    $appName = 'Campus Food Pre-Order System';

    $nav = match ($role->value) {
        'staff' => [
            ['staff.dashboard', 'bi-speedometer2', 'Dashboard'],
            ['staff.orders', 'bi-list-check', 'Order Queue'],
            ['staff.verify', 'bi-qr-code-scan', 'Verify Token'],
        ],
        'manager' => [
            ['manager.dashboard', 'bi-speedometer2', 'Dashboard'],
            ['manager.foods', 'bi-egg-fried', 'Food Items'],
            ['manager.categories', 'bi-tags', 'Categories'],
            ['manager.orders', 'bi-bag', 'All Orders'],
            ['manager.dues', 'bi-cash-stack', 'Outstanding Dues'],
        ],
        'admin' => [
            ['admin.dashboard', 'bi-speedometer2', 'Dashboard'],
            ['admin.users', 'bi-people', 'Users'],
            ['admin.activity', 'bi-shield-check', 'Activity Log'],
        ],
        default => [],
    };
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Dashboard') · {{ $appName }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Manrope:wght@600;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
</head>
<body>

@if ($isStudent)
    @php
        $cartCount = (int) $user->cartItems()->sum('quantity');
        $unread = $user->unreadNotifications()->count();
        $pendingTransfers = $user->receivedTransfers()->where('status', 'pending')->where('expires_at', '>', now())->count();
        $words = preg_split('/\s+/', trim($user->name)) ?: [];
        $initials = mb_strtoupper(mb_substr($words[0] ?? '?', 0, 1).(count($words) > 1 ? mb_substr(end($words), 0, 1) : ''));
        $firstName = \Illuminate\Support\Str::before($user->name, ' ');
        $links = [
            ['student.dashboard', 'bi-house', 'Home'],
            ['student.menu', 'bi-grid', 'Menu'],
            ['student.orders', 'bi-bag', 'My Orders'],
            ['student.tokens', 'bi-ticket-perforated', 'My Food Tokens'],
        ];
    @endphp

    <nav class="navbar navbar-expand-lg student-nav sticky-top" aria-label="Main navigation">
        <div class="container-xl">
            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ route('student.dashboard') }}">
                @if ($logo)
                    <img src="{{ $logo }}" alt="Independent University, Bangladesh (IUB)" class="brand-logo">
                @endif
                <span class="brand-text">
                    <span class="{{ $logo ? 'd-none d-sm-inline' : '' }} d-md-none d-lg-inline d-xl-none">Campus Food</span>
                    <span class="d-none d-md-inline d-lg-none d-xl-inline">{{ $appName }}</span>
                </span>
            </a>

            <div class="d-flex align-items-center gap-1 ms-auto order-lg-3">
                <a class="icon-btn" href="{{ route('student.cart') }}" aria-label="My cart, {{ $cartCount }} items">
                    <i class="bi bi-cart3"></i>
                    @if ($cartCount > 0)
                        <span class="badge rounded-pill text-bg-success">{{ $cartCount }}</span>
                    @endif
                </a>
                <a class="icon-btn" href="{{ route('student.notifications') }}" aria-label="Notifications, {{ $unread }} unread">
                    <i class="bi bi-bell"></i>
                    @if ($unread > 0)
                        <span class="badge rounded-pill text-bg-danger">{{ $unread }}</span>
                    @endif
                </a>
                <div class="dropdown">
                    <button class="btn p-0 border-0 d-flex align-items-center gap-2 ms-1" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu">
                        <span class="avatar">{{ $initials }}</span>
                        <span class="d-none d-xl-inline fw-semibold small">{{ $firstName }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow">
                        <li class="px-3 py-2">
                            <div class="fw-bold">{{ $user->name }}</div>
                            <div class="small text-muted">Student ID {{ $user->student_id }}</div>
                            <div class="small text-muted">{{ $user->email }}</div>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="dropdown-item" type="submit"><i class="bi bi-box-arrow-right"></i> Log out</button>
                            </form>
                        </li>
                    </ul>
                </div>
                <button class="navbar-toggler ms-1" type="button" data-bs-toggle="collapse" data-bs-target="#studentNav"
                        aria-controls="studentNav" aria-expanded="false" aria-label="Show menu">
                    <span class="navbar-toggler-icon"></span>
                </button>
            </div>

            <div class="collapse navbar-collapse order-lg-2" id="studentNav">
                <ul class="navbar-nav me-auto gap-lg-1 mt-2 mt-lg-0">
                    @foreach ($links as [$route, $icon, $label])
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs($route) ? 'active' : '' }}"
                               href="{{ route($route) }}" @if (request()->routeIs($route)) aria-current="page" @endif>
                                <i class="bi {{ $icon }}"></i> {{ $label }}
                            </a>
                        </li>
                    @endforeach
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle {{ request()->routeIs('student.transfers.*') ? 'active' : '' }}"
                           href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-arrow-left-right"></i> Transfers
                            @if ($pendingTransfers > 0)
                                <span class="badge text-bg-warning">{{ $pendingTransfers }}</span>
                            @endif
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="{{ route('student.transfers.received') }}">Received requests</a></li>
                            <li><a class="dropdown-item" href="{{ route('student.transfers.sent') }}">Sent requests</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="container-xl py-4">
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

    <footer class="site-footer">
        <div class="container-xl">Independent University, Bangladesh. CSE-451 Software Engineering project.</div>
    </footer>
@else
    <div class="app">
        <aside class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" id="sidebar" aria-label="Main navigation">
            <div class="sidebar-inner w-100">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        @if ($logo)
                            <span class="brand-chip mb-2">
                                <img src="{{ $logo }}" alt="Independent University, Bangladesh (IUB)" class="brand-logo">
                            </span>
                        @endif
                        <div class="sidebar-brand">Campus Food Pre-Order</div>
                        <div class="sidebar-role">{{ $role->label() }}</div>
                    </div>
                    <button type="button" class="btn-close btn-close-white d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Close"></button>
                </div>
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
@endif

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>