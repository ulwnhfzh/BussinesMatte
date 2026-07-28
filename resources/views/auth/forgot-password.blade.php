@extends('layouts.app')

@section('title', 'Lupa Password - UsahaMate')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-8">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-blue-600">UsahaMate</h1>
            <h2 class="text-xl font-semibold text-gray-800 mt-2">Lupa Kata Sandi?</h2>
            <p class="text-sm text-gray-600 mt-2">Masukkan email yang terdaftar untuk menerima instruksi pengaturan ulang kata sandi.</p>
        </div>

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        <form action="{{ route('password.email') }}" method="POST">
            @csrf
            <div class="mb-4">
                <label for="email" class="block text-sm font-medium text-gray-700">Alamat Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" 
                    class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 @error('email') border-red-500 @enderror"
                    placeholder="nama@email.com" required>
                @error('email') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700 transition font-semibold">
                Kirim Instruksi Pemulihan
            </button>
        </form>

        <div class="flex items-center my-4">
            <div class="flex-1 border-t border-gray-300"></div>
            <span class="px-3 text-sm text-gray-500">ATAU</span>
            <div class="flex-1 border-t border-gray-300"></div>
        </div>

        <p class="text-center text-sm">
            <a href="{{ route('login') }}" class="text-blue-600 hover:underline font-medium">← Kembali ke Login</a>
        </p>

        <p class="text-center text-sm text-gray-600 mt-4">
            Butuh bantuan lebih lanjut? <a href="#" class="text-blue-600 hover:underline">Hubungi Dukungan</a>
        </p>
    </div>
</div>
@endsection