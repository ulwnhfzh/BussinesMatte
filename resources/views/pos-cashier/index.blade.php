@extends('layouts.app')
@section('title', 'POS Cashier - UsahaMate')
@section('content')
<style>
    .pos-product-card {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }
    .pos-product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.06);
    }
    .cart-item-enter {
        animation: slideIn 0.25s ease-out forwards;
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(15px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    .quantity-btn {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        border: 1px solid #e2e8f0;
        background: white;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.15s ease;
        font-weight: 600;
    }
    .quantity-btn:hover {
        background: #2563eb;
        color: white;
        border-color: #2563eb;
    }
    .quantity-btn.minus:hover {
        background: #ef4444;
        color: white;
        border-color: #ef4444;
    }
    /* ===== Payment Modal System ===== */
    .payment-overlay {
        position: fixed;
        inset: 0;
        background: rgba(15, 23, 42, 0.6);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 9998;
        display: none;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    .payment-overlay.active {
        display: flex;
        opacity: 1;
    }
    .payment-modal {
        background: white;
        border-radius: 1.5rem;
        box-shadow: 0 25px 60px rgba(0, 0, 0, 0.15), 0 0 0 1px rgba(0,0,0,0.04);
        width: 95%;
        max-width: 520px;
        max-height: 92vh;
        overflow-y: auto;
        transform: translateY(30px) scale(0.95);
        opacity: 0;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
    }
    .payment-overlay.active .payment-modal {
        transform: translateY(0) scale(1);
        opacity: 1;
    }
    .payment-modal-header {
        padding: 1.75rem 1.75rem 0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .payment-modal-header h3 {
        font-size: 1.25rem;
        font-weight: 800;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    .payment-modal-close {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        color: #64748b;
        font-size: 0.875rem;
    }
    .payment-modal-close:hover {
        background: #fee2e2;
        border-color: #fecaca;
        color: #ef4444;
    }
    .payment-modal-body {
        padding: 1.5rem 1.75rem 1.75rem;
    }
    .payment-total-badge {
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
        border: 1px solid #bfdbfe;
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .payment-total-badge .label {
        font-size: 0.8rem;
        color: #64748b;
        font-weight: 600;
    }
    .payment-total-badge .amount {
        font-size: 1.35rem;
        font-weight: 800;
        color: #1d4ed8;
    }
    /* Method Cards */
    .method-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 0.875rem;
    }
    .method-card {
        position: relative;
        border-radius: 1.25rem;
        padding: 1.5rem 1rem;
        text-align: center;
        cursor: pointer;
        border: 2px solid transparent;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        overflow: hidden;
    }
    .method-card::before {
        content: '';
        position: absolute;
        inset: 0;
        opacity: 0;
        transition: opacity 0.25s;
        border-radius: inherit;
    }
    .method-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(0,0,0,0.12);
    }
    .method-card:hover::before {
        opacity: 1;
    }
    .method-card:active {
        transform: translateY(-1px) scale(0.98);
    }
    .method-card .method-icon {
        width: 52px;
        height: 52px;
        border-radius: 1rem;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 0.75rem;
        font-size: 1.4rem;
        position: relative;
        z-index: 1;
    }
    .method-card .method-label {
        font-weight: 700;
        font-size: 0.85rem;
        position: relative;
        z-index: 1;
    }
    .method-card .method-sublabel {
        font-size: 0.7rem;
        margin-top: 0.25rem;
        position: relative;
        z-index: 1;
        opacity: 0.7;
    }
    /* Tunai Card */
    .method-card.tunai {
        background: linear-gradient(145deg, #ecfdf5, #d1fae5);
        border-color: #a7f3d0;
        color: #065f46;
    }
    .method-card.tunai .method-icon {
        background: #059669;
        color: white;
        box-shadow: 0 6px 16px rgba(5, 150, 105, 0.3);
    }
    .method-card.tunai:hover { border-color: #059669; }
    /* QRIS Card */
    .method-card.qris {
        background: linear-gradient(145deg, #eef2ff, #e0e7ff);
        border-color: #c7d2fe;
        color: #3730a3;
    }
    .method-card.qris .method-icon {
        background: #4f46e5;
        color: white;
        box-shadow: 0 6px 16px rgba(79, 70, 229, 0.3);
    }
    .method-card.qris:hover { border-color: #4f46e5; }
    /* E-Wallet Card */
    .method-card.ewallet {
        background: linear-gradient(145deg, #fff7ed, #ffedd5);
        border-color: #fed7aa;
        color: #9a3412;
    }
    .method-card.ewallet .method-icon {
        background: #ea580c;
        color: white;
        box-shadow: 0 6px 16px rgba(234, 88, 12, 0.3);
    }
    .method-card.ewallet:hover { border-color: #ea580c; }
    /* Quick Amount Buttons */
    .quick-amount-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 0.5rem;
        margin-bottom: 1rem;
    }
    .quick-amount-btn {
        padding: 0.6rem;
        border: 2px solid #e2e8f0;
        border-radius: 0.875rem;
        background: #f8fafc;
        font-weight: 700;
        font-size: 0.8rem;
        color: #334155;
        cursor: pointer;
        transition: all 0.2s;
        text-align: center;
    }
    .quick-amount-btn:hover {
        border-color: #059669;
        background: #ecfdf5;
        color: #059669;
    }
    .quick-amount-btn.active {
        border-color: #059669;
        background: #059669;
        color: white;
    }
    .quick-amount-btn.uang-pas {
        grid-column: span 3;
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border-color: #a7f3d0;
        color: #059669;
        font-size: 0.85rem;
    }
    .quick-amount-btn.uang-pas:hover,
    .quick-amount-btn.uang-pas.active {
        background: #059669;
        color: white;
        border-color: #059669;
    }
    /* Cash Input */
    .cash-input-wrapper {
        position: relative;
        margin-bottom: 1rem;
    }
    .cash-input-wrapper .prefix {
        position: absolute;
        left: 1rem;
        top: 50%;
        transform: translateY(-50%);
        font-weight: 700;
        color: #64748b;
        font-size: 1rem;
    }
    .cash-input {
        width: 100%;
        padding: 1rem 1rem 1rem 3rem;
        border: 2px solid #e2e8f0;
        border-radius: 1rem;
        font-size: 1.35rem;
        font-weight: 800;
        color: #1e293b;
        outline: none;
        transition: all 0.2s;
        background: #f8fafc;
    }
    .cash-input:focus {
        border-color: #059669;
        box-shadow: 0 0 0 4px rgba(5, 150, 105, 0.1);
        background: white;
    }
    /* Change Display */
    .change-display {
        border-radius: 1rem;
        padding: 1rem 1.25rem;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.3s;
    }
    .change-display.positive {
        background: linear-gradient(135deg, #ecfdf5, #d1fae5);
        border: 1px solid #a7f3d0;
    }
    .change-display.negative {
        background: linear-gradient(135deg, #fef2f2, #fee2e2);
        border: 1px solid #fecaca;
    }
    .change-display.neutral {
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }
    .change-display .change-label {
        font-size: 0.8rem;
        font-weight: 600;
    }
    .change-display .change-amount {
        font-size: 1.2rem;
        font-weight: 800;
    }
    .change-display.positive .change-label { color: #065f46; }
    .change-display.positive .change-amount { color: #059669; }
    .change-display.negative .change-label { color: #991b1b; }
    .change-display.negative .change-amount { color: #dc2626; }
    .change-display.neutral .change-label { color: #64748b; }
    .change-display.neutral .change-amount { color: #94a3b8; }
    /* Payment Submit Button */
    .payment-submit-btn {
        width: 100%;
        padding: 0.9rem;
        border: none;
        border-radius: 1rem;
        font-weight: 800;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.25s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .payment-submit-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
    }
    .payment-submit-btn.btn-emerald {
        background: linear-gradient(135deg, #059669, #047857);
        color: white;
        box-shadow: 0 6px 20px rgba(5, 150, 105, 0.3);
    }
    .payment-submit-btn.btn-emerald:not(:disabled):hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(5, 150, 105, 0.4);
    }
    .payment-submit-btn.btn-indigo {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        color: white;
        box-shadow: 0 6px 20px rgba(79, 70, 229, 0.3);
    }
    .payment-submit-btn.btn-indigo:not(:disabled):hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(79, 70, 229, 0.4);
    }
    .payment-submit-btn.btn-orange {
        background: linear-gradient(135deg, #ea580c, #c2410c);
        color: white;
        box-shadow: 0 6px 20px rgba(234, 88, 12, 0.3);
    }
    .payment-submit-btn.btn-orange:not(:disabled):hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 28px rgba(234, 88, 12, 0.4);
    }
    /* QRIS Section */
    .qris-code-box {
        background: white;
        border: 2px solid #e0e7ff;
        border-radius: 1.25rem;
        padding: 1.5rem;
        text-align: center;
        margin-bottom: 1.25rem;
    }
    .qris-placeholder {
        width: 200px;
        height: 200px;
        margin: 0 auto 1rem;
        background: linear-gradient(135deg, #f1f5f9, #e2e8f0);
        border-radius: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }
    .qris-placeholder::after {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent 30%, rgba(255,255,255,0.5) 50%, transparent 70%);
        animation: qrisScan 2s infinite;
    }
    @keyframes qrisScan {
        from { transform: translateY(-100%); }
        to { transform: translateY(100%); }
    }
    .qris-placeholder i {
        font-size: 3.5rem;
        color: #4f46e5;
        position: relative;
        z-index: 1;
    }
    .qris-placeholder span {
        font-size: 0.75rem;
        color: #64748b;
        margin-top: 0.5rem;
        font-weight: 600;
        position: relative;
        z-index: 1;
    }
    .qris-instructions {
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
        margin-bottom: 1.25rem;
    }
    .qris-step {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.6rem 0.875rem;
        background: #f8fafc;
        border-radius: 0.75rem;
        font-size: 0.8rem;
        color: #475569;
    }
    .qris-step .step-num {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #4f46e5;
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        font-weight: 800;
        flex-shrink: 0;
    }
    /* E-Wallet Provider List */
    .ewallet-grid {
        display: grid;
        gap: 0.625rem;
        margin-bottom: 1.25rem;
    }
    .ewallet-option {
        display: flex;
        align-items: center;
        gap: 1rem;
        padding: 0.875rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 1rem;
        cursor: pointer;
        transition: all 0.2s;
        background: white;
    }
    .ewallet-option:hover {
        border-color: #ea580c;
        background: #fff7ed;
    }
    .ewallet-option.selected {
        border-color: #ea580c;
        background: linear-gradient(135deg, #fff7ed, #ffedd5);
        box-shadow: 0 4px 12px rgba(234, 88, 12, 0.12);
    }
    .ewallet-option .ew-icon {
        width: 42px;
        height: 42px;
        border-radius: 0.75rem;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 0.7rem;
        flex-shrink: 0;
    }
    .ewallet-option .ew-name {
        font-weight: 700;
        font-size: 0.9rem;
        color: #1e293b;
    }
    .ewallet-option .ew-desc {
        font-size: 0.72rem;
        color: #94a3b8;
        margin-top: 0.1rem;
    }
    .ewallet-option .ew-check {
        margin-left: auto;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        border: 2px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
        font-size: 0.7rem;
    }
    .ewallet-option.selected .ew-check {
        background: #ea580c;
        border-color: #ea580c;
        color: white;
    }
    /* Back Button */
    .payment-back-btn {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        font-size: 0.8rem;
        font-weight: 600;
        color: #64748b;
        cursor: pointer;
        background: none;
        border: none;
        padding: 0.4rem 0;
        margin-bottom: 1rem;
        transition: color 0.2s;
    }
    .payment-back-btn:hover {
        color: #1e293b;
    }
    /* Divider */
    .modal-divider {
        height: 1px;
        background: #e2e8f0;
        margin: 0 1.75rem;
    }
</style>
<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-center md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">🛒 POS Cashier</h2>
            <p class="text-sm text-gray-500 mt-0.5 flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                Sistem Point of Sale • {{ date('d F Y H:i') }}
            </p>
        </div>
        <div class="flex flex-wrap gap-2.5">
            <a href="{{ route('pos.history') }}" class="rounded-xl border border-blue-200 bg-blue-50 px-4 py-2 text-sm font-semibold text-blue-600 shadow-sm transition hover:bg-blue-100 flex items-center gap-2">
                <i class="fas fa-history"></i> Riwayat
            </a>
            <button onclick="window.location.reload()" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                <i class="fas fa-sync-alt mr-1.5"></i>Refresh
            </button>
            <button onclick="clearCart()" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-600 shadow-sm transition hover:bg-red-100">
                <i class="fas fa-trash mr-1.5"></i>Kosongkan
            </button>
        </div>
    </div>
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <!-- Product List -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-5">
                    <h3 class="font-bold text-gray-800 text-lg">📦 Daftar Produk</h3>
                    <div class="relative w-full sm:w-64">
<i class="fas fa-search absolute left-3.5 top-3 text-gray-400 text-xs"></i>
                        <input type="text" id="searchProduct" placeholder="Cari nama atau kategori..." class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-h-[520px] overflow-y-auto pr-1">
                    @foreach($products as $product)
                        @php
                            $prodImg = $product->image ?? null;
                            if ($prodImg) {
                                $prodImgUrl = str_starts_with($prodImg, 'http') ? $prodImg : asset('storage/products/' . $prodImg);
                            } else {
                                $prodImgUrl = 'https://via.placeholder.com/60?text=No+Image';
                            }
                        @endphp
                        <div class="pos-product-card border border-slate-100 rounded-xl p-3.5 flex flex-col justify-between {{ $product->stock > 0 ? 'cursor-pointer hover:border-blue-300' : 'opacity-50 cursor-not-allowed bg-slate-50' }}"
                             data-product-name="{{ strtolower($product->name) }}"
                             data-product-category="{{ strtolower($product->category) }}"
                             @if($product->stock > 0) onclick="addToCart({{ $product->id }})" @endif>
                            
                            <div class="flex items-start gap-3">
                                <img src="{{ $prodImgUrl }}"
                                     onerror="this.onerror=null; this.src='https://via.placeholder.com/60?text=No+Image';"
                                     alt="{{ $product->name }}"
                                     class="w-14 h-14 rounded-lg object-cover flex-shrink-0 bg-slate-100">
                                
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-gray-800 text-sm truncate" title="{{ $product->name }}">{{ $product->name }}</h4>
                                    <p class="text-[11px] text-gray-400 truncate">{{ $product->category }}</p>
                                    <p class="text-[11px] text-gray-500 font-medium">Stok: {{ $product->stock }}</p>
                                    <p class="text-sm font-extrabold text-blue-600 mt-1">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                                </div>
                            </div>
                            <div class="mt-3 pt-2 border-t border-slate-50 flex items-center justify-between">
                                <span class="text-[10px] px-2 py-0.5 rounded-full font-semibold
                                    @if($product->stock > 10) bg-emerald-100 text-emerald-700
                                    @elseif($product->stock > 0) bg-amber-100 text-amber-700
                                    @else bg-rose-100 text-rose-700 @endif">
                                    @if($product->stock > 10) Tersedia
                                    @elseif($product->stock > 0) Stok Terbatas
                                    @else Habis @endif
                                </span>
                                @if($product->stock > 0)
                                <button type="button"
                                        class="w-7 h-7 rounded-lg bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center text-xs shadow-sm transition active:scale-90"
                                        title="Tambah ke Keranjang">
                                    <i class="fas fa-plus"></i>
                                </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        <!-- Cart Section -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col h-[600px]">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800 text-lg">🛍️ Keranjang</h3>
                    <span class="text-xs bg-blue-50 text-blue-600 border border-blue-200 px-2.5 py-1 rounded-full font-bold">
                        {{ count($cart) }} Item
                    </span>
                </div>
                <!-- Cart Items List -->
                <div class="flex-1 overflow-y-auto space-y-3 pr-1">
                    @if(empty($cart))
                    <div class="flex flex-col items-center justify-center h-full text-center py-12">
                        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-3">
                            <i class="fas fa-shopping-cart text-2xl text-slate-300"></i>
                        </div>
                        <p class="text-gray-500 font-medium text-sm">Keranjang masih kosong</p>
                        <p class="text-gray-400 text-xs mt-1">Pilih produk di sebelah kiri untuk ditambahkan</p>
                    </div>
                    @else
                    @foreach($cart as $id => $item)
                        @php
                            $cartImg = $item['image'] ?? null;
                            if ($cartImg) {
                                $cartImgUrl = str_starts_with($cartImg, 'http') ? $cartImg : asset('storage/products/' . $cartImg);
                            } else {
                                $cartImgUrl = 'https://via.placeholder.com/50?text=No+Img';
                            }
                        @endphp
                        <div class="cart-item-enter border border-slate-100 rounded-xl p-3 hover:border-slate-200 transition bg-slate-50/50">
                            <div class="flex gap-3">
                                <img src="{{ $cartImgUrl }}"
                                     onerror="this.onerror=null; this.src='https://via.placeholder.com/50?text=No+Img';"
                                     alt="{{ $item['name'] }}"
                                     class="w-12 h-12 rounded-lg object-cover flex-shrink-0 bg-white border border-slate-100">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="font-bold text-gray-800 text-sm truncate" title="{{ $item['name'] }}">{{ $item['name'] }}</p>
                                        <button onclick="removeFromCart({{ $id }})" class="text-slate-400 hover:text-rose-500 transition text-xs p-1">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </div>
                                    <p class="text-xs text-gray-500 mt-0.5">Rp {{ number_format($item['selling_price'], 0, ',', '.') }}</p>
                                    
                                    <div class="flex items-center justify-between mt-2">
                                        <div class="flex items-center gap-2">
                                            <button onclick="updateQuantity({{ $id }}, -1)" class="quantity-btn minus text-xs">−</button>
                                            <span class="font-bold text-sm w-6 text-center text-gray-800">{{ $item['quantity'] }}</span>
                                            <button onclick="updateQuantity({{ $id }}, 1)" class="quantity-btn text-xs">+</button>
                                        </div>
                                        <span class="text-xs font-bold text-gray-700">
                                            Rp {{ number_format($item['selling_price'] * $item['quantity'], 0, ',', '.') }}
                                        </span>
                                    </div>
                                    @if(!empty($item['note']))
                                    <p class="text-[10px] text-gray-500 italic mt-1.5 bg-white p-1 rounded border border-slate-100">📝 {{ $item['note'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                    @endif
                </div>
                <!-- Cart Summary -->
                <div class="border-t border-slate-100 pt-3 mt-2 space-y-1.5">
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Subtotal</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($subtotal ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Pajak (11%)</span>
                        <span class="font-semibold text-gray-800">Rp {{ number_format($tax ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-xs text-gray-500">
                        <span>Diskon Member</span>
                        <span class="font-semibold text-rose-600">-Rp {{ number_format($discount ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-base pt-2.5 border-t border-slate-100 items-baseline">
                        <span class="font-bold text-gray-800">Total</span>
                        <span class="font-extrabold text-blue-600 text-xl">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
                    </div>
<div class="pt-2">
                        <button type="button" id="checkoutBtn" onclick="processCheckout()" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 active:bg-blue-800 transition shadow-md shadow-blue-200">
                            <i class="fas fa-credit-card mr-2"></i>Bayar Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- Hidden Forms -->
<form id="addForm" method="POST" action="{{ route('pos.add') }}" class="hidden">
    @csrf
    <input type="hidden" name="product_id" id="addProductId">
    <input type="hidden" name="quantity" value="1">
</form>
<form id="updateForm" method="POST" action="{{ route('pos.update') }}" class="hidden">
    @csrf
    <input type="hidden" name="product_id" id="updateProductId">
    <input type="hidden" name="quantity" id="updateQuantity">
</form>
<form id="removeForm" method="POST" action="{{ route('pos.remove', ['id' => 0]) }}" class="hidden">
    @csrf
    @method('DELETE')
</form>
<form id="clearForm" method="POST" action="{{ route('pos.clear') }}" class="hidden">
    @csrf
</form>
<form id="checkoutForm" method="POST" action="{{ route('pos.checkout') }}" class="hidden">
    @csrf
    <input type="hidden" name="payment_method" id="paymentMethodInput" value="cash">
</form>
{{-- ============================================== --}}
{{-- MODAL 1: Pilih Metode Pembayaran               --}}
{{-- ============================================== --}}
<div id="modalPaymentMethod" class="payment-overlay">
    <div class="payment-modal">
        <div class="payment-modal-header">
            <h3>💳 Pilih Metode Pembayaran</h3>
            <button class="payment-modal-close" onclick="closeAllModals()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-divider" style="margin-top:1rem"></div>
        <div class="payment-modal-body">
            <div class="payment-total-badge">
                <span class="label">Total Pembayaran</span>
                <span class="amount" id="modalTotalAmount">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="method-grid">
                <div class="method-card tunai" onclick="openCashModal()">
                    <div class="method-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <div class="method-label">Tunai</div>
                    <div class="method-sublabel">Uang Cash</div>
                </div>
                <div class="method-card qris" onclick="openQrisModal()">
                    <div class="method-icon">
                        <i class="fas fa-qrcode"></i>
                    </div>
                    <div class="method-label">QRIS</div>
                    <div class="method-sublabel">Scan QR</div>
                </div>
                <div class="method-card ewallet" onclick="openEwalletModal()">
                    <div class="method-icon">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div class="method-label">E-Wallet</div>
                    <div class="method-sublabel">Dompet Digital</div>
                </div>
            </div>
        </div>
    </div>
</div>
{{-- ============================================== --}}
{{-- MODAL 2: Pembayaran Tunai                      --}}
{{-- ============================================== --}}
<div id="modalCash" class="payment-overlay">
    <div class="payment-modal">
        <div class="payment-modal-header">
            <h3>💵 Pembayaran Tunai</h3>
            <button class="payment-modal-close" onclick="closeAllModals()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-divider" style="margin-top:1rem"></div>
        <div class="payment-modal-body">
            <button class="payment-back-btn" onclick="backToMethodSelection()">
                <i class="fas fa-arrow-left"></i> Ganti Metode
            </button>
            <div class="payment-total-badge">
                <span class="label">Total Tagihan</span>
                <span class="amount" id="cashTotalAmount">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
            </div>
            <label style="font-size:0.8rem;font-weight:700;color:#475569;margin-bottom:0.5rem;display:block">Jumlah Uang Diterima</label>
            <div class="cash-input-wrapper">
                <span class="prefix">Rp</span>
                <input type="text" id="cashAmountInput" class="cash-input" placeholder="0" oninput="calculateChange()" autocomplete="off">
            </div>
            <div class="quick-amount-grid" id="quickAmountGrid">
                {{-- Quick amount buttons will be generated by JS --}}
            </div>
            <div id="changeDisplay" class="change-display neutral">
                <span class="change-label">Kembalian</span>
                <span class="change-amount">Rp 0</span>
            </div>
            <button id="cashSubmitBtn" class="payment-submit-btn btn-emerald" onclick="submitPayment('cash')" disabled>
                <i class="fas fa-check-circle"></i>
                Konfirmasi Pembayaran Tunai
            </button>
        </div>
    </div>
</div>
{{-- ============================================== --}}
{{-- MODAL 3: Pembayaran QRIS                       --}}
{{-- ============================================== --}}
<div id="modalQris" class="payment-overlay">
    <div class="payment-modal">
        <div class="payment-modal-header">
            <h3>📱 Pembayaran QRIS</h3>
            <button class="payment-modal-close" onclick="closeAllModals()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-divider" style="margin-top:1rem"></div>
        <div class="payment-modal-body">
            <button class="payment-back-btn" onclick="backToMethodSelection()">
                <i class="fas fa-arrow-left"></i> Ganti Metode
            </button>
            <div class="payment-total-badge" style="background:linear-gradient(135deg,#eef2ff,#e0e7ff);border-color:#c7d2fe">
                <span class="label">Total Pembayaran</span>
                <span class="amount" style="color:#4f46e5" id="qrisTotalAmount">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="qris-code-box">
                <div class="qris-placeholder">
                    <i class="fas fa-qrcode"></i>
                    <span>QRIS Code</span>
                </div>
                <p style="font-size:0.78rem;color:#64748b;font-weight:600">Scan kode QR di atas dengan aplikasi pembayaran</p>
            </div>
            <div class="qris-instructions">
                <div class="qris-step">
                    <span class="step-num">1</span>
                    <span>Buka aplikasi pembayaran (GoPay, OVO, DANA, dll)</span>
                </div>
                <div class="qris-step">
                    <span class="step-num">2</span>
                    <span>Pilih menu <strong>Scan QR / Pay</strong></span>
                </div>
                <div class="qris-step">
                    <span class="step-num">3</span>
                    <span>Arahkan kamera ke kode QR di atas</span>
                </div>
                <div class="qris-step">
                    <span class="step-num">4</span>
                    <span>Konfirmasi pembayaran di aplikasi Anda</span>
                </div>
            </div>
            <button class="payment-submit-btn btn-indigo" onclick="submitPayment('qris')">
                <i class="fas fa-check-circle"></i>
                Pembayaran QRIS Diterima
            </button>
        </div>
    </div>
</div>
{{-- ============================================== --}}
{{-- MODAL 4: Pembayaran E-Wallet                   --}}
{{-- ============================================== --}}
<div id="modalEwallet" class="payment-overlay">
    <div class="payment-modal">
        <div class="payment-modal-header">
            <h3>📲 Pembayaran E-Wallet</h3>
            <button class="payment-modal-close" onclick="closeAllModals()">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="modal-divider" style="margin-top:1rem"></div>
        <div class="payment-modal-body">
            <button class="payment-back-btn" onclick="backToMethodSelection()">
                <i class="fas fa-arrow-left"></i> Ganti Metode
            </button>
            <div class="payment-total-badge" style="background:linear-gradient(135deg,#fff7ed,#ffedd5);border-color:#fed7aa">
                <span class="label">Total Pembayaran</span>
                <span class="amount" style="color:#ea580c" id="ewalletTotalAmount">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
            </div>
            <label style="font-size:0.8rem;font-weight:700;color:#475569;margin-bottom:0.75rem;display:block">Pilih E-Wallet</label>
            <div class="ewallet-grid">
                <div class="ewallet-option" onclick="selectEwallet(this, 'gopay')">
                    <div class="ew-icon" style="background:#00AA13;color:white">Pay</div>
                    <div>
                        <div class="ew-name">GoPay</div>
                        <div class="ew-desc">Gojek Payment</div>
                    </div>
                    <div class="ew-check"><i class="fas fa-check"></i></div>
                </div>
                <div class="ewallet-option" onclick="selectEwallet(this, 'ovo')">
                    <div class="ew-icon" style="background:#4C3494;color:white">OVO</div>
                    <div>
                        <div class="ew-name">OVO</div>
                        <div class="ew-desc">OVO Cash</div>
                    </div>
                    <div class="ew-check"><i class="fas fa-check"></i></div>
                </div>
                <div class="ewallet-option" onclick="selectEwallet(this, 'dana')">
                    <div class="ew-icon" style="background:#108EE9;color:white">DANA</div>
                    <div>
                        <div class="ew-name">DANA</div>
                        <div class="ew-desc">DANA Indonesia</div>
                    </div>
                    <div class="ew-check"><i class="fas fa-check"></i></div>
                </div>
                <div class="ewallet-option" onclick="selectEwallet(this, 'shopeepay')">
                    <div class="ew-icon" style="background:#EE4D2D;color:white">SPay</div>
                    <div>
                        <div class="ew-name">ShopeePay</div>
                        <div class="ew-desc">Shopee Payment</div>
                    </div>
                    <div class="ew-check"><i class="fas fa-check"></i></div>
                </div>
                <div class="ewallet-option" onclick="selectEwallet(this, 'linkaja')">
                    <div class="ew-icon" style="background:#E82127;color:white">Link</div>
                    <div>
                        <div class="ew-name">LinkAja</div>
                        <div class="ew-desc">Link Aja Indonesia</div>
                    </div>
                    <div class="ew-check"><i class="fas fa-check"></i></div>
                </div>
            </div>
            <button id="ewalletSubmitBtn" class="payment-submit-btn btn-orange" onclick="submitPayment('e-wallet')" disabled>
                <i class="fas fa-check-circle"></i>
                Konfirmasi Pembayaran E-Wallet
            </button>
        </div>
    </div>
</div>
<script>
    // ===== Cart Operations =====
    function addToCart(productId) {
        document.getElementById('addProductId').value = productId;
        document.getElementById('addForm').submit();
    }
    function updateQuantity(productId, change) {
        document.getElementById('updateProductId').value = productId;
        document.getElementById('updateQuantity').value = change;
        document.getElementById('updateForm').submit();
    }
    function removeFromCart(productId) {
        const form = document.getElementById('removeForm');
        form.action = '{{ route("pos.remove", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', productId);
        form.submit();
    }
    function clearCart() {
        if (confirm('Apakah Anda yakin ingin mengosongkan seluruh keranjang?')) {
            document.getElementById('clearForm').submit();
        }
    }
    // ===== Payment Total (raw number) =====
    const paymentTotal = {{ $total ?? 0 }};
    // ===== Process Checkout - Open Payment Modal =====
    function processCheckout() {
        if (paymentTotal <= 0) {
            showToast('❌ Keranjang masih kosong! Tambahkan produk terlebih dahulu.', 'error');
            return;
        }
        openModal('modalPaymentMethod');
    }
// ===== Modal System =====
    // Token generasi modal untuk membatalkan timer penutupan yang tertunda.
    let modalGen = 0;

    function openModal(modalId) {
        // Batalkan SEMUA timer penutupan yang masih berjalan secara instan.
        modalGen++;

        // Tutup modal lain tanpa penundaan (langsung sembunyikan).
        document.querySelectorAll('.payment-overlay.active').forEach(m => {
            m.classList.remove('active');
            m.style.display = 'none';
        });

        const modal = document.getElementById(modalId);
        if (modal) {
            document.body.appendChild(modal);
            modal.style.display = 'flex';
            // Trigger reflow untuk animasi masuk
            modal.offsetHeight;
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }
    }

    function closeAllModals() {
        // Perbarui token agar timer lama yang tertunda menjadi tidak valid.
        modalGen++;
        const genAtClose = modalGen;

        document.querySelectorAll('.payment-overlay').forEach(m => {
            m.classList.remove('active');
            // Simpan referensi modal agar bisa diperiksa statusnya.
            const modalEl = m;

            setTimeout(() => {
                // Periksa secara ketat: hanya sembunyikan bila tidak ada modal
                // baru yang dibuka sejak penutupan dimulai (hal ini mencegah
                // timer lama menutup modal yang baru saja dibuka).
                if (genAtClose === modalGen && !modalEl.classList.contains('active')) {
                    modalEl.style.display = 'none';
                }
            }, 300);
        });

        document.body.style.overflow = '';
    }

    function backToMethodSelection() {
        openModal('modalPaymentMethod');
    }
    // Close modal on overlay click
    document.querySelectorAll('.payment-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeAllModals();
            }
        });
    });
    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
    // ===== Open Specific Payment Modals =====
    function openCashModal() {
        openModal('modalCash');
        document.getElementById('cashAmountInput').value = '';
        generateQuickAmounts();
        calculateChange();
        setTimeout(() => document.getElementById('cashAmountInput').focus(), 400);
    }
    function openQrisModal() {
        openModal('modalQris');
    }
    function openEwalletModal() {
        selectedEwallet = null;
        document.querySelectorAll('.ewallet-option').forEach(el => el.classList.remove('selected'));
        document.getElementById('ewalletSubmitBtn').disabled = true;
        openModal('modalEwallet');
    }
    // ===== Cash Payment Logic =====
    function formatRupiah(num) {
        return 'Rp ' + Math.round(num).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
    }
    function parseRupiahInput(str) {
        return parseInt(str.replace(/[^0-9]/g, ''), 10) || 0;
    }
    function generateQuickAmounts() {
        const grid = document.getElementById('quickAmountGrid');
        const total = paymentTotal;
        const quickAmounts = [];
        // Uang pas button
        quickAmounts.push({ label: '💰 Uang Pas — ' + formatRupiah(total), value: total, isPas: true });
        // Smart rounding amounts
        const roundings = [1000, 5000, 10000, 20000, 50000, 100000];
        const seen = new Set();
        seen.add(Math.ceil(total));
        for (const r of roundings) {
            const rounded = Math.ceil(total / r) * r;
            if (rounded > total && !seen.has(rounded)) {
                seen.add(rounded);
                quickAmounts.push({ label: formatRupiah(rounded), value: rounded, isPas: false });
            }
            const extra = rounded + r;
            if (extra > total && !seen.has(extra) && quickAmounts.filter(q => !q.isPas).length < 6) {
                seen.add(extra);
                quickAmounts.push({ label: formatRupiah(extra), value: extra, isPas: false });
            }
        }
        // Sort non-pas by value and take max 6
        const nonPas = quickAmounts.filter(q => !q.isPas).sort((a, b) => a.value - b.value).slice(0, 6);
        const pas = quickAmounts.filter(q => q.isPas);
        grid.innerHTML = '';
        pas.forEach(q => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'quick-amount-btn uang-pas';
            btn.textContent = q.label;
            btn.onclick = () => setQuickAmount(q.value, btn);
            grid.appendChild(btn);
        });
        nonPas.forEach(q => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'quick-amount-btn';
            btn.textContent = q.label;
            btn.onclick = () => setQuickAmount(q.value, btn);
            grid.appendChild(btn);
        });
    }
    function setQuickAmount(value, btnEl) {
        document.getElementById('cashAmountInput').value = Math.round(value).toLocaleString('id-ID');
        document.querySelectorAll('.quick-amount-btn').forEach(b => b.classList.remove('active'));
        if (btnEl) btnEl.classList.add('active');
        calculateChange();
    }
    function calculateChange() {
        const input = document.getElementById('cashAmountInput');
        const paid = parseRupiahInput(input.value);
        const change = paid - paymentTotal;
        const display = document.getElementById('changeDisplay');
        const submitBtn = document.getElementById('cashSubmitBtn');
        const label = display.querySelector('.change-label');
        const amount = display.querySelector('.change-amount');
        if (paid === 0) {
            display.className = 'change-display neutral';
            label.textContent = 'Kembalian';
            amount.textContent = 'Rp 0';
            submitBtn.disabled = true;
        } else if (change >= 0) {
            display.className = 'change-display positive';
            label.textContent = change === 0 ? '✅ Uang Pas' : '💰 Kembalian';
            amount.textContent = formatRupiah(change);
            submitBtn.disabled = false;
        } else {
            display.className = 'change-display negative';
            label.textContent = '⚠️ Kurang';
            amount.textContent = '- ' + formatRupiah(Math.abs(change));
            submitBtn.disabled = true;
        }
    }
    // Format input as user types
    document.getElementById('cashAmountInput')?.addEventListener('input', function() {
        let raw = this.value.replace(/[^0-9]/g, '');
        if (raw) {
            this.value = parseInt(raw, 10).toLocaleString('id-ID');
        }
        // Deactivate quick amount buttons
        document.querySelectorAll('.quick-amount-btn').forEach(b => b.classList.remove('active'));
        calculateChange();
    });
    // ===== E-Wallet Logic =====
    let selectedEwallet = null;
    function selectEwallet(el, provider) {
        selectedEwallet = provider;
        document.querySelectorAll('.ewallet-option').forEach(o => o.classList.remove('selected'));
        el.classList.add('selected');
        document.getElementById('ewalletSubmitBtn').disabled = false;
    }
// ===== Submit Payment =====
    let paymentSubmitting = false;

    function submitPayment(method) {
        if (paymentSubmitting) {
            return;
        }

        paymentSubmitting = true;

        // Disable semua tombol konfirmasi agar mencegah klik ganda.
        document.querySelectorAll('.payment-submit-btn').forEach(btn => {
            btn.disabled = true;
        });

        const checkoutBtn = document.getElementById('checkoutBtn');
        if (checkoutBtn) {
            checkoutBtn.disabled = true;
            checkoutBtn.innerHTML =
                '<i class="fas fa-spinner fa-spin mr-2"></i> Memproses...';
        }

        let paymentMethod = method;
        if (method === 'e-wallet' && selectedEwallet) {
            paymentMethod = 'e-wallet';
        }
        document.getElementById('paymentMethodInput').value = paymentMethod;
        document.getElementById('checkoutForm').submit();
    }
    // Cegah resubmit ganda pada form checkout (back/forward cache).
    window.addEventListener('pageshow', function (event) {
        if (event.persisted && paymentSubmitting) {
            paymentSubmitting = false;
            const checkoutBtn = document.getElementById('checkoutBtn');
            if (checkoutBtn) {
                checkoutBtn.disabled = false;
                checkoutBtn.innerHTML =
                    '<i class="fas fa-credit-card mr-2"></i>Bayar Sekarang';
            }
        }
    });
    // ===== Live Search Produk =====
    document.getElementById('searchProduct')?.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        const products = document.querySelectorAll('.pos-product-card');
        products.forEach(product => {
            const name = product.getAttribute('data-product-name') || '';
            const category = product.getAttribute('data-product-category') || '';
            if (name.includes(query) || category.includes(query)) {
                product.classList.remove('hidden');
            } else {
                product.classList.add('hidden');
            }
        });
    });
    // ===== Toast Notification =====
    function showToast(message, type = 'success') {
        const colors = {
            success: 'bg-emerald-600',
            error: 'bg-rose-600'
        };
        const toast = document.createElement('div');
        toast.className = `fixed top-5 right-5 ${colors[type] || 'bg-blue-600'} text-white px-5 py-3 rounded-xl shadow-xl z-[99999] transition-all duration-300 transform translate-x-full text-sm font-medium flex items-center gap-2`;
        toast.innerHTML = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
            toast.classList.add('translate-x-0');
        }, 50);
        setTimeout(() => {
            toast.classList.remove('translate-x-0');
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }
</script>
<!-- Flash Messages -->
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        showToast('✅ {{ session('success') }}', 'success');
    });
</script>
@endif
@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        showToast('❌ {{ session('error') }}', 'error');
    });
</script>
@endif
@endsection