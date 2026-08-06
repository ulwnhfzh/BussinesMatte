@extends('layouts.app')

@section('title', 'Pengaturan - BusinessMate')

@section('content')
@php
    $storedProfilePhoto = trim((string) $user->profile_photo);
    $profilePhotoUrl = null;

    if ($storedProfilePhoto !== '') {
        $normalizedProfilePhoto = ltrim(
            str_replace('\\', '/', $storedProfilePhoto),
            '/'
        );

        if (filter_var($storedProfilePhoto, FILTER_VALIDATE_URL)) {
            $profilePhotoUrl = $storedProfilePhoto;
        } elseif (str_starts_with($normalizedProfilePhoto, 'storage/')) {
            $profilePhotoUrl = asset($normalizedProfilePhoto);
        } elseif (str_contains($normalizedProfilePhoto, '/')) {
            $profilePhotoUrl = asset('storage/' . $normalizedProfilePhoto);
        } else {
            $profilePhotoUrl = asset(
                'storage/profile-photos/' . $normalizedProfilePhoto
            );
        }

        $profilePhotoUrl .= '?v=' . optional($user->updated_at)->timestamp;
    }
@endphp

<div class="space-y-6">

    {{-- ====================================================== --}}
    {{-- NOTIFIKASI --}}
    {{-- ====================================================== --}}

    @if(session('success'))
        <div class="flex items-start gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-700">
            <i class="fas fa-check-circle mt-0.5"></i>

            <div>
                <p class="text-sm font-semibold">Berhasil</p>
                <p class="mt-1 text-sm">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="flex items-start gap-3 rounded-2xl border border-red-200 bg-red-50 p-4 text-red-700">
            <i class="fas fa-exclamation-circle mt-0.5"></i>

            <div>
                <p class="text-sm font-semibold">Terjadi Kesalahan</p>
                <p class="mt-1 text-sm">{{ session('error') }}</p>
            </div>
        </div>
    @endif


    {{-- ====================================================== --}}
    {{-- HEADER --}}
    {{-- ====================================================== --}}

    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">
                Pengaturan
            </h1>

            <p class="mt-1 text-sm text-gray-500">
                Kelola profil, keamanan akun, dan laporan bisnis Anda.
            </p>
        </div>

        <button
            type="button"
            id="open-report-export-modal"
            class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700"
        >
            <i class="fas fa-file-export"></i>
            Ekspor Laporan
        </button>
    </div>


    {{-- ====================================================== --}}
    {{-- PROFIL DAN PASSWORD --}}
    {{-- ====================================================== --}}

    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        {{-- PROFIL --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-800">
                    Profil Saya
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Perbarui nama, email, dan foto profil akun Anda.
                </p>
            </div>

            <form
                action="{{ route('pengaturan.update') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf
                @method('PUT')

                <div class="space-y-5">

                    {{-- FOTO PROFIL --}}
                    <div>
                        <label class="text-sm font-semibold text-gray-700">
                            Foto Profil
                        </label>

                        <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-center">

                            <div class="relative h-24 w-24 shrink-0 overflow-hidden rounded-full border-4 border-blue-50 bg-slate-100">

                                <img
                                    id="profile-photo-preview"
                                    src="{{ $profilePhotoUrl ?? '' }}"
                                    alt=""
                                    onerror="this.classList.add('hidden'); document.getElementById('profile-photo-fallback').classList.remove('hidden');"
                                    class="h-full w-full object-cover {{ $profilePhotoUrl ? '' : 'hidden' }}"
                                >

                                <div
                                    id="profile-photo-fallback"
                                    class="flex h-full w-full items-center justify-center text-slate-400 {{ $profilePhotoUrl ? 'hidden' : '' }}"
                                >
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        class="h-14 w-14"
                                    >
                                        <path
                                            stroke-linecap="round"
                                            stroke-linejoin="round"
                                            d="M15.75 6.75a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.5 20.1a7.5 7.5 0 0115 0A17.9 17.9 0 0112 21.75a17.9 17.9 0 01-7.5-1.65z"
                                        />
                                    </svg>
                                </div>
                            </div>

                            <div class="flex-1">
                                <input
                                    type="file"
                                    id="profile_photo"
                                    name="profile_photo"
                                    accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp"
                                    class="block w-full cursor-pointer rounded-xl border border-slate-200 bg-white text-sm text-gray-600 file:mr-4 file:border-0 file:bg-blue-50 file:px-4 file:py-3 file:text-sm file:font-semibold file:text-blue-600 hover:file:bg-blue-100"
                                >

                                <p class="mt-2 text-xs text-gray-400">
                                    Format JPG, JPEG, PNG, atau WebP. Maksimal 2 MB.
                                </p>

                                @if($user->profile_photo)
                                    <label class="mt-3 inline-flex cursor-pointer items-center gap-2 text-sm text-red-600">
                                        <input
                                            type="checkbox"
                                            id="remove_profile_photo"
                                            name="remove_profile_photo"
                                            value="1"
                                            class="h-4 w-4 rounded border-gray-300 text-red-600 focus:ring-red-500"
                                            @checked(old('remove_profile_photo'))
                                        >

                                        Hapus foto profil
                                    </label>
                                @endif

                                @error('profile_photo', 'profile')
                                    <p class="mt-2 text-xs text-red-600">
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- NAMA --}}
                    <div>
                        <label
                            for="name"
                            class="text-sm font-semibold text-gray-700"
                        >
                            Nama Lengkap
                        </label>

                        <input
                            id="name"
                            type="text"
                            name="name"
                            value="{{ old('name', $user->name) }}"
                            required
                            autocomplete="name"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        >

                        @error('name', 'profile')
                            <p class="mt-2 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- EMAIL --}}
                    <div>
                        <label
                            for="email"
                            class="text-sm font-semibold text-gray-700"
                        >
                            Email
                        </label>

                        <input
                            id="email"
                            type="email"
                            name="email"
                            value="{{ old('email', $user->email) }}"
                            required
                            autocomplete="email"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        >

                        @error('email', 'profile')
                            <p class="mt-2 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700"
                    >
                        <i class="fas fa-save"></i>
                        Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>


        {{-- PASSWORD --}}
        <div class="rounded-2xl border border-gray-100 bg-white p-6 shadow-sm">
            <div class="mb-6">
                <h2 class="text-lg font-bold text-gray-800">
                    Keamanan Akun
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Gunakan password yang kuat untuk melindungi akun Anda.
                </p>
            </div>

            <form
                action="{{ route('pengaturan.password') }}"
                method="POST"
            >
                @csrf
                @method('PUT')

                <div class="space-y-5">

                    {{-- PASSWORD LAMA --}}
                    <div>
                        <label
                            for="current_password"
                            class="text-sm font-semibold text-gray-700"
                        >
                            Password Lama
                        </label>

                        <input
                            id="current_password"
                            type="password"
                            name="current_password"
                            required
                            autocomplete="current-password"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        >

                        @error('current_password', 'password')
                            <p class="mt-2 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- PASSWORD BARU --}}
                    <div>
                        <label
                            for="password"
                            class="text-sm font-semibold text-gray-700"
                        >
                            Password Baru
                        </label>

                        <input
                            id="password"
                            type="password"
                            name="password"
                            required
                            autocomplete="new-password"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        >

                        <p class="mt-2 text-xs text-gray-400">
                            Password minimal terdiri dari 8 karakter.
                        </p>

                        @error('password', 'password')
                            <p class="mt-2 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- KONFIRMASI PASSWORD --}}
                    <div>
                        <label
                            for="password_confirmation"
                            class="text-sm font-semibold text-gray-700"
                        >
                            Konfirmasi Password Baru
                        </label>

                        <input
                            id="password_confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            autocomplete="new-password"
                            class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        >
                    </div>

                    <button
                        type="submit"
                        class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-amber-500 px-5 py-3 text-sm font-semibold text-white transition hover:bg-amber-600"
                    >
                        <i class="fas fa-key"></i>
                        Perbarui Password
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>


