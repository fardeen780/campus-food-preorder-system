@php $logo = \App\Support\Brand::logoUrl(); @endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · Campus Food Pre-Order System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Manrope:wght@600;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}?v={{ filemtime(public_path('css/app.css')) }}" rel="stylesheet">
</head>
<body class="guest-body">
    <div class="container py-5 d-flex flex-column align-items-center">
        <div class="text-center mb-4">
            @if ($logo)
                <img src="{{ $logo }}" alt="Independent University, Bangladesh (IUB)" class="brand-logo brand-logo-lg mx-auto mb-3">
            @endif
            <h1 class="h4 brand-title mb-1">Campus Food Pre-Order System</h1>
            <p class="text-muted mb-0">Independent University, Bangladesh</p>
        </div>
        <div class="card auth-card w-100">
            <div class="card-body p-4">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>