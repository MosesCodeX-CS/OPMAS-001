<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OPMAS — @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        :root {
            --kijabe-navy:  #1B3A6B;
            --kijabe-blue:  #2B8AC6;
            --kijabe-gold:  #E8A020;
            --kijabe-bg:    #F4F6F9;
            --kijabe-card:  #FFFFFF;
            --kijabe-border:#DDE3EE;
            --kijabe-text:  #1A2A3A;
            --kijabe-muted: #6B7A90;
        }
        body { background-color: var(--kijabe-bg); color: var(--kijabe-text); }
    </style>
</head>
<body class="min-h-screen flex">

    <aside class="w-72 flex flex-col min-h-screen fixed shadow-lg bg-[#102A5F] text-white">
        <div class="px-4 py-5">
            <div class="bg-white rounded-3xl p-4 shadow-sm">
                <div class="w-16 h-16 rounded-2xl bg-slate-100 flex items-center justify-center mb-4">
                    <img src="{{ asset('images/kijabe-logo.svg') }}" alt="Kijabe Hospital" class="h-10 w-10 object-contain" />
                </div>
                <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-slate-500 mb-2">OPMAS-001</p>
                <p class="text-lg font-semibold text-slate-900 leading-tight">Kijabe Hospital</p>
                <p class="text-sm text-slate-500 mt-1">OX-PLANT-01</p>
            </div>
        </div>
        <nav class="flex-1 px-4 py-4 space-y-2">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
               {{ request()->routeIs('dashboard')
                    ? 'text-white'
                    : 'hover:text-white' }}"
               style="{{ request()->routeIs('dashboard')
                    ? 'background-color:#2B8AC6;'
                    : 'color:#93B8D8;' }}">
                Dashboard
            </a>
            <a href="{{ route('alarms') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
               {{ request()->routeIs('alarms')
                    ? 'text-white'
                    : 'hover:text-white' }}"
               style="{{ request()->routeIs('alarms')
                    ? 'background-color:#2B8AC6;'
                    : 'color:#93B8D8;' }}">
                Alarms
            </a>
            <a href="{{ route('reports') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
               {{ request()->routeIs('reports')
                    ? 'text-white'
                    : 'hover:text-white' }}"
               style="{{ request()->routeIs('reports')
                    ? 'background-color:#2B8AC6;'
                    : 'color:#93B8D8;' }}">
                Reports
            </a>
            <a href="{{ route('equipment') }}"
               class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-colors
               {{ request()->routeIs('equipment')
                    ? 'text-white'
                    : 'hover:text-white' }}"
               style="{{ request()->routeIs('equipment')
                    ? 'background-color:#2B8AC6;'
                    : 'color:#93B8D8;' }}">
                Equipment
            </a>
        </nav>
        <div class="px-5 py-4 border-t border-blue-900 text-xs" style="color:#93B8D8;">
            {{ Auth::user()->name ?? 'Operator' }}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="block mt-1 text-red-400 hover:text-red-300">Logout</button>
            </form>
        </div>
    </aside>

    <main class="ml-60 flex-1 p-6" style="background-color:#F4F6F9;">
        @yield('content')
    </main>

</body>
</html>