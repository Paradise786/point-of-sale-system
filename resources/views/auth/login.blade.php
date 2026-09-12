<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - SmartPOS Retail & Inventory</title>

    <!-- Tailwind CSS & Font Awesome -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script>
        if (typeof tailwind !== 'undefined') {
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
        }
    </script>
</head>
<body class="h-full flex items-center justify-center p-4 bg-slate-950 antialiased selection:bg-emerald-500 selection:text-white">
    <div class="w-full max-w-md">
        <!-- Logo & Header -->
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-gradient-to-tr from-emerald-600 to-teal-400 text-white shadow-xl shadow-emerald-500/20 mb-4 ring-8 ring-emerald-500/10">
                <i class="fa-solid fa-cash-register text-2xl"></i>
            </div>
            <h1 class="text-3xl font-black tracking-tight text-white">Smart<span class="text-emerald-400">POS</span></h1>
            <p class="text-sm font-medium text-slate-400 mt-1">Point of Sale & Inventory Management System</p>
        </div>

        <!-- Login Card -->
        <div class="bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl p-8 backdrop-blur">
            <div class="mb-6">
                <h2 class="text-xl font-bold text-white">Sign In to Your Account</h2>
                <p class="text-xs text-slate-400 mt-1">Enter your credentials to access the POS terminal and backoffice</p>
            </div>

            <!-- Flash Alerts -->
            @if(session('info'))
                <div class="mb-4 p-3 bg-sky-950/80 border border-sky-800 text-sky-300 rounded-xl text-xs flex items-center gap-2">
                    <i class="fa-solid fa-circle-info text-sky-400"></i>
                    <span>{{ session('info') }}</span>
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 p-3.5 bg-rose-950/80 border border-rose-800/80 text-rose-300 rounded-xl text-xs flex items-start gap-2.5">
                    <i class="fa-solid fa-circle-exclamation text-rose-400 mt-0.5 text-sm shrink-0"></i>
                    <div>
                        <span class="font-semibold block mb-0.5">Authentication Failed</span>
                        {{ $errors->first() }}
                    </div>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Email Address</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-regular fa-envelope text-sm"></i>
                        </div>
                        <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                               class="w-full pl-10 pr-4 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                               placeholder="admin@smartpos.com">
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider mb-1.5">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-500">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <input type="password" name="password" id="password" required
                               class="w-full pl-10 pr-10 py-2.5 bg-slate-950 border border-slate-700 rounded-xl text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition"
                               placeholder="••••••••">
                        <button type="button" onclick="togglePasswordVisibility()" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-500 hover:text-slate-300">
                            <i id="password-toggle-icon" class="fa-regular fa-eye text-sm"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between text-xs pt-1">
                    <label class="flex items-center gap-2 cursor-pointer text-slate-300 select-none">
                        <input type="checkbox" name="remember" class="w-4 h-4 rounded bg-slate-950 border-slate-700 text-emerald-500 focus:ring-emerald-500 focus:ring-offset-slate-900">
                        <span>Remember me on this device</span>
                    </label>
                </div>

                <button type="submit" 
                        class="w-full flex items-center justify-center gap-2 py-3 px-4 bg-gradient-to-r from-emerald-500 to-teal-600 hover:from-emerald-600 hover:to-teal-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-500/25 transition duration-200 mt-2">
                    <i class="fa-solid fa-right-to-bracket text-sm"></i>
                    <span>Sign In</span>
                </button>
            </form>

            <!-- Quick Demo Credentials for Fast Testing -->
            <div class="mt-6 pt-5 border-t border-slate-800">
                <p class="text-[11px] uppercase tracking-wider font-bold text-slate-400 mb-2.5 flex items-center gap-1.5">
                    <i class="fa-solid fa-bolt text-amber-400"></i> Quick Fill Demo Accounts
                </p>
                <div class="grid grid-cols-2 gap-2">
                    <button type="button" onclick="fillCredentials('admin@smartpos.com', 'password123')"
                            class="text-left p-2.5 bg-slate-950 hover:bg-slate-800/80 border border-slate-800 hover:border-emerald-500/50 rounded-xl transition group">
                        <div class="text-xs font-bold text-white group-hover:text-emerald-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            Super Admin
                        </div>
                        <div class="text-[10px] text-slate-400 font-mono mt-0.5">Full System Access</div>
                    </button>

                    <button type="button" onclick="fillCredentials('cashier@smartpos.com', 'password123')"
                            class="text-left p-2.5 bg-slate-950 hover:bg-slate-800/80 border border-slate-800 hover:border-teal-500/50 rounded-xl transition group">
                        <div class="text-xs font-bold text-white group-hover:text-teal-400 flex items-center gap-1.5">
                            <span class="w-2 h-2 rounded-full bg-teal-500"></span>
                            Cashier
                        </div>
                        <div class="text-[10px] text-slate-400 font-mono mt-0.5">POS & Sales Only</div>
                    </button>
                </div>
            </div>
        </div>

        <div class="text-center mt-6 text-xs text-slate-500">
            SmartPOS &copy; {{ date('Y') }} • All rights reserved
        </div>
    </div>

    <script>
        function fillCredentials(email, password) {
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
        }

        function togglePasswordVisibility() {
            const pwdInput = document.getElementById('password');
            const icon = document.getElementById('password-toggle-icon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                pwdInput.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
