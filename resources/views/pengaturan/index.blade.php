@extends('layouts.app')

@section('title', 'Pengaturan - UsahaMate')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">⚙️ Pengaturan</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola pengaturan akun dan preferensi Anda.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Profil -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4">👤 Profil Saya</h3>
            <form action="{{ route('pengaturan.update') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Nama Lengkap</label>
                        <input type="text" name="name" value="{{ $user->name }}" 
                               class="w-full mt-1 rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Email</label>
                        <input type="email" name="email" value="{{ $user->email }}" 
                               class="w-full mt-1 rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-xl font-bold hover:bg-blue-700 transition">
                        <i class="fas fa-save mr-2"></i>Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>

        <!-- Password -->
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4">🔒 Ubah Password</h3>
            <form action="{{ route('pengaturan.password') }}" method="POST">
                @csrf
                @method('PUT')
                <div class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-gray-700">Password Lama</label>
                        <input type="password" name="current_password" 
                               class="w-full mt-1 rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Password Baru</label>
                        <input type="password" name="password" 
                               class="w-full mt-1 rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <div>
                        <label class="text-sm font-medium text-gray-700">Konfirmasi Password</label>
                        <input type="password" name="password_confirmation" 
                               class="w-full mt-1 rounded-xl border border-slate-200 px-4 py-2 text-sm outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-100">
                    </div>
                    <button type="submit" class="w-full bg-yellow-600 text-white py-2 rounded-xl font-bold hover:bg-yellow-700 transition">
                        <i class="fas fa-key mr-2"></i>Update Password
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Pengaturan Lainnya -->
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <h3 class="font-bold text-gray-800 mb-4">🌐 Pengaturan Lainnya</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <p class="text-sm font-medium">Notifikasi Email</p>
                    <p class="text-xs text-gray-500">Terima notifikasi via email</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer" checked>
                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <div>
                    <p class="text-sm font-medium">Mode Gelap</p>
                    <p class="text-xs text-gray-500">Tampilan dark mode</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" class="sr-only peer">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:ring-2 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600"></div>
                </label>
            </div>
        </div>
    </div>
</div>
@endsection