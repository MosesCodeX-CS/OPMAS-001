<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>OPMAS — @yield('title', 'Dashboard')</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Lucide Icons CDN -->
    <script src="https://unpkg.com/lucide@latest"></script>
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
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--kijabe-bg);
            color: var(--kijabe-text);
        }
        .kijabe-card {
            background-color: var(--kijabe-card);
            border: 1px solid var(--kijabe-border);
            border-radius: 1rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.05), 0 1px 2px 0 rgba(0, 0, 0, 0.03);
        }
        .kijabe-input {
            background-color: #ffffff;
            border: 1px solid var(--kijabe-border);
            color: var(--kijabe-text);
            transition: all 0.2s ease-in-out;
        }
        .kijabe-input:focus {
            border-color: var(--kijabe-blue);
            box-shadow: 0 0 0 2px rgba(43, 138, 198, 0.2);
            outline: none;
        }
        .sidebar-link-active {
            background-color: rgba(255, 255, 255, 0.1);
            color: #ffffff;
            padding-left: 1.25rem;
        }
        .role-badge-system_admin {
            background-color: #DC2626;
        }
        .role-badge-admin {
            background-color: #D97706;
        }
        .role-badge-user {
            background-color: #059669;
        }
    </style>
</head>
<body class="min-h-screen text-gray-800 flex">

    <!-- Sidebar -->
    <aside class="w-72 flex flex-col min-h-screen fixed shadow-lg bg-[#19409A] text-white z-30">
        <!-- Top Accent Green Line -->
        <div class="h-1 bg-[#198754] w-full"></div>

        <!-- Full width white logo header -->
        <div class="bg-white py-4 px-6 flex justify-center items-center border-b border-gray-100">
            <img src="{{ asset('images/Kijabe-logo.svg') }}" alt="AIC Kijabe Hospital" class="w-full h-auto object-contain" />
        </div>

        <!-- Navigation Links -->
        <nav class="flex-1 px-4 py-6 space-y-1.5 overflow-y-auto">
            <span class="px-3 text-[10px] font-bold text-[#99B7E3] uppercase tracking-widest block mb-2">Main Menu</span>
            
            <!-- Dashboard -->
            <a href="{{ route('dashboard') }}"
               class="relative flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium transition-all group
               {{ request()->routeIs('dashboard') ? 'sidebar-link-active' : 'text-[#99B7E3] hover:text-white hover:bg-white/5' }}">
                @if(request()->routeIs('dashboard'))
                    <span class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#00D2FF]"></span>
                @endif
                <i data-lucide="layout-dashboard" class="w-5 h-5 flex-shrink-0"></i>
                <span>Dashboard</span>
            </a>

            <!-- Alarms -->
            <a href="{{ route('alarms') }}"
               class="relative flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium transition-all group
               {{ request()->routeIs('alarms') ? 'sidebar-link-active' : 'text-[#99B7E3] hover:text-white hover:bg-white/5' }}">
                @if(request()->routeIs('alarms'))
                    <span class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#00D2FF]"></span>
                @endif
                <i data-lucide="bell" class="w-5 h-5 flex-shrink-0"></i>
                <span>Alarms</span>
            </a>

            <!-- Reports -->
            <a href="{{ route('reports') }}"
               class="relative flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium transition-all group
               {{ request()->routeIs('reports') ? 'sidebar-link-active' : 'text-[#99B7E3] hover:text-white hover:bg-white/5' }}">
                @if(request()->routeIs('reports'))
                    <span class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#00D2FF]"></span>
                @endif
                <i data-lucide="trending-up" class="w-5 h-5 flex-shrink-0"></i>
                <span>Reports</span>
            </a>

            <!-- Equipment -->
            <a href="{{ route('equipment') }}"
               class="relative flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium transition-all group
               {{ request()->routeIs('equipment') ? 'sidebar-link-active' : 'text-[#99B7E3] hover:text-white hover:bg-white/5' }}">
                @if(request()->routeIs('equipment'))
                    <span class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#00D2FF]"></span>
                @endif
                <i data-lucide="cpu" class="w-5 h-5 flex-shrink-0"></i>
                <span>Equipment</span>
            </a>

            @if(auth()->user()->isSystemAdmin())
                <div class="pt-6">
                    <span class="px-3 text-[10px] font-bold text-[#99B7E3] uppercase tracking-widest block mb-2">Management</span>
                </div>

                <!-- User Management -->
                <a href="{{ route('users.index') }}"
                   class="relative flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium transition-all group
                   {{ request()->routeIs('users.index') ? 'sidebar-link-active' : 'text-[#99B7E3] hover:text-white hover:bg-white/5' }}">
                    @if(request()->routeIs('users.index'))
                        <span class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#00D2FF]"></span>
                    @endif
                    <i data-lucide="users" class="w-5 h-5 flex-shrink-0"></i>
                    <span>User Accounts</span>
                </a>

                <!-- Settings -->
                <a href="{{ route('settings.index') }}"
                   class="relative flex items-center gap-3.5 px-4 py-3 rounded-lg text-sm font-medium transition-all group
                   {{ request()->routeIs('settings.index') ? 'sidebar-link-active' : 'text-[#99B7E3] hover:text-white hover:bg-white/5' }}">
                    @if(request()->routeIs('settings.index'))
                        <span class="absolute left-0 top-0 bottom-0 w-1.5 bg-[#00D2FF]"></span>
                    @endif
                    <i data-lucide="sliders" class="w-5 h-5 flex-shrink-0"></i>
                    <span>System Settings</span>
                </a>
            @endif
        </nav>

        <!-- Bottom User Panel -->
        <div class="px-5 py-5 border-t border-[#224AAB] bg-[#143486] text-xs">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-9 h-9 rounded-full bg-white/10 flex items-center justify-center font-bold text-white shadow-sm border border-white/10">
                    {{ substr(auth()->user()->name, 0, 1) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-white truncate leading-none mb-1.5">{{ auth()->user()->name }}</p>
                    <span class="text-[8px] font-extrabold uppercase px-2 py-0.5 rounded text-white tracking-widest inline-block
                        {{ auth()->user()->isSystemAdmin() ? 'role-badge-system_admin' : (auth()->user()->isAdmin() ? 'role-badge-admin' : 'role-badge-user') }}">
                        {{ str_replace('_', ' ', auth()->user()->role) }}
                    </span>
                </div>
            </div>
            
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex items-center gap-2 text-xs text-[#99B7E3] hover:text-white transition-colors w-full text-left">
                    <i data-lucide="log-out" class="w-4 h-4 text-red-400 hover:text-red-300"></i>
                    <span class="text-red-400 hover:text-red-300 font-semibold">Logout</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="ml-72 flex-1 p-8 min-h-screen">
        @if(session('status'))
            <div class="mb-6 rounded-xl border px-5 py-4 text-sm text-[#0F5132] bg-[#D1E7DD] border-[#BADBCC] flex items-center gap-3 animate-fade-in" role="alert">
                <i data-lucide="check-circle" class="w-5 h-5 text-[#15803D] flex-shrink-0"></i>
                <div>{{ session('status') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 rounded-xl border px-5 py-4 text-sm text-[#842029] bg-[#F8D7DA] border-[#F5C2C7] flex items-center gap-3 animate-fade-in" role="alert">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-[#B91C1C] flex-shrink-0"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>