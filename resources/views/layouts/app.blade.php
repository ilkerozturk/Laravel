<!doctype html>
<html lang="tr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BT Places — @yield('title', 'CRM')</title>

    {{-- Tailwind CSS CDN --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                borderRadius: {
                    none: '0', sm: '0', DEFAULT: '0', md: '0', lg: '0', xl: '0', '2xl': '0', '3xl': '0',
                    full: '9999px'
                },
                extend: {
                    colors: {
                        sidebar: { DEFAULT: '#0f172a', hover: '#1e293b', active: '#334155' },
                        brand: { 50: '#eef2ff', 100: '#e0e7ff', 200: '#c7d2fe', 400: '#818cf8', 500: '#6366f1', 600: '#4f46e5', 700: '#4338ca' }
                    },
                    fontFamily: { sans: ['Inter', 'system-ui', 'sans-serif'] }
                }
            }
        }
    </script>

    {{-- Inter Font --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Lucide Icons --}}
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>

    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>

    <style>
        [x-cloak] { display: none !important; }

        /* Smooth transitions */
        * { transition-property: color, background-color, border-color, box-shadow, opacity, transform;
            transition-timing-function: cubic-bezier(.4,0,.2,1);
            transition-duration: 150ms; }
        table * { transition: none; }

        /* Custom scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Sidebar link hover effect */
        .nav-link { position: relative; overflow: hidden; }
        .nav-link::before {
            content: '';
            position: absolute; left: 0; top: 0; bottom: 0; width: 4px;
            background: #6366f1; transform: scaleY(0);
            transition: transform .2s ease;
        }
        .nav-link:hover::before, .nav-link.active::before { transform: scaleY(1); }

        /* Table row hover + striping */
        tbody tr { transition: background .15s ease; }
        tbody tr:hover { background: #f8fafc; }
        tbody tr:nth-child(even) { background: #fafbfc; }
        tbody tr:nth-child(even):hover { background: #f1f5f9; }

        /* Button hover lift */
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(99,102,241,.30); }

        /* Card accent & hover */
        .stat-card { border-left: 4px solid #4f46e5; }
        .stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 24px -4px rgba(0,0,0,.12); }

        /* Pulse animation for badges */
        .badge-pulse { animation: pulse-ring 2s ease infinite; }
        @keyframes pulse-ring {
            0% { box-shadow: 0 0 0 0 rgba(99,102,241,.4); }
            70% { box-shadow: 0 0 0 6px rgba(99,102,241,0); }
            100% { box-shadow: 0 0 0 0 rgba(99,102,241,0); }
        }

        /* Mobile sidebar */
        @media (max-width: 1023px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.open { transform: translateX(0); }
        }
    </style>
</head>
<body class="h-full bg-gray-50 font-sans text-gray-800 antialiased">

{{-- Mobile overlay --}}
<div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden" onclick="toggleSidebar()"></div>

{{-- ============ SIDEBAR ============ --}}
<aside id="sidebar" class="sidebar fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-gradient-to-b from-sidebar to-[#131c33] text-white lg:translate-x-0">
    {{-- Logo --}}
    @php
        $logoUrl = \App\Models\AppSetting::getAssetUrl('logo_url');
        $logoHeight = \App\Models\AppSetting::getValue('logo_height', '40');
    @endphp
    <div class="flex h-16 items-center justify-center border-b border-white/10 px-5">
        @if($logoUrl)
            <img src="{{ $logoUrl }}" alt="Logo" style="height: {{ $logoHeight }}px;" class="max-w-full object-contain">
        @else
            <span class="text-lg font-bold tracking-tight text-white">BT Places</span>
        @endif
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        <a href="{{ route('dashboard') }}"
           class="nav-link flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('dashboard') ? 'bg-sidebar-active text-white active' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
            <i data-lucide="layout-dashboard" class="h-5 w-5 shrink-0"></i>
            Dashboard
        </a>
        <a href="{{ route('companies.index') }}"
           class="nav-link flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('companies.*') ? 'bg-sidebar-active text-white active' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
            <i data-lucide="building-2" class="h-5 w-5 shrink-0"></i>
            Firmalar
        </a>
        <a href="{{ route('companies.import-logs') }}"
           class="nav-link flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('companies.import-logs') ? 'bg-sidebar-active text-white active' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
            <i data-lucide="download-cloud" class="h-5 w-5 shrink-0"></i>
            Import Logları
        </a>
        <a href="{{ route('leads.index') }}"
           class="nav-link flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('leads.*') ? 'bg-sidebar-active text-white active' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
            <i data-lucide="users" class="h-5 w-5 shrink-0"></i>
            Leadler
        </a>
        <a href="{{ route('follow-ups.index') }}"
           class="nav-link flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('follow-ups.*') ? 'bg-sidebar-active text-white active' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
            <i data-lucide="phone-call" class="h-5 w-5 shrink-0"></i>
            Takipler
        </a>
        <a href="{{ route('reports.index') }}"
           class="nav-link flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('reports.*') ? 'bg-sidebar-active text-white active' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
            <i data-lucide="bar-chart-3" class="h-5 w-5 shrink-0"></i>
            Raporlar
        </a>

        <div class="my-3 border-t border-white/10"></div>

        <a href="{{ route('settings.index') }}"
           class="nav-link flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium {{ request()->routeIs('settings.*') ? 'bg-sidebar-active text-white active' : 'text-slate-300 hover:bg-sidebar-hover hover:text-white' }}">
            <i data-lucide="settings" class="h-5 w-5 shrink-0"></i>
            Ayarlar
        </a>
    </nav>

    {{-- Footer --}}
    <div class="border-t border-white/10 px-5 py-3">
        <p class="text-xs text-slate-400">&copy; {{ date('Y') }} BT Places CRM</p>
    </div>
</aside>

{{-- ============ MAIN ============ --}}
<div class="lg:pl-64">
    {{-- Top Bar --}}
    <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-gray-200 bg-white/90 px-4 shadow-sm backdrop-blur-lg sm:px-6">
        <button onclick="toggleSidebar()" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 lg:hidden">
            <i data-lucide="menu" class="h-5 w-5"></i>
        </button>
        <h1 class="text-lg font-semibold text-gray-800">@yield('page-title', 'Dashboard')</h1>
        <div class="ml-auto flex items-center gap-3">
            <span class="hidden text-sm text-gray-500 sm:inline">{{ now()->format('d M Y, H:i') }}</span>
            @auth
                <div class="hidden items-center gap-2 border border-gray-200 bg-white px-3 py-1.5 sm:flex">
                    <div class="flex h-7 w-7 items-center justify-center rounded-full bg-brand-600 text-xs font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</div>
                    <span class="text-sm font-medium text-gray-700">{{ auth()->user()->name }}</span>
                    <span class="rounded-full bg-brand-50 px-2 py-0.5 text-[10px] font-semibold uppercase text-brand-600">{{ auth()->user()->role }}</span>
                </div>
                <form method="post" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-1.5 text-sm font-medium text-gray-600 hover:bg-gray-50 hover:text-gray-800">
                        <i data-lucide="log-out" class="h-4 w-4"></i>
                        Çıkış
                    </button>
                </form>
            @endauth
        </div>
    </header>

    {{-- Content --}}
    <main class="p-4 sm:p-6 lg:p-8">
        {{-- Flash messages --}}
        @if (session('status'))
            <div class="mb-6 flex items-center gap-3 border-l-4 border-l-green-500 border border-green-200 bg-green-50 px-5 py-3 text-sm text-green-800 shadow-sm">
                <i data-lucide="check-circle-2" class="h-5 w-5 shrink-0 text-green-500"></i>
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-6 border-l-4 border-l-red-500 border border-red-200 bg-red-50 px-5 py-3 text-sm text-red-800 shadow-sm">
                <div class="flex items-center gap-3">
                    <i data-lucide="alert-circle" class="h-5 w-5 shrink-0 text-red-500"></i>
                    <strong>Hata oluştu:</strong>
                </div>
                <ul class="mt-2 ml-8 list-disc space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script>
    // Initialize Lucide icons
    lucide.createIcons();

    // Mobile sidebar toggle
    function toggleSidebar() {
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        sidebar.classList.toggle('open');
        overlay.classList.toggle('hidden');
    }
</script>

@yield('scripts')
</body>
</html>
