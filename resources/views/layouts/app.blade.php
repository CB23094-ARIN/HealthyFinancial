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
    @php
        $hideAppChrome = request()->routeIs('login', 'register');
        $showSidebar = auth()->check() && ! $hideAppChrome;
    @endphp

    @unless($hideAppChrome)
    <header class="hf-topbar">
        <div class="hf-topbar-inner">
            <a href="{{ route('dashboard') }}" class="hf-brand hf-title text-xl font-black tracking-tight" aria-label="HealthyFinancial dashboard">
                <img src="{{ asset('images/healthyfinancial-icon.png') }}" alt="" class="hf-brand-icon">
                <span>HealthyFinancial</span>
            </a>

            @auth
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="hf-power-logout" aria-label="Logout">
                        <img src="{{ asset('images/power.png') }}" alt="" class="hf-power-icon">
                        <span>Logout</span>
                    </button>
                </form>
            @endauth
        </div>
    </header>
    @endunless

    <div class="{{ $showSidebar ? 'hf-app-shell' : '' }}">
        @if($showSidebar)
            <aside class="hf-sidebar" aria-label="Main navigation">
                <a href="{{ route('dashboard') }}" class="hf-sidebar-link {{ request()->routeIs('dashboard') ? 'hf-sidebar-link-active' : '' }}">Dashboard</a>
                <a href="{{ route('transactions') }}" class="hf-sidebar-link {{ request()->routeIs('transactions') ? 'hf-sidebar-link-active' : '' }}">Transactions</a>
                <a href="{{ route('can-afford.form') }}" class="hf-sidebar-link {{ request()->routeIs('can-afford.*') ? 'hf-sidebar-link-active' : '' }}">Can I Afford this?</a>
                <a href="{{ route('scan-receipt.form') }}" class="hf-sidebar-link {{ request()->routeIs('scan-receipt.*') ? 'hf-sidebar-link-active' : '' }}">Scan Receipt</a>
                <a href="{{ route('leaderboard') }}" class="hf-sidebar-link {{ request()->routeIs('leaderboard') ? 'hf-sidebar-link-active' : '' }}">Leaderboard</a>
                <a href="{{ route('profile.edit') }}" class="hf-sidebar-link {{ request()->routeIs('profile.*') ? 'hf-sidebar-link-active' : '' }}">Profile</a>
            </aside>
        @endif

        <main class="{{ $showSidebar ? 'hf-app-main' : 'py-8' }}">
            <div class="mx-auto max-w-none px-4 sm:px-6 lg:px-10">
                @if(session('success'))
                    <div class="hf-note mb-4 rounded-lg p-4 text-sm">
                        {{ session('success') }}
                    </div>
                @endif

                @yield('content')
            </div>
        </main>
    </div>
</body>
</html>
