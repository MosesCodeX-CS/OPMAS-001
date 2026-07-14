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

    <aside class="w-72 flex flex-col min-h-screen fixed shadow-lg bg-[#11264C] text-white">
        <div class="px-5 py-6 border-b border-blue-900">
            <div class="rounded-3xl bg-white/10 p-4">
                <div class="h-16 w-16 rounded-3xl bg-white/10 p-3 flex items-center justify-center mb-4">
                    <img src="{{ asset('images/kijabe-logo.svg') }}" alt="Kijabe Hospital" class="h-full w-full object-contain" />
                </div>
                <p class="text-[10px] uppercase tracking-[0.3em] text-[#8FB5E2]">OPMAS-001</p>
                <p class="mt-3 text-lg font-semibold text-white leading-snug">Kijabe Hospital</p>
                <p class="mt-1 text-xs text-[#9BB4D9]">OX-PLANT-01</p>
            </div>
        </div>

        <nav class="flex-1 px-4 py-5 space-y-1">
            <a href="{{ route('dashboard') }}"
               class="sidebar-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="icon">⌘</span>
                <span>Dashboard</span>
            </a>
            <a href="{{ route('alarms') }}"
               class="sidebar-link {{ request()->routeIs('alarms') ? 'active' : '' }}">
                <span class="icon">⚠</span>
                <span>Alarms</span>
            </a>
            <a href="{{ route('reports') }}"
               class="sidebar-link {{ request()->routeIs('reports') ? 'active' : '' }}">
                <span class="icon">📄</span>
                <span>Reports</span>
            </a>
            <a href="{{ route('equipment') }}"
               class="sidebar-link {{ request()->routeIs('equipment') ? 'active' : '' }}">
                <span class="icon">🛠</span>
                <span>Equipment</span>
            </a>

            <div class="mt-6 px-3 pt-3 text-xs font-semibold uppercase tracking-[0.18em] text-[#7A93C3]">
                Administration
            </div>
            <a href="#" class="sidebar-link">
                <span class="icon">👤</span>
                <span>User Management</span>
            </a>
            <a href="#" class="sidebar-link">
                <span class="icon">📝</span>
                <span>Audit Log</span>
            </a>
            <a href="#" class="sidebar-link">
                <span class="icon">✉</span>
                <span>Notification Log</span>
            </a>
        </nav>

        <div class="px-5 py-5 border-t border-blue-900">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-full bg-white/10 flex items-center justify-center text-sm font-semibold text-white">
                    {{ strtoupper(substr(Auth::user()->name ?? 'OP', 0, 2)) }}
                </div>
                <div>
                    <p class="text-sm font-semibold text-white">{{ Auth::user()->name ?? 'System Administrator' }}</p>
                    <p class="text-xs text-[#9BB4D9]">Administrator</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button type="submit" class="text-sm text-[#EA6060] hover:text-red-300">Logout</button>
            </form>
        </div>
    </aside>

    <main class="ml-72 flex-1 p-6 bg-[#F4F6F9]">
        @yield('content')
    </main>

</body>
</html>