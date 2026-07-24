<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
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
        html {
            font-size: 18px;
        }
        html, body {
            overflow-x: hidden;
        }
        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--kijabe-bg);
            color: var(--kijabe-text);
            font-size: 1.125rem;
            line-height: 1.5;
            min-height: 100vh;
        }
        .text-\[6\.5px\] {
            font-size: 0.39rem;
        }
        .text-\[8px\] {
            font-size: 0.48rem;
        }
        .text-\[9px\] {
            font-size: 0.55rem;
        }
        .text-\[10px\] {
            font-size: 0.62rem;
        }
        .text-\[11px\] {
            font-size: 0.68rem;
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
        @media (max-width: 1024px) {
            #sidebar {
                width: 18rem;
                max-width: calc(100vw - 2rem);
            }
            header {
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .sidebar-link-active {
                padding-left: 1rem;
            }
            .text-xs {
                font-size: 0.78rem;
            }
            .p-8 {
                padding: 1.5rem;
            }
        }
        @media (max-width: 640px) {
            #sidebar {
                width: 100%;
                max-width: 100%;
            }
            #sidebar-overlay {
                display: block;
            }
            header {
                height: auto;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }
            .text-xs {
                font-size: 0.72rem;
            }
            .p-8 {
                padding: 1rem;
            }
            .h-16 {
                height: 3.5rem;
            }
            .w-72 {
                width: 100%;
                max-width: 100%;
            }
        }
    </style>