{{-- ========================================================== --}}
{{-- MODAL EKSPOR LAPORAN --}}
{{-- ========================================================== --}}

<div
    id="report-export-modal"
    class="fixed inset-0 z-[100] hidden items-center justify-center p-4"
    aria-hidden="true"
>
    {{-- BACKDROP --}}
    <div
        id="report-export-backdrop"
        class="absolute inset-0 bg-slate-900/50 backdrop-blur-sm"
    ></div>

    {{-- MODAL CONTENT --}}
    <div class="relative z-10 max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-2xl bg-white shadow-2xl">

        {{-- MODAL HEADER --}}
        <div class="flex items-start justify-between border-b border-slate-100 p-6">
            <div>
                <h2 class="text-xl font-bold text-gray-800">
                    Ekspor Laporan
                </h2>

                <p class="mt-1 text-sm text-gray-500">
                    Pilih jenis laporan, periode, dan format file.
                </p>
            </div>

            <button
                type="button"
                id="close-report-export-modal"
                class="flex h-10 w-10 items-center justify-center rounded-xl text-gray-400 transition hover:bg-slate-100 hover:text-gray-700"
                aria-label="Tutup modal"
            >
                <i class="fas fa-times"></i>
            </button>
        </div>

        <form
            action="{{ route('pengaturan.reports.export') }}"
            method="POST"
        >
            @csrf

            <div class="space-y-6 p-6">

                {{-- JENIS LAPORAN --}}
                <div>
                    <label
                        for="report_type"
                        class="text-sm font-semibold text-gray-700"
                    >
                        Jenis Laporan
                    </label>

                    <select
                        id="report_type"
                        name="report_type"
                        required
                        class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                    >
                        <option
                            value="inventory"
                            @selected(old('report_type', 'inventory') === 'inventory')
                        >
                            Laporan Inventory
                        </option>

                        <option
                            value="stock_movements"
                            @selected(old('report_type') === 'stock_movements')
                        >
                            Laporan Riwayat Stok
                        </option>

                        <option
                            value="sales"
                            @selected(old('report_type') === 'sales')
                        >
                            Laporan Penjualan POS
                        </option>

                        <option
                            value="analytics"
                            @selected(old('report_type') === 'analytics')
                        >
                            Laporan Analytics
                        </option>

                        <option
                            value="ai_prediction"
                            @selected(old('report_type') === 'ai_prediction')
                        >
                            Laporan Prediksi AI
                        </option>

                        <option
                            value="complete"
                            @selected(old('report_type') === 'complete')
                        >
                            Laporan Lengkap
                        </option>
                    </select>

                    @error('report_type', 'reportExport')
                        <p class="mt-2 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>


                {{-- INFORMASI JENIS LAPORAN --}}
                <div
                    id="report-type-info"
                    class="rounded-xl border border-blue-100 bg-blue-50 p-4"
                >
                    <div class="flex items-start gap-3">
                        <div
                            id="report-type-icon"
                            class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600"
                        >
                            <i class="fas fa-boxes"></i>
                        </div>

                        <div>
                            <p
                                id="report-type-title"
                                class="text-sm font-semibold text-blue-800"
                            >
                                Laporan Inventory
                            </p>

                            <p
                                id="report-type-description"
                                class="mt-1 text-xs leading-relaxed text-blue-700"
                            >
                                Menampilkan kondisi stok, harga, batas minimum,
                                batas maksimum, status, dan nilai inventory pada
                                saat laporan dibuat. Laporan ini tidak memerlukan
                                pilihan periode.
                            </p>
                        </div>
                    </div>
                </div>


                {{-- PERIODE LAPORAN HISTORIS --}}
                <div
                    id="report-period-section"
                    class="hidden space-y-4"
                >
                    <div>
                        <label
                            for="report_period"
                            class="text-sm font-semibold text-gray-700"
                        >
                            Periode Laporan
                        </label>

                        <select
                            id="report_period"
                            name="period"
                            class="mt-2 w-full rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                        >
                            <option
                                value="today"
                                @selected(old('period') === 'today')
                            >
                                Hari Ini
                            </option>

                            <option
                                value="7_days"
                                @selected(old('period', '7_days') === '7_days')
                            >
                                7 Hari Terakhir
                            </option>

                            <option
                                value="30_days"
                                @selected(old('period') === '30_days')
                            >
                                30 Hari Terakhir
                            </option>

                            <option
                                value="custom"
                                @selected(old('period') === 'custom')
                            >
                                Pilih Tanggal Sendiri
                            </option>
                        </select>

                        @error('period', 'reportExport')
                            <p class="mt-2 text-xs text-red-600">
                                {{ $message }}
                            </p>
                        @enderror
                    </div>

                    {{-- TANGGAL CUSTOM --}}
                    <div
                        id="custom-date-section"
                        class="hidden grid grid-cols-1 gap-4 sm:grid-cols-2"
                    >
                        <div>
                            <label
                                for="start_date"
                                class="text-sm font-semibold text-gray-700"
                            >
                                Tanggal Mulai
                            </label>

                            <input
                                id="start_date"
                                type="date"
                                name="start_date"
                                value="{{ old('start_date') }}"
                                max="{{ now()->timezone('Asia/Jakarta')->format('Y-m-d') }}"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                            >

                            @error('start_date', 'reportExport')
                                <p class="mt-2 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>

                        <div>
                            <label
                                for="end_date"
                                class="text-sm font-semibold text-gray-700"
                            >
                                Tanggal Selesai
                            </label>

                            <input
                                id="end_date"
                                type="date"
                                name="end_date"
                                value="{{ old('end_date') }}"
                                max="{{ now()->timezone('Asia/Jakarta')->format('Y-m-d') }}"
                                class="mt-2 w-full rounded-xl border border-slate-200 px-4 py-3 text-sm outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100"
                            >

                            @error('end_date', 'reportExport')
                                <p class="mt-2 text-xs text-red-600">
                                    {{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>
                </div>


                {{-- FORMAT FILE --}}
                <div>
                    <p class="text-sm font-semibold text-gray-700">
                        Format File
                    </p>

                    <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-3">

                        {{-- PDF --}}
                        <label class="cursor-pointer">
                            <input
                                type="radio"
                                name="format"
                                value="pdf"
                                class="peer sr-only"
                                @checked(old('format', 'pdf') === 'pdf')
                            >

                            <div class="rounded-xl border-2 border-slate-200 p-4 text-center transition peer-checked:border-red-500 peer-checked:bg-red-50">
                                <i class="fas fa-file-pdf text-2xl text-red-500"></i>

                                <p class="mt-2 text-sm font-semibold text-gray-800">
                                    PDF
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    Siap dicetak
                                </p>
                            </div>
                        </label>

                        {{-- EXCEL --}}
                        <label class="cursor-pointer">
                            <input
                                type="radio"
                                name="format"
                                value="xlsx"
                                class="peer sr-only"
                                @checked(old('format') === 'xlsx')
                            >

                            <div class="rounded-xl border-2 border-slate-200 p-4 text-center transition peer-checked:border-emerald-500 peer-checked:bg-emerald-50">
                                <i class="fas fa-file-excel text-2xl text-emerald-500"></i>

                                <p class="mt-2 text-sm font-semibold text-gray-800">
                                    Excel
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    Dapat diolah
                                </p>
                            </div>
                        </label>

                        {{-- CSV --}}
                        <label class="cursor-pointer">
                            <input
                                type="radio"
                                name="format"
                                value="csv"
                                class="peer sr-only"
                                @checked(old('format') === 'csv')
                            >

                            <div class="rounded-xl border-2 border-slate-200 p-4 text-center transition peer-checked:border-blue-500 peer-checked:bg-blue-50">
                                <i class="fas fa-file-csv text-2xl text-blue-500"></i>

                                <p class="mt-2 text-sm font-semibold text-gray-800">
                                    CSV
                                </p>

                                <p class="mt-1 text-xs text-gray-400">
                                    Data mentah
                                </p>
                            </div>
                        </label>
                    </div>

                    @error('format', 'reportExport')
                        <p class="mt-2 text-xs text-red-600">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- INFORMASI FORMAT KHUSUS --}}
                <div
                    id="report-format-note"
                    class="hidden rounded-xl border border-amber-200 bg-amber-50 p-4 text-xs leading-relaxed text-amber-700"
                ></div>
            </div>


            {{-- FOOTER MODAL --}}
            <div class="flex flex-col-reverse gap-3 border-t border-slate-100 p-6 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    id="cancel-report-export"
                    class="rounded-xl border border-slate-200 px-5 py-3 text-sm font-semibold text-gray-600 transition hover:bg-slate-50"
                >
                    Batal
                </button>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-blue-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-blue-700"
                >
                    <i class="fas fa-download"></i>
                    Unduh Laporan
                </button>
            </div>
        </form>
    </div>
