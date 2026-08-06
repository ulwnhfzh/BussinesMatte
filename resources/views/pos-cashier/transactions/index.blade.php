@extends('layouts.app')

@section('title', 'Riwayat Transaksi - UsahaMate')

@section('content')
<div class="space-y-6">
    <!-- Header Page -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Riwayat Transaksi POS</h2>
            <p class="text-sm text-slate-500">Cari, tinjau, dan cetak ulang bukti transaksi penjualan.</p>
        </div>
        <a href="{{ route('pos.cashier') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-semibold transition shadow-sm w-fit">
            <i class="fa-solid fa-cash-register"></i> Buka Kasir POS
        </a>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-2xl p-5 border border-slate-200 shadow-sm">
        <form action="{{ route('pos.transactions.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Cari Invoice / Kasir</label>
                <div class="relative flex items-center">
                    <i class="fa-solid fa-magnifying-glass absolute left-3.5 text-slate-400 text-xs pointer-events-none"></i>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="TRX-2026..." class="w-full pl-10 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Dari Tanggal</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Sampai Tanggal</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-end gap-2">
                <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm rounded-xl transition">
                    Filter
                </button>
                <a href="{{ route('pos.transactions.index') }}" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600 font-semibold text-sm rounded-xl transition">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Table Section -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-xs uppercase font-semibold text-slate-500 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4">Waktu</th>
<th class="px-6 py-4">No. Invoice</th>
                        <th class="px-6 py-4">Kasir</th>
                        <th class="px-6 py-4">Metode Bayar</th>
                        <th class="px-6 py-4">Status</th>
                        <th class="px-6 py-4 text-right">Total Transaksi</th>
                        <th class="px-6 py-4 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-slate-50/80 transition">
                        <td class="px-6 py-4 whitespace-nowrap text-xs text-slate-500">
                            {{ $trx->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap font-bold text-blue-600">
                            {{ $trx->invoice_number }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ $trx->user->name ?? 'Kasir' }}
                        </td>
<td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-block px-2.5 py-1 text-[11px] font-bold rounded-lg bg-slate-100 text-slate-700 uppercase">
                                {{ $trx->payment_method }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $statusClass = match ($trx->status) {
                                    'voided' => 'bg-red-100 text-red-700',
                                    'refunded' => 'bg-amber-100 text-amber-700',
                                    'returned' => 'bg-purple-100 text-purple-700',
                                    default => 'bg-emerald-100 text-emerald-700',
                                };
                            @endphp
                            <span class="inline-block px-2.5 py-1 text-[11px] font-bold rounded-lg {{ $statusClass }}">
                                {{ $trx->status_label }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right font-bold text-slate-800">
                            Rp {{ number_format($trx->total_amount, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('pos.transactions.show', $trx->id) }}" class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition" title="Lihat Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('pos.transactions.print', $trx->id) }}" target="_blank" class="p-2 text-slate-600 hover:bg-slate-100 rounded-lg transition" title="Cetak Struk">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
<td colspan="7" class="px-6 py-8 text-center text-slate-400">
                            Belum ada riwayat transaksi.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
        <div class="p-4 border-t border-slate-100">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>
</div>
@endsection