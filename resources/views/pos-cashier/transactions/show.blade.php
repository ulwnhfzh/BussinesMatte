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
</div>
@endsection