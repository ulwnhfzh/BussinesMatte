﻿<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'UsahaMate')</title>
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
    <div class="flex h-screen overflow-hidden">
        
        <!-- SIDEBAR KIRI -->
        <aside class="hidden w-72 flex-shrink-0 flex-col justify-between border-r border-slate-200 bg-white p-6 md:flex">
            <div>
                <!-- Logo UsahaMate -->
                <div class="flex items-center gap-2 mb-10">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">UM</div>
                    <div>
                        <h1 class="font-bold text-lg leading-tight text-blue-600">UsahaMate</h1>
                        <span class="text-[10px] text-gray-500">ENTERPRISE V2.0</span>
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

                    <!-- POS CASHIER -->
                    <a href="{{ route('pos.cashier') }}" class="flex items-center gap-3 px-4 py-3 rounded-xl transition duration-200 {{ request()->routeIs('pos.cashier') ? 'bg-blue-600 text-white shadow-md' : 'text-slate-600 hover:bg-slate-50' }}">
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

            <!-- Bagian Bawah Sidebar -->
            <div>
                <div class="mb-4 rounded-2xl border border-blue-100 bg-blue-50 p-4 shadow-sm">
                    <div class="flex justify-between text-xs mb-1 text-blue-800"><span>Penyimpanan Cloud</span> <span>72%</span></div>
                    <div class="w-full bg-blue-200 rounded-full h-2"><div class="bg-blue-600 h-2 rounded-full" style="width: 72%"></div></div>
                </div>
                <button class="flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-4 py-3 font-semibold text-white shadow-lg transition hover:bg-blue-700">
                    <span>+</span> Transaksi Baru
                </button>
            </div>
        </aside>

        <!-- KONTEN UTAMA (KANAN) -->
        <main class="flex-1 flex flex-col h-screen overflow-y-auto">
            
            <!-- NAVBAR ATAS (Search & Profile) -->
            <header class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-slate-100/95 px-6 py-4 backdrop-blur">
                <!-- Search Bar -->
                <div class="relative w-full max-w-md">
                    <input type="text" placeholder="Cari data, laporan, atau instruksi AI..." class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-10 pr-4 text-sm shadow-sm outline-none ring-0 focus:border-blue-300 focus:ring-2 focus:ring-blue-100">
                    <svg class="w-5 h-5 text-gray-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                
                <!-- Right Actions -->
                <div class="flex items-center gap-3">
                    <div class="rounded-full bg-white p-2 shadow-sm">
                        <svg class="h-5 w-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    </div>
                    
                    <div class="flex items-center gap-3 rounded-full bg-white px-3 py-2 shadow-sm">
                        <div class="text-right hidden sm:block">
                            <p class="font-bold text-sm">{{ Auth::user()->name }}</p>
                            <p class="text-[10px] text-blue-600 font-semibold">TIER: ENTERPRISE</p>
                        </div>
                        <img src="https://i.pravatar.cc/150?u={{ Auth::id() }}" alt="Profile" class="w-10 h-10 rounded-full border-2 border-blue-200">
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-2 text-sm font-medium text-slate-600 shadow-sm transition hover:bg-red-50 hover:text-red-600">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h6a2 2 0 012 2v1"></path></svg>
                            Logout
                        </button>
                    </form>
                </div>
            </header>

            <!-- TEMPAT KONTEN DARI HALAMAN LAIN (DASHBOARD / INVENTORY) -->
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