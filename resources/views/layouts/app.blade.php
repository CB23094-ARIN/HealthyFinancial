<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HealthyFinancial</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz@14..32&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 text-gray-900">
    <nav class="border-b border-gray-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex min-h-16 flex-col gap-3 py-4 md:flex-row md:items-center md:justify-between">
                <a href="{{ route('dashboard') }}" class="text-xl font-bold text-gray-950">HealthyFinancial</a>

                <div class="flex flex-wrap items-center gap-3 text-sm">
                    @auth
                        <a href="{{ route('dashboard') }}" class="font-medium text-gray-600 hover:text-emerald-700">Dashboard</a>
                        <a href="{{ route('transactions') }}" class="font-medium text-gray-600 hover:text-emerald-700">Transactions</a>
                        <a href="{{ route('can-afford.form') }}" class="font-medium text-gray-600 hover:text-emerald-700">Can I Afford this?</a>
                        <a href="{{ route('scan-receipt.form') }}" class="font-medium text-gray-600 hover:text-emerald-700">Scan Receipt</a>
                        <a href="{{ route('leaderboard') }}" class="font-medium text-gray-600 hover:text-emerald-700">Leaderboard</a>
                        <a href="{{ route('profile.edit') }}" class="font-medium text-gray-600 hover:text-emerald-700">Profile</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="font-medium text-rose-600 hover:text-rose-700">Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="font-medium text-gray-600 hover:text-emerald-700">Login</a>
                        <a href="{{ route('register') }}" class="rounded-lg bg-gray-950 px-4 py-2 font-semibold text-white hover:bg-gray-800">Register</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <main class="py-6">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-4 rounded-lg border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @yield('content')
        </div>
    </main>
</body>
</html>
