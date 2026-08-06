@extends('layouts.app')

@section('title', 'Detail Transaksi - UsahaMate')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <a href="{{ route('pos.transactions.index') }}" class="inline-flex items-center gap-2 text-xs font-semibold text-slate-500 hover:text-blue-600 mb-2 transition">
                <i class="fa-solid fa-arrow-left"></i> Kembali ke Riwayat
            </a>
            <h2 class="text-2xl font-bold text-slate-800">Detail Transaksi {{ $transaction->invoice_number }}</h2>
            <div class="mt-2">
                @php
                    $statusClass = match ($transaction->status) {
                        'voided' => 'bg-red-100 text-red-700 border-red-200',
                        'refunded' => 'bg-amber-100 text-amber-700 border-amber-200',
                        'returned' => 'bg-purple-100 text-purple-700 border-purple-200',
                        default => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                    };
                @endphp
                <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full border {{ $statusClass }}">
                    <i class="fa-solid fa-circle text-[6px]"></i>
                    {{ $transaction->status_label }}
                </span>
            </div>
        </div>
        <a href="{{ route('pos.transactions.print', $transaction->id) }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition shadow-sm w-fit">
            <i class="fa-solid fa-print"></i> Cetak Struk
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Rincian Item (Kiri) -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 font-bold text-slate-800">
                Item Pembelian
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-3">Produk</th>
                            <th class="px-6 py-3 text-right">Harga</th>
                            <th class="px-6 py-3 text-center">Qty</th>
                            <th class="px-6 py-3 text-right">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($transaction->details as $item)
                        <tr>
                            <td class="px-6 py-4">
                                <div class="font-bold text-slate-800">{{ $item->product->name ?? 'Produk Dihapus' }}</div>
                                @if($item->note)
                                    <div class="text-xs text-slate-400">Catatan: {{ $item->note }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                Rp {{ number_format($item->selling_price, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-4 text-center font-semibold">
                                {{ $item->quantity }}
                            </td>
                            <td class="px-6 py-4 text-right font-bold text-slate-800">
                                Rp {{ number_format($item->subtotal, 0, ',', '.') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ringkasan Informasi (Kanan) -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 space-y-4">
            <h3 class="font-bold text-slate-800 border-b border-slate-100 pb-3">Ringkasan Pembayaran</h3>

            <div class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-slate-500">Tanggal Transaksi</span>
                    <span class="font-semibold text-slate-800">{{ $transaction->created_at->format('d/m/Y H:i') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Kasir</span>
                    <span class="font-semibold text-slate-800">{{ $transaction->user->name ?? '-' }}</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-slate-500">Metode Pembayaran</span>
                    <span class="px-2.5 py-1 bg-slate-100 font-bold text-xs uppercase rounded-lg text-slate-700">
                        {{ $transaction->payment_method }}
                    </span>
                </div>
                
                <hr class="border-slate-100">

                <div class="flex justify-between text-slate-600">
                    <span>Subtotal</span>
                    <span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Pajak (Tax)</span>
                    <span>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between text-slate-600">
                    <span>Diskon</span>
                    <span class="text-emerald-600">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span>
                </div>

                <hr class="border-slate-100">

                <div class="flex justify-between text-lg font-bold text-blue-600 pt-1">
                    <span>Total Bayar</span>
                    <span>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

<!-- Aksi Transaksi (hanya untuk transaksi selesai) -->
    @if($transaction->status === 'completed')
    <div class="bg-white rounded-2xl border border-slate-300 shadow-md p-5">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-800">Aksi Transaksi</h3>
                <p class="text-sm text-slate-500 mt-1">Kelola pengembalian stok dan pembatalan transaksi.</p>
            </div>
            <div class="flex flex-wrap gap-2.5">
<!-- Void -->
                <form method="POST" action="{{ route('pos.transactions.void', $transaction->id) }}"
                      onsubmit="return confirm('Batalkan transaksi ini? Seluruh stok item akan dikembalikan ke inventory dan status menjadi Void.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-md"
                            style="background-color:#dc2626;color:#ffffff;border:2px solid #fecaca;">
                        <i class="fa-solid fa-ban"></i> Void Transaksi
                    </button>
                </form>

                <!-- Refund -->
                <form method="POST" action="{{ route('pos.transactions.refund', $transaction->id) }}"
                      onsubmit="return confirm('Proses refund penuh? Seluruh stok item akan kembali dan status menjadi Refund.')">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-md"
                            style="background-color:#f59e0b;color:#ffffff;border:2px solid #fde68a;">
                        <i class="fa-solid fa-rotate-left"></i> Refund
                    </button>
                </form>

                <!-- Retur -->
                <button type="button" onclick="openReturnModal()" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-bold transition shadow-md"
                        style="background-color:#9333ea;color:#ffffff;border:2px solid #e9d5ff;">
                    <i class="fa-solid fa-arrow-turn-up"></i> Retur
                </button>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- Modal Retur Parsial --}}
<div id="returnModal" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
        <div class="flex justify-between items-center mb-5">
            <h2 class="text-xl font-bold text-gray-800">Retur Item</h2>
            <button onclick="closeReturnModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <form method="POST" action="{{ route('pos.transactions.return', $transaction->id) }}">
            @csrf
            <p class="text-sm text-slate-500 mb-4">Pilih jumlah item yang akan dikembalikan. Stok akan ditambahkan kembali secara otomatis.</p>

            <div class="space-y-3">
                @foreach($transaction->details as $item)
                <div class="flex items-center justify-between gap-3 border border-slate-200 rounded-xl p-3">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate">{{ $item->product->name ?? 'Produk Dihapus' }}</p>
                        <p class="text-xs text-slate-400">Dibeli: {{ $item->quantity }}</p>
                    </div>
                    <div class="flex-shrink-0">
                        <input type="number" name="items[{{ $item->product_id }}][quantity]"
                               min="0" max="{{ $item->quantity }}" value="0"
                               class="w-24 border border-slate-300 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500 outline-none"
                               placeholder="Jumlah">
                    </div>
                </div>
                @endforeach
            </div>

            <div class="flex justify-end gap-2 mt-6">
                <button type="button" onclick="closeReturnModal()" class="border px-4 py-2 rounded-lg text-sm">Batal</button>
<button type="submit" class="text-white px-4 py-2 rounded-lg text-sm font-semibold" style="background-color:#9333ea;">Proses Retur</button>
            </div>
        </form>
    </div>
</div>

<script>
    function openReturnModal() {
        const modal = document.getElementById('returnModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeReturnModal() {
        const modal = document.getElementById('returnModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }
</script>
@endsection
