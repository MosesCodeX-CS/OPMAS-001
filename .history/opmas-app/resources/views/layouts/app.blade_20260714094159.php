<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OPMAS — @yield('title', 'Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-950 text-gray-100 min-h-screen flex">

    <aside class="w-56 bg-gray-900 border-r border-gray-800 flex flex-col min-h-screen fixed">
        <div class="px-5 py-5 border-b border-gray-800">
            <p class="text-xs text-gray-500 font-mono">OPMAS-001</p>
            <p class="text-base font-semibold text-white mt-1">OX-PLANT-01</p>
        </div>
        <nav class="flex-1 px-3 py-4 space-y-1">
            <a href="{{ route('dashboard') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                Dashboard
            </a>
            <a href="{{ route('alarms') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('alarms') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                Alarms
            </a>
            <a href="{{ route('reports') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('reports') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                Reports
            </a>
            <a href="{{ route('equipment') }}"
               class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm {{ request()->routeIs('equipment') ? 'bg-blue-600 text-white' : 'text-gray-400 hover:bg-gray-800 hover:text-white' }}">
                Equipment
            </a>
        </nav>
        <div class="px-5 py-4 border-t border-gray-800 text-xs text-gray-500">
            {{ Auth::user()->name ?? 'Operator' }}
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="block text-red-400 hover:text-red-300 mt-1">Logout</button>
            </form>
        </div>
    </aside>

    <main class="ml-56 flex-1 p-6">
        @yield('content')
    </main>

</body>
</html>