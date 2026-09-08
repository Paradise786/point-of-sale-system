<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Dashboard' }} - SmartPOS</title>

    <!-- Tailwind CSS & Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: {
                            50: '#ecfdf5',
                            100: '#d1fae5',
                            500: '#10b981',
                            600: '#059669',
                            700: '#047857',
                            900: '#064e3b',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased font-sans">
    <div class="min-h-screen flex flex-col md:flex-row">
        <!-- Sidebar Navigation -->
        <aside class="w-full md:w-64 bg-slate-900 text-slate-200 flex-shrink-0 flex flex-col no-print shadow-xl">
            <!-- Brand -->
            <div class="h-16 flex items-center justify-between px-6 bg-slate-950 border-b border-slate-800">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-emerald-500 flex items-center justify-center text-white font-black shadow-lg shadow-emerald-500/30">
                        <i class="fa-solid fa-cash-register text-lg"></i>
                    </div>
                    <div>
                        <span class="text-lg font-black tracking-wider text-white">Smart<span class="text-emerald-400">POS</span></span>
                        <span class="block text-[10px] text-slate-400 -mt-1 font-medium">Retail & Inventory</span>
                    </div>
                </a>
            </div>

            <!-- POS Terminal Quick Launch -->
            <div class="p-4 border-b border-slate-800/80">
                <a href="{{ route('pos.index') }}" 
                   class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/25 transition duration-200 group">
                    <i class="fa-solid fa-cart-shopping text-base group-hover:scale-110 transition-transform"></i>
                    <span>Open POS Terminal</span>
                </a>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mb-2">Main Menu</p>
                
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-chart-pie w-5 text-center text-slate-400 {{ request()->routeIs('dashboard') ? 'text-white' : '' }}"></i>
                    <span>Dashboard</span>
                </a>

                <a href="{{ route('stock.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('stock.*') ? 'bg-emerald-600 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-boxes-stacked w-5 text-center text-slate-400 {{ request()->routeIs('stock.*') ? 'text-white' : '' }}"></i>
                    <span>Stock Overview</span>
                </a>

                <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-6 mb-2">Inventory</p>

                <a href="{{ route('products.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('products.*') ? 'bg-emerald-600 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-box-open w-5 text-center text-slate-400 {{ request()->routeIs('products.*') ? 'text-white' : '' }}"></i>
                    <span>Products</span>
                </a>

                <a href="{{ route('categories.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('categories.*') ? 'bg-emerald-600 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-tags w-5 text-center text-slate-400 {{ request()->routeIs('categories.*') ? 'text-white' : '' }}"></i>
                    <span>Categories</span>
                </a>

                <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-6 mb-2">Transactions</p>

                <a href="{{ route('purchases.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('purchases.*') ? 'bg-emerald-600 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-bag-shopping w-5 text-center text-slate-400 {{ request()->routeIs('purchases.*') ? 'text-white' : '' }}"></i>
                    <span>Purchases (Stock In)</span>
                </a>

                <a href="{{ route('sales.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('sales.*') ? 'bg-emerald-600 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-receipt w-5 text-center text-slate-400 {{ request()->routeIs('sales.*') ? 'text-white' : '' }}"></i>
                    <span>Sales & Invoices</span>
                </a>

                <p class="px-3 text-[11px] font-bold uppercase tracking-wider text-slate-400 mt-6 mb-2">People</p>

                <a href="{{ route('customers.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('customers.*') ? 'bg-emerald-600 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-users w-5 text-center text-slate-400 {{ request()->routeIs('customers.*') ? 'text-white' : '' }}"></i>
                    <span>Customers</span>
                </a>

                <a href="{{ route('vendors.index') }}" 
                   class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition {{ request()->routeIs('vendors.*') ? 'bg-emerald-600 text-white shadow' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    <i class="fa-solid fa-truck-moving w-5 text-center text-slate-400 {{ request()->routeIs('vendors.*') ? 'text-white' : '' }}"></i>
                    <span>Vendors</span>
                </a>
            </nav>

            <!-- Footer / System status -->
            <div class="p-4 border-t border-slate-800 text-xs text-slate-400">
                <div class="flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    <span class="font-medium">System Online</span>
                </div>
                <p class="text-[11px] text-slate-500 mt-1">SmartPOS v1.0 • Laravel 12</p>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
            <!-- Top Header -->
            <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 no-print shadow-sm">
                <div class="flex items-center gap-4">
                    <h1 class="text-xl font-bold text-slate-800">{{ $title ?? 'Dashboard' }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <a href="{{ route('pos.index') }}" 
                       class="hidden sm:inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded-lg transition">
                        <i class="fa-solid fa-barcode"></i>
                        <span>POS Screen</span>
                    </a>
                    <div class="h-8 w-px bg-slate-200 hidden sm:block"></div>
                    <div class="text-xs text-slate-500">
                        <i class="fa-regular fa-calendar mr-1"></i>
                        {{ date('d M Y') }}
                    </div>
                </div>
            </header>

            <!-- Main Page Body -->
            <main class="flex-1 p-6 overflow-y-auto">
                <!-- Flash Alerts -->
                @if (session('success'))
                    <div class="mb-6 flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl shadow-sm no-print">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-lg"></i>
                        <span class="text-sm font-medium">{{ session('success') }}</span>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 flex items-center gap-3 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm no-print">
                        <i class="fa-solid fa-circle-exclamation text-rose-600 text-lg"></i>
                        <span class="text-sm font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm no-print">
                        <div class="flex items-center gap-2 font-semibold text-sm mb-2">
                            <i class="fa-solid fa-circle-exclamation text-rose-600"></i>
                            <span>Please correct the following errors:</span>
                        </div>
                        <ul class="list-disc list-inside text-xs space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot ?? '' }}
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
