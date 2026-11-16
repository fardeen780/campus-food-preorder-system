<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title') · {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;700&family=Manrope:wght@600;800&display=swap" rel="stylesheet">
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div class="container py-5 d-flex flex-column align-items-center">
        <h1 class="h3 mb-1">🍽️ {{ config('app.name') }}</h1>
        <p class="text-muted mb-4">Independent University, Bangladesh</p>
        <div class="card auth-card w-100">
            <div class="card-body p-4">
                @yield('content')
            </div>
        </div>
    </div>
</body>
</html>