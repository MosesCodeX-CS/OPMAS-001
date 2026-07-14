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

    <aside class="w-72 flex flex-col min-h-screen fixed shadow-lg bg-[#19409A] text-white">
        <!-- Accent Green Line -->
        <div class="h-1 bg-[#198754] w-full"></div>

        <!-- Full width white logo header -->
        <div class="bg-white py-3 px-4 flex justify-center items-center border-b border-gray-100">
            <img src="{{ asset('images/Kijabe-logo.svg') }}" alt="AIC Kijabe Hospital" class="w-full h-auto object-contain" />
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
               class="relative flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium transition-all overflow-hidden
               {{ request()->routeIs('dashboard')
                    ? 'bg-white/10 text-white pl-5'
                    : 'text-[#99B7E3] hover:text-white hover:bg-white/5' }}">
                @if(request()->routeIs('dashboard'))
                    <span class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#00D2FF]"></span>
                @endif
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                </svg>
                <span>Dashboard</span>
            </a>

            <!-- Alarms -->
            <a href="{{ route('alarms') }}"
               class="relative flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium transition-all overflow-hidden
               {{ request()->routeIs('alarms')
                    ? 'bg-white/10 text-white pl-5'
                    : 'text-[#99B7E3] hover:text-white hover:bg-white/5' }}">
                @if(request()->routeIs('alarms'))
                    <span class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#00D2FF]"></span>
                @endif
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <span>Alarms</span>
            </a>

            <!-- Reports -->
            <a href="{{ route('reports') }}"
               class="relative flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium transition-all overflow-hidden
               {{ request()->routeIs('reports')
                    ? 'bg-white/10 text-white pl-5'
                    : 'text-[#99B7E3] hover:text-white hover:bg-white/5' }}">
                @if(request()->routeIs('reports'))
                    <span class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#00D2FF]"></span>
                @endif
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 12l3-3 3 3 4-4M8 21h12a2 2 0 002-2V7a2 2 0 00-2-2H8a2 2 0 00-2 2v12a2 2 0 002 2z" />
                </svg>
                <span>Reports</span>
            </a>

            <!-- Equipment -->
            <a href="{{ route('equipment') }}"
               class="relative flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium transition-all overflow-hidden
               {{ request()->routeIs('equipment')
                    ? 'bg-white/10 text-white pl-5'
                    : 'text-[#99B7E3] hover:text-white hover:bg-white/5' }}">
                @if(request()->routeIs('equipment'))
                    <span class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#00D2FF]"></span>
                @endif
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <span>Equipment</span>
            </a>
        </nav>

        <!-- Bottom Panel -->
        <div class="px-5 py-5 border-t border-[#224AAB] bg-[#143486] text-xs">
            <div class="flex items-center gap-2 mb-3 text-[#99B7E3]">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                <span class="font-medium text-white">{{ Auth::user()->name ?? 'Operator' }}</span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 text-xs text-[#99B7E3] hover:text-white transition-colors w-full text-left">
                    <svg class="w-4 h-4 text-red-400 hover:text-red-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    <span class="text-red-400 hover:text-red-300">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <main class="ml-72 flex-1 p-6" style="background-color:#F4F6F9;">
        @if(session('status'))
            <div class="mb-6 rounded-xl border px-4 py-3 text-sm text-[#0F5132] bg-[#D1E7DD] border-[#BADBCC]" role="alert">
                {{ session('status') }}
            </div>
        @endif

        @yield('content')
    </main>

</body>
</html>