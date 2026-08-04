﻿<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'BusinessMate')</title>
    @php
        $manifestPath = public_path('build/manifest.json');
    @endphp

    @if (file_exists($manifestPath))
        @php $manifest = json_decode(file_get_contents($manifestPath), true); @endphp
        <link rel="stylesheet" href="{{ asset('build/' . $manifest['resources/css/app.css']['file']) }}">
        <script src="{{ asset('build/' . $manifest['resources/js/app.js']['file']) }}" defer></script>
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    <!-- Font Awesome untuk ikon -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-slate-100 font-sans">

    <!-- ========================================== -->
    <!-- MULAI KONDISI: HANYA UNTUK USER YANG LOGIN  -->
    <!-- ========================================== -->
    @auth
    @php
        $currentUser = Auth::user();
        $userName = trim($currentUser->name ?: 'User');

        $businessLabel = $currentUser->business_name
            ?: $currentUser->email;
    @endphp

    <div class="flex h-screen overflow-hidden">
        
        <!-- SIDEBAR KIRI -->
        <aside class="hidden w-72 flex-shrink-0 flex-col border-r border-slate-200 bg-white p-6 md:flex">
            <div>
                <!-- Logo Aplikasi UsahaMate -->
                @php
                    $applicationLogoPath = 'images/usahamate-logo.png';
                    $applicationLogoExists = file_exists(
                        public_path($applicationLogoPath)
                    );
                @endphp

                <div class="flex items-center gap-3 mb-10">
                    <div class="w-11 h-11 flex-shrink-0 overflow-hidden rounded-full border border-blue-100 bg-white shadow-sm">
                        @if ($applicationLogoExists)
                            <img
                                src="{{ asset($applicationLogoPath) }}"
                                alt="Logo aplikasi BusinessMate"
                                class="w-full h-full object-cover"
                            >
                        @else
                            <div class="w-full h-full bg-blue-600 flex items-center justify-center text-white font-bold">
                                UM
                            </div>
                        @endif
                    </div>

                    <div>
                        <h1 class="font-bold text-lg leading-tight text-blue-600">BusinessMate</h1>
                        <span class="text-[10px] text-gray-500">Businessmate V1.0</span>
                    </div>
                </div>

                <!-- Menu Navigasi -->
                <nav class="space-y-1.5">
                    <!-- DASHBOARD -->
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 {{ request()->routeIs('dashboard') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </a>

                    <!-- INVENTORY -->
                    <a href="{{ route('inventory') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 {{ request()->routeIs('inventory*') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                        Inventory
                    </a>

                    <!-- ANALYTICS -->
                    <a href="{{ route('analytics') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 {{ request()->routeIs('analytics') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                        Analytics
                    </a>

                    <!-- AI COPILOT -->
                    <a href="{{ route('ai.copilot') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 {{ request()->routeIs('ai.copilot') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
                        AI Copilot
                    </a>

                    <!-- POS CASHIER (Aktif jika di halaman kasir maupun riwayat) -->
                    <a href="{{ route('pos.cashier') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 {{ request()->routeIs('pos*') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        POS Cashier
                    </a>

                    <!-- PENGATURAN -->
                    <a href="{{ route('pengaturan') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 {{ request()->routeIs('pengaturan') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50' }}">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Pengaturan
                    </a>
                </nav>
            </div>

        </aside>

        <!-- KONTEN UTAMA (KANAN) -->
        <main class="flex-1 flex flex-col h-screen overflow-y-auto">
            
            <!-- NAVBAR ATAS -->
            <header class="sticky top-0 z-20 flex items-center justify-end border-b border-slate-200 bg-white/95 px-4 py-3 shadow-sm backdrop-blur sm:px-6">
                <!-- Menu profil -->
                <details class="group relative flex-shrink-0">
                    <summary
                        class="flex cursor-pointer list-none items-center gap-3 rounded-full border border-slate-200 bg-white py-1.5 pl-1.5 pr-3 shadow-sm transition hover:border-blue-200 hover:bg-blue-50 [&::-webkit-details-marker]:hidden"
                        aria-label="Buka menu profil"
                        style="list-style: none;"
                    >
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-slate-400 ring-2 ring-slate-200">
                            <svg
                                class="h-6 w-6"
                                fill="none"
                                stroke="currentColor"
                                viewBox="0 0 24 24"
                                aria-hidden="true"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    stroke-width="1.75"
                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.118a7.5 7.5 0 0115 0A17.93 17.93 0 0112 21.75a17.93 17.93 0 01-7.5-1.632z"
                                />
                            </svg>
                        </div>

                        <div class="hidden min-w-0 text-left sm:block">
                            <p class="max-w-36 truncate text-sm font-bold text-slate-800">
                                {{ $userName }}
                            </p>
                            <p class="max-w-36 truncate text-[10px] font-medium text-blue-600">
                                {{ $businessLabel }}
                            </p>
                        </div>

                        <svg
                            class="h-4 w-4 text-slate-400 transition group-open:rotate-180"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                            />
                        </svg>
                    </summary>

                    <div class="absolute right-0 z-50 mt-2 w-64 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-xl">
                        <div class="border-b border-slate-100 px-4 py-3">
                            <p class="truncate text-sm font-bold text-slate-800">
                                {{ $userName }}
                            </p>

                            @if ($currentUser->business_name)
                                <p class="mt-1 truncate text-xs font-medium text-blue-600">
                                    {{ $currentUser->business_name }}
                                </p>
                            @endif

                            <p class="mt-1 truncate text-xs text-slate-500">
                                {{ $currentUser->email }}
                            </p>
                        </div>

                        <div class="p-2">
                            <a
                                href="{{ route('pengaturan') }}"
                                class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-slate-600 transition hover:bg-slate-50 hover:text-blue-600"
                            >
                                <svg
                                    class="h-4 w-4"
                                    fill="none"
                                    stroke="currentColor"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"
                                    />
                                </svg>
                                Pengaturan Akun
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-left text-sm font-medium text-slate-600 transition hover:bg-red-50 hover:text-red-600"
                                >
                                    <svg
                                        class="h-4 w-4"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"
                                        />
                                    </svg>
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </details>
            </header>

            <!-- TEMPAT KONTEN DARI HALAMAN LAIN -->
            <div class="space-y-6 p-6">
                @yield('content')
            </div>

        </main>
    </div>
    @endauth
    <!-- ========================================== -->
    <!-- SELESAI KONDISI: UNTUK USER YANG LOGIN      -->
    <!-- ========================================== -->


    <!-- ========================================== -->
    <!-- KONDISI UNTUK GUEST (BELUM LOGIN)           -->
    <!-- ========================================== -->
    @guest
    <div class="min-h-screen flex items-center justify-center bg-slate-100">
        @yield('content')
    </div>
    @endguest

</body>
</html>