</div>


{{-- ========================================================== --}}
{{-- JAVASCRIPT --}}
{{-- ========================================================== --}}

<script>
document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | FOTO PROFIL
    |--------------------------------------------------------------------------
    */

    const profilePhotoInput = document.getElementById('profile_photo');
    const profilePhotoPreview = document.getElementById('profile-photo-preview');
    const profilePhotoFallback = document.getElementById('profile-photo-fallback');
    const removeProfilePhoto = document.getElementById('remove_profile_photo');

    let previewObjectUrl = null;

    if (profilePhotoInput) {
        profilePhotoInput.addEventListener('change', function (event) {
            const file = event.target.files[0];

            if (!file) {
                return;
            }

            if (previewObjectUrl) {
                URL.revokeObjectURL(previewObjectUrl);
            }

            previewObjectUrl = URL.createObjectURL(file);

            profilePhotoPreview.src = previewObjectUrl;
            profilePhotoPreview.classList.remove('hidden');
            profilePhotoFallback.classList.add('hidden');

            if (removeProfilePhoto) {
                removeProfilePhoto.checked = false;
            }
        });
    }

    if (removeProfilePhoto) {
        removeProfilePhoto.addEventListener('change', function () {
            if (this.checked) {
                profilePhotoPreview.classList.add('hidden');
                profilePhotoFallback.classList.remove('hidden');

                if (profilePhotoInput) {
                    profilePhotoInput.value = '';
                }
            } else {
                @if($user->profile_photo)
                    profilePhotoPreview.src =
                        @json($profilePhotoUrl);

                    profilePhotoPreview.classList.remove('hidden');
                    profilePhotoFallback.classList.add('hidden');
                @endif
            }
        });
    }


    /*
    |--------------------------------------------------------------------------
    | MODAL EKSPOR
    |--------------------------------------------------------------------------
    */

    const modal = document.getElementById('report-export-modal');
    const openModalButton = document.getElementById('open-report-export-modal');
    const otherOpenModalButtons = document.querySelectorAll(
        '.open-report-export-modal'
    );

    const closeModalButton = document.getElementById(
        'close-report-export-modal'
    );

    const cancelModalButton = document.getElementById(
        'cancel-report-export'
    );

    const modalBackdrop = document.getElementById(
        'report-export-backdrop'
    );

    function openReportModal() {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('overflow-hidden');
    }

    function closeReportModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        modal.setAttribute('aria-hidden', 'true');

        document.body.classList.remove('overflow-hidden');
    }

    if (openModalButton) {
        openModalButton.addEventListener('click', openReportModal);
    }

    otherOpenModalButtons.forEach(function (button) {
        button.addEventListener('click', openReportModal);
    });

    if (closeModalButton) {
        closeModalButton.addEventListener('click', closeReportModal);
    }

    if (cancelModalButton) {
        cancelModalButton.addEventListener('click', closeReportModal);
    }

    if (modalBackdrop) {
        modalBackdrop.addEventListener('click', closeReportModal);
    }

    document.addEventListener('keydown', function (event) {
        if (
            event.key === 'Escape' &&
            !modal.classList.contains('hidden')
        ) {
            closeReportModal();
        }
    });


    /*
    |--------------------------------------------------------------------------
    | PENGATURAN JENIS LAPORAN, PERIODE, DAN FORMAT
    |--------------------------------------------------------------------------
    */

    const reportTypeSelect = document.getElementById('report_type');
    const reportTypeIcon = document.getElementById('report-type-icon');
    const reportTypeTitle = document.getElementById('report-type-title');
    const reportTypeDescription = document.getElementById(
        'report-type-description'
    );
    const reportPeriodSection = document.getElementById(
        'report-period-section'
    );
    const reportPeriodSelect = document.getElementById(
        'report_period'
    );
    const customDateSection = document.getElementById(
        'custom-date-section'
    );
    const startDateInput = document.getElementById('start_date');
    const endDateInput = document.getElementById('end_date');
    const reportFormatNote = document.getElementById(
        'report-format-note'
    );
    const formatInputs = document.querySelectorAll(
        'input[name="format"]'
    );

    const reportConfigurations = {
        inventory: {
            title: 'Laporan Inventory',
            description:
                'Menampilkan kondisi stok, harga beli, harga jual, batas minimum, batas maksimum, status, dan nilai inventory saat laporan dibuat.',
            icon: 'fas fa-boxes',
            usesPeriod: false
        },

        stock_movements: {
            title: 'Laporan Riwayat Stok',
            description:
                'Menampilkan stok awal, penyesuaian manual, penjualan, restok, retur, refund, dan void sesuai periode yang dipilih.',
            icon: 'fas fa-history',
            usesPeriod: true
        },

        sales: {
            title: 'Laporan Penjualan POS',
            description:
                'Menampilkan transaksi POS, metode pembayaran, subtotal, pajak, diskon, total pembayaran, modal, dan laba.',
            icon: 'fas fa-cash-register',
            usesPeriod: true
        },

        analytics: {
            title: 'Laporan Analytics',
            description:
                'Menampilkan performa harian, jumlah transaksi, produk terjual, pendapatan, modal, laba, margin, dan produk unggulan.',
            icon: 'fas fa-chart-line',
            usesPeriod: true
        },

        ai_prediction: {
            title: 'Laporan Prediksi AI',
            description:
                'Menampilkan metode prediksi aktif, prakiraan permintaan, rekomendasi restok, estimasi biaya, dan alasan rekomendasi.',
            icon: 'fas fa-brain',
            usesPeriod: false
        },

        complete: {
            title: 'Laporan Lengkap',
            description:
                'Menggabungkan laporan Inventory, Riwayat Stok, Penjualan POS, Analytics, dan Prediksi AI dalam satu hasil ekspor.',
            icon: 'fas fa-file-archive',
            usesPeriod: true
        }
    };

    function getSelectedFormat() {
        const selectedFormat = document.querySelector(
            'input[name="format"]:checked'
        );

        return selectedFormat ? selectedFormat.value : 'pdf';
    }

    function syncCustomDateFields() {
        const configuration =
            reportConfigurations[reportTypeSelect.value]
            ?? reportConfigurations.inventory;

        const isCustomPeriod =
            configuration.usesPeriod &&
            reportPeriodSelect.value === 'custom';

        customDateSection.classList.toggle(
            'hidden',
            !isCustomPeriod
        );

        startDateInput.required = isCustomPeriod;
        endDateInput.required = isCustomPeriod;
    }

    function syncFormatInformation() {
        const reportType = reportTypeSelect.value;
        const selectedFormat = getSelectedFormat();
        let message = '';

        if (reportType === 'complete') {
            if (selectedFormat === 'xlsx') {
                message =
                    'Laporan lengkap Excel dibuat dalam satu file dengan lima sheet: Inventory, Riwayat Stok, Penjualan POS, Analytics, dan Prediksi AI.';
            }

            if (selectedFormat === 'pdf') {
                message =
                    'Laporan lengkap PDF dibuat dalam satu dokumen dengan beberapa bagian dan halaman.';
            }

            if (selectedFormat === 'csv') {
                message =
                    'CSV tidak mendukung beberapa sheet. Sistem akan mengunduh satu file ZIP yang berisi lima laporan CSV.';
            }
        }

        reportFormatNote.textContent = message;
        reportFormatNote.classList.toggle('hidden', message === '');
    }

    function syncReportFields() {
        const configuration =
            reportConfigurations[reportTypeSelect.value]
            ?? reportConfigurations.inventory;

        reportTypeTitle.textContent = configuration.title;
        reportTypeDescription.textContent = configuration.description;
        reportTypeIcon.innerHTML = `<i class="${configuration.icon}"></i>`;

        reportPeriodSection.classList.toggle(
            'hidden',
            !configuration.usesPeriod
        );

        reportPeriodSelect.required = configuration.usesPeriod;

        syncCustomDateFields();
        syncFormatInformation();
    }

    if (reportTypeSelect) {
        reportTypeSelect.addEventListener(
            'change',
            syncReportFields
        );
    }

    if (reportPeriodSelect) {
        reportPeriodSelect.addEventListener(
            'change',
            syncCustomDateFields
        );
    }

    formatInputs.forEach(function (formatInput) {
        formatInput.addEventListener(
            'change',
            syncFormatInformation
        );
    });

    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function () {
            endDateInput.min = this.value;

            if (
                endDateInput.value &&
                endDateInput.value < this.value
            ) {
                endDateInput.value = '';
            }
        });
    }

    syncReportFields();


    /*
    |--------------------------------------------------------------------------
    | BUKA KEMBALI MODAL JIKA VALIDASI EKSPOR GAGAL
    |--------------------------------------------------------------------------
    */

    const hasReportExportErrors =
        @json($errors->reportExport->any());

    if (hasReportExportErrors) {
        openReportModal();
    }
});
</script>
@endsection