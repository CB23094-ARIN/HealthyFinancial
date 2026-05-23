<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthyFinancial</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-gray-900">
    @unless(request()->routeIs('login', 'register'))
    <nav class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex min-h-20 flex-col gap-4 py-5 md:flex-row md:items-center md:justify-between">
                <a href="{{ route('dashboard') }}" class="hf-brand hf-title text-2xl font-black tracking-tight" aria-label="HealthyFinancial dashboard">
                    <img src="{{ asset('images/healthyfinancial-icon.png') }}" alt="" class="hf-brand-icon">
                    <span>HealthyFinancial</span>
                </a>

                <div class="flex flex-wrap items-center gap-4 text-base">
                    @auth
                        <a href="{{ route('dashboard') }}" class="hf-link {{ request()->routeIs('dashboard') ? 'hf-link-active' : '' }} font-medium">Dashboard</a>
                        <a href="{{ route('transactions') }}" class="hf-link {{ request()->routeIs('transactions') ? 'hf-link-active' : '' }} font-medium">Transactions</a>
                        <a href="{{ route('can-afford.form') }}" class="hf-link {{ request()->routeIs('can-afford.*') ? 'hf-link-active' : '' }} font-medium">Can I Afford this?</a>
                        <a href="{{ route('scan-receipt.form') }}" class="hf-link {{ request()->routeIs('scan-receipt.*') ? 'hf-link-active' : '' }} font-medium">Scan Receipt</a>
                        <a href="{{ route('leaderboard') }}" class="hf-link {{ request()->routeIs('leaderboard') ? 'hf-link-active' : '' }} font-medium">Leaderboard</a>
                        <a href="{{ route('profile.edit') }}" class="hf-link {{ request()->routeIs('profile.*') ? 'hf-link-active' : '' }} font-medium">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="hf-logout font-semibold">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hf-link {{ request()->routeIs('login') ? 'hf-link-active' : '' }} font-medium">Login</a>
                        <a href="{{ route('register') }}" class="hf-btn {{ request()->routeIs('register') ? 'hf-btn-active' : '' }} rounded-lg px-4 py-2 font-semibold">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>
    @endunless

    <main class="py-8">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="hf-note mb-4 rounded-lg p-4 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</body>
</html>
