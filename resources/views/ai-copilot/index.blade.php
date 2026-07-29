@extends('layouts.app')

@section('title', 'AI Copilot - UsahaMate')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">🤖 AI Copilot</h2>
            <p class="text-sm text-gray-500 mt-1">Asisten cerdas untuk membantu keputusan bisnis Anda.</p>
        </div>
        <div class="flex gap-3">
            <button class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                <i class="fas fa-sync mr-2"></i>Refresh Insight
            </button>
        </div>
    </div>

    <!-- AI Chat Interface -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <!-- Chat Area -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 flex flex-col h-[500px]">
            <div class="p-4 border-b border-gray-100">
                <h3 class="font-bold text-gray-800">💬 AI Assistant</h3>
                <p class="text-xs text-gray-400">Tanyakan apa saja tentang bisnis Anda</p>
            </div>
            <div class="flex-1 p-4 overflow-y-auto space-y-4">
                @foreach($suggestions as $suggestion)
                <div class="bg-blue-50 p-4 rounded-xl">
                    <p class="text-xs font-bold text-blue-800 mb-1">{{ $suggestion['title'] }}</p>
                    <p class="text-sm text-gray-700 leading-relaxed">{{ $suggestion['message'] }}</p>
                    <button class="mt-2 text-xs bg-white px-3 py-1 rounded-full shadow-sm border border-blue-100 text-blue-600 font-medium hover:bg-blue-50 transition">
                        {{ $suggestion['action'] }}
                    </button>
                </div>
                @endforeach
            </div>
            <div class="p-4 border-t border-gray-100">
                <div class="flex gap-2">
                    <input type="text" placeholder="Tanyakan sesuatu ke AI..." class="flex-1 rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm shadow-sm outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-100">
                    <button class="bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Sidebar AI Info -->
        <div class="space-y-6">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h4 class="font-bold text-gray-800 text-sm mb-4">📊 Statistik Cepat</h4>
                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Prediksi Hari Ini</span>
                        <span class="font-bold text-green-600">+12%</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Produk Terlaris</span>
                        <span class="font-bold text-gray-800">Kopi Arabika</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Stok Kritis</span>
                        <span class="font-bold text-red-600">3 Produk</span>
                    </div>
                </div>
            </div>
            <div class="bg-gradient-to-br from-blue-600 to-blue-800 p-6 rounded-2xl text-white">
                <h4 class="font-bold text-sm mb-2">🎯 Rekomendasi AI</h4>
                <p class="text-xs opacity-90 leading-relaxed">Tingkatkan stok Kopi Arabika Gayo karena permintaan meningkat 25% minggu ini.</p>
                <button class="mt-3 bg-white text-blue-600 text-xs px-4 py-2 rounded-full font-bold hover:bg-blue-50 transition">
                    Lihat Detail
                </button>
            </div>
        </div>
    </div>
</div>
@endsection