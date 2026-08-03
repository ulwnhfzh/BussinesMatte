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
                        <input type="text" id="searchProduct" placeholder="Cari nama atau kategori..." class="w-full pl-9 pr-4 py-2 text-sm rounded-xl border border-slate-200 outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
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
                        <button onclick="processCheckout()" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 active:bg-blue-800 transition shadow-md shadow-blue-200">
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
</form>

<script>
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

    function processCheckout() {
        const cartItems = document.querySelectorAll('.cart-item-enter');
        if (cartItems.length === 0) {
            showToast('❌ Keranjang masih kosong! Tambahkan produk terlebih dahulu.', 'error');
            return;
        }
        if (confirm('Konfirmasi transaksi & lanjutkan pembayaran?')) {
            document.getElementById('checkoutForm').submit();
        }
    }

    // Live Search Produk (Optimized via Data Attributes)
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

    // Toast Notification Function
    function showToast(message, type = 'success') {
        const colors = {
            success: 'bg-emerald-600',
            error: 'bg-rose-600'
        };
        const toast = document.createElement('div');
        toast.className = `fixed top-5 right-5 ${colors[type] || 'bg-blue-600'} text-white px-5 py-3 rounded-xl shadow-xl z-50 transition-all duration-300 transform translate-x-full text-sm font-medium flex items-center gap-2`;
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