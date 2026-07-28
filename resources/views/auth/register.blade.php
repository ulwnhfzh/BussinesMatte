@extends('layouts.app')

@section('title', 'Register - UsahaMate')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100 py-8">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-8">
        <!-- Header -->
        <div class="text-center mb-6">
            <h1 class="text-3xl font-bold text-blue-600">UsahaMate</h1>
            <h2 class="text-xl font-semibold text-gray-800 mt-2">Buat Akun Baru</h2>
            <p class="text-sm text-gray-600">Sudah punya akun? <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Masuk ke Dashboard</a></p>
        </div>

        <!-- Form Register -->
        <form action="{{ route('register') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label for="name" class="block text-sm font-medium text-gray-700">Nama Lengkap Pemilik</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" 
                    class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror"
                    placeholder="Budi Santoso" required>
                @error('name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-3">
                <label for="business_name" class="block text-sm font-medium text-gray-700">Nama Bisnis / Perusahaan</label>
                <input type="text" name="business_name" id="business_name" value="{{ old('business_name') }}" 
                    class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('business_name') border-red-500 @enderror"
                    placeholder="CV. Maju Bersama" required>
                @error('business_name') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-3">
                <label for="email" class="block text-sm font-medium text-gray-700">Email Profesional</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" 
                    class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                    placeholder="owner@bisnisanda.com" required>
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
                <input type="password" name="password" id="password" 
                    class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('password') border-red-500 @enderror"
                    placeholder="Gunakan minimal 8 karakter" required>
                @error('password') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Kata Sandi</label>
                <input type="password" name="password_confirmation" id="password_confirmation" 
                    class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                    placeholder="Ulangi kata sandi" required>
            </div>

            <div class="flex items-start mb-4">
                <input type="checkbox" name="terms" id="terms" value="1" class="mt-1 h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                <label for="terms" class="ml-2 block text-sm text-gray-700">
                    Saya menyetujui <a href="#" class="text-blue-600 hover:underline">Syarat dan Ketentuan</a> serta <a href="#" class="text-blue-600 hover:underline">Kebijakan Privasi</a> UsahaMate.
                </label>
            </div>
            @error('terms') <p class="text-red-500 text-xs -mt-3 mb-3">{{ $message }}</p> @enderror

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                Buat Akun Usaha
            </button>
        </form>

        <!-- Divider -->
        <div class="flex items-center my-4">
            <div class="flex-1 border-t border-gray-300"></div>
            <span class="px-3 text-sm text-gray-500">ATAU DAFTAR DENGAN</span>
            <div class="flex-1 border-t border-gray-300"></div>
        </div>

        <!-- Social Register -->
        <div class="space-y-2">
            <button class="w-full border border-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-50 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92a5.06 5.06 0 0 1-2.2 3.32v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.1z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
                Google
            </button>
            <button class="w-full border border-gray-300 text-gray-700 py-2 rounded-lg hover:bg-gray-50 transition flex items-center justify-center gap-2">
                <svg class="w-5 h-5" viewBox="0 0 24 24"><path fill="#1877F2" d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                Facebook
            </button>
        </div>

        <p class="text-center text-sm text-gray-600 mt-4">
            Butuh bantuan daftar? <a href="#" class="text-blue-600 hover:underline">Hubungi Account Manager</a>
        </p>

        <hr class="my-4">

        <div class="flex justify-center gap-4 text-xs text-gray-500">
            <span>© 2024 UsahaMate Enterprise</span>
            <a href="#" class="hover:underline">Bantuan</a>
            <a href="#" class="hover:underline">Keamanan</a>
            <a href="#" class="hover:underline">Kontak Kami</a>
        </div>
    </div>
</div>
@endsection