</head>
<body class="min-h-screen text-gray-800 flex">

    <!-- Mobile Sidebar Overlay -->
    <div id="sidebar-overlay" class="fixed inset-0 z-30 hidden bg-black/40 md:hidden" onclick="closeMobileSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="fixed inset-y-0 left-0 z-40 w-72 flex flex-col overflow-y-auto bg-[#19409A] text-white shadow-lg transition-transform duration-200 -translate-x-full md:translate-x-0">

        <!-- Full width white logo header -->
        <div class="bg-white py-4 px-4 flex items-center justify-between border-b border-gray-100">
            <div class="flex-1 pr-2">
                <img src="{{ asset('images/Kijabe-logo.png') }}" alt="AIC Kijabe Hospital" class="w-full h-auto object-contain" />
            </div>
            <button type="button" onclick="closeMobileSidebar()" class="md:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
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
    <main class="ml-0 md:ml-72 flex-1 min-h-screen flex flex-col min-w-0 overflow-x-hidden">
        <!-- Top Header Bar -->
        <header class="bg-white border-b border-gray-200 h-16 px-8 flex items-center justify-between z-10 shadow-sm flex-shrink-0">
            <div class="flex items-center gap-4">
                <button type="button" onclick="openMobileSidebar()" class="md:hidden inline-flex h-10 w-10 items-center justify-center rounded-xl bg-slate-100 text-slate-700 hover:bg-slate-200 transition-colors">
                    <i data-lucide="menu" class="w-5 h-5"></i>
                </button>
                <div class="flex items-center gap-2">
                    <i data-lucide="activity" class="w-5 h-5 text-[#2B8AC6]"></i>
                    <span class="text-xs font-bold text-[#6B7A90] uppercase tracking-wider">AIC Kijabe Hospital Oxygen Plant Registry</span>
                </div>
                
                <!-- Live Plant Health Status Pill -->
                <div id="header-health-status" class="hidden md:flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition-colors duration-300 bg-emerald-50 text-emerald-700 border border-emerald-200">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse"></span>
                    <span>Plant Health: <span id="header-health-score">100%</span></span>
                </div>
            </div>
            
            <div class="flex items-center gap-6">
                <!-- Notifications Bell -->
                <div class="relative" id="notification-bell-container">
                    <button onclick="toggleNotifications()" class="p-2 rounded-full hover:bg-gray-100 transition-colors relative text-gray-500 hover:text-gray-700 focus:outline-none">
                        <i data-lucide="bell" class="w-5 h-5"></i>
                        <!-- Red dot badge if active alarms exist -->
                        <span id="bell-badge" class="absolute top-2.5 right-2.5 w-2 h-2 rounded-full bg-red-600 border border-white hidden animate-pulse"></span>
                    </button>
                    
                    <!-- Notifications Dropdown Popover -->
                    <div id="notifications-dropdown" class="absolute right-0 mt-3 bg-white border border-gray-200 rounded-xl shadow-xl w-80 hidden z-50 overflow-hidden">
                        <div class="p-4 border-b border-gray-100 bg-gray-50/50 flex items-center justify-between">
                            <span class="font-bold text-xs uppercase tracking-wider text-[#1B3A6B]">Active Alerts</span>
                            <a href="{{ route('alarms') }}" class="text-[10px] font-bold text-[#2B8AC6] hover:underline uppercase">View All</a>
                        </div>
                        <div id="notifications-list" class="max-h-60 overflow-y-auto divide-y divide-gray-100 text-xs text-[#1A2A3A]">
                            <p class="p-4 text-center text-gray-400 italic">Checking alerts...</p>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Inner Content with padding -->
        <div class="p-4 sm:p-6 lg:p-8 flex-1 min-w-0">
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
        </div>
    </main>

    <script>
        function toggleNotifications() {
            const dropdown = document.getElementById('notifications-dropdown');
            dropdown.classList.toggle('hidden');
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (e) => {
            const container = document.getElementById('notification-bell-container');
            const dropdown = document.getElementById('notifications-dropdown');
            if (container && !container.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Global state variable
        window.latestSystemStatus = null;

        // Fetch active system status (telemetry + alarms)
        async function fetchSystemStatus() {
            try {
                console.debug('Fetching system status from {{ route('api.system-status') }}');
                const response = await fetch('{{ route('api.system-status') }}', { credentials: 'same-origin' });
                if (!response.ok) {
                    console.warn('System status fetch failed with status', response.status, response.statusText);
                    return;
                }
                const data = await response.json();
                console.debug('System status fetched', data);
                
                window.latestSystemStatus = data;
                
                // Dispatch event for local page listening
                window.dispatchEvent(new CustomEvent('system-status-updated', { detail: data }));
                
                // Update Notifications
                updateHeaderNotifications(data.active_alarms);
                
                // Update Health status pill
                if (data.reading) {
                    updateHeaderHealth(data.reading, data.reading_age);
                }
            } catch (err) {
                console.error('Error fetching system status:', err);
            }
        }

        function updateHeaderNotifications(alarms) {
            const badge = document.getElementById('bell-badge');
            const list = document.getElementById('notifications-list');
            
            if (alarms.length > 0) {
                badge.classList.remove('hidden');
                let html = '';
                alarms.forEach(alarm => {
                    const dateStr = new Date(alarm.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    const severityColor = alarm.severity === 'CRITICAL' ? 'text-red-600' : 'text-amber-600';
                    html += `
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold ${severityColor} uppercase tracking-wider text-[10px]">${alarm.severity}</span>
                                <span class="text-gray-400 text-[10px]">${dateStr}</span>
                            </div>
                            <p class="text-gray-700 leading-snug">${alarm.message}</p>
                        </div>
                    `;
                });
                list.innerHTML = html;
            } else {
                badge.classList.add('hidden');
                list.innerHTML = `
                    <div class="p-6 text-center text-gray-400 italic">
                        All systems operating normally.
                    </div>
                `;
            }
        }

        function openMobileSidebar() {
            document.getElementById('sidebar').classList.remove('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.remove('hidden');
        }

        function closeMobileSidebar() {
            document.getElementById('sidebar').classList.add('-translate-x-full');
            document.getElementById('sidebar-overlay').classList.add('hidden');
        }

        function updateHeaderHealth(data, age) {
            const pill = document.getElementById('header-health-status');
            const scoreSpan = document.getElementById('header-health-score');
            
            pill.classList.remove('hidden');

            // If reading_age is greater than 15 seconds, collector is offline
            if (age !== null && age > 15) {
                scoreSpan.textContent = "OFFLINE";
                pill.className = "hidden md:flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-red-50 text-red-700 border border-red-200 animate-bounce";
                pill.querySelector('span').className = "w-1.5 h-1.5 rounded-full bg-red-600 animate-ping";
                return;
            }

            let score = 100;
            if (data.compressor_status === 2) score -= 50;
            else if (data.compressor_status === 0) score -= 20;
            
            if (data.pressure < 4.0) score -= 30;
            else if (data.pressure < 4.8) score -= 10;
            
            if (data.purity < 90.0) score -= 40;
            else if (data.purity < 92.5) score -= 15;
            
            if (data.temperature >= 80.0) score -= 25;
            else if (data.temperature >= 55.0) score -= 10;
            
            if (data.tank_level < 15.0) score -= 15;
            
            score = Math.max(0, score);
            
            scoreSpan.textContent = score + '%';
            
            // Set styles based on score
            if (score >= 80) {
                pill.className = "hidden md:flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200";
                pill.querySelector('span').className = "w-1.5 h-1.5 rounded-full bg-emerald-600 animate-pulse";
            } else if (score >= 50) {
                pill.className = "hidden md:flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200";
                pill.querySelector('span').className = "w-1.5 h-1.5 rounded-full bg-amber-600 animate-pulse";
            } else {
                pill.className = "hidden md:flex items-center gap-1.5 px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-red-50 text-red-700 border border-red-200 animate-bounce";
                pill.querySelector('span').className = "w-1.5 h-1.5 rounded-full bg-red-600 animate-ping";
            }
        }

        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
            fetchSystemStatus();
            setInterval(fetchSystemStatus, 5000);
        });
    </script>
</body>
</html>
