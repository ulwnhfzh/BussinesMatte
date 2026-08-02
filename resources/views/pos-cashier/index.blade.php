@extends('layouts.app')

@section('title', 'POS Cashier - UsahaMate')

@section('content')
<style>
    .pos-product-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 24px rgba(0,0,0,0.1);
    }
    .pos-product-card {
        transition: all 0.3s ease;
    }
    .cart-item-enter {
        animation: slideIn 0.3s ease;
    }
    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(20px);
        }
        to {
            opacity: 1;
            transform: translateX(0);
        }
    }
    .quantity-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        border: 1px solid #e2e8f0;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.2s;
        font-weight: bold;
    }
    .quantity-btn:hover {
        background: #2563eb;
        color: white;
        border-color: #2563eb;
    }
    .quantity-btn.minus:hover {
        background: #ef4444;
        border-color: #ef4444;
    }
</style>

<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-3 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm md:flex-row md:items-end md:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">🛒 POS Cashier</h2>
            <p class="text-sm text-gray-500 mt-1 flex items-center gap-2">
                <span class="inline-block w-2 h-2 rounded-full bg-green-500"></span>
                Sistem Point of Sale • {{ date('d F Y H:i') }}
            </p>
        </div>
        <div class="flex gap-3">
            <button onclick="window.location.reload()" class="rounded-xl border border-slate-200 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm transition hover:bg-slate-50">
                <i class="fas fa-sync-alt mr-2"></i>Refresh
            </button>
            <button onclick="clearCart()" class="rounded-xl border border-red-200 bg-red-50 px-4 py-2 text-sm font-medium text-red-600 shadow-sm transition hover:bg-red-100">
                <i class="fas fa-trash mr-2"></i>Kosongkan
            </button>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">
        <!-- Product List -->
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-bold text-gray-800">📦 Daftar Produk</h3>
                    <div class="flex gap-2">
                        <input type="text" id="searchProduct" placeholder="Cari produk..." class="rounded-xl border border-slate-200 px-4 py-1.5 text-sm outline-none focus:border-blue-300 focus:ring-2 focus:ring-blue-100">
                    </div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 max-h-[500px] overflow-y-auto pr-2">
                    @foreach($products as $product)
                    <div class="pos-product-card border border-gray-100 rounded-xl p-4 {{ $product->stock > 0 ? 'cursor-pointer hover:border-blue-300' : 'opacity-50 cursor-not-allowed bg-gray-50' }}"
                         data-product-id="{{ $product->id }}"
                         @if($product->stock > 0) onclick="addToCart({{ $product->id }})" @endif>
                        <div class="flex items-start gap-3">
                            <img src="{{ $product->image ? asset('storage/products/' . $product->image) : 'https://via.placeholder.com/60' }}"
                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/60';"
                                 alt="{{ $product->name }}"
                                 class="w-14 h-14 rounded-lg object-cover flex-shrink-0">
                            <div class="flex-1 min-w-0">
                                <h4 class="font-bold text-gray-800 text-sm truncate" title="{{ $product->name }}">{{ $product->name }}</h4>
                                <p class="text-[10px] text-gray-400">{{ $product->category }}</p>
                                <p class="text-[10px] text-gray-400">Stok: {{ $product->stock }}</p>
                                <p class="text-sm font-bold text-blue-600 mt-1">Rp {{ number_format($product->selling_price, 0, ',', '.') }}</p>
                            </div>
                        </div>
                        <div class="mt-3 flex items-center justify-between">
                            <span class="text-[10px] px-2 py-0.5 rounded-full font-medium
                                @if($product->stock > 10) bg-green-100 text-green-700
                                @elseif($product->stock > 0) bg-yellow-100 text-yellow-700
                                @else bg-red-100 text-red-700 @endif">
                                @if($product->stock > 10) Tersedia
                                @elseif($product->stock > 0) Stok Terbatas
                                @else Habis @endif
                            </span>

                            @if($product->stock > 0)
                            <button type="button"
                                    onclick="event.stopPropagation(); addToCart({{ $product->id }});"
                                    class="w-7 h-7 rounded-lg bg-blue-600 hover:bg-blue-700 text-white flex items-center justify-center text-xs shadow-sm transition-transform active:scale-95"
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
                    <h3 class="font-bold text-gray-800">🛍️ Keranjang</h3>
                    <span class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-full font-medium">
                        {{ count($cart) }} item
                    </span>
                </div>

                <!-- Cart Items -->
                <div class="flex-1 overflow-y-auto space-y-3 pr-2">
                    @if(empty($cart))
                    <div class="text-center py-12">
                        <i class="fas fa-shopping-cart text-4xl text-gray-300 mb-3"></i>
                        <p class="text-gray-400 text-sm">Keranjang kosong</p>
                        <p class="text-gray-400 text-xs">Klik produk untuk menambahkan</p>
                    </div>
                    @else
                    @foreach($cart as $id => $item)
                    <div class="cart-item-enter border border-gray-100 rounded-xl p-3 hover:shadow-sm transition">
                        <div class="flex gap-3">
                            <!-- PERBAIKAN LOGIKA GAMBAR KERANJANG -->
                            @php
                                $cartImg = $item['image'] ?? null;
                                if ($cartImg) {
                                    $cartImgUrl = str_starts_with($cartImg, 'http') ? $cartImg : asset('storage/products/' . $cartImg);
                                } else {
                                    $cartImgUrl = 'https://via.placeholder.com/50';
                                }
                            @endphp
                            <img src="{{ $cartImgUrl }}"
                                 onerror="this.onerror=null; this.src='https://via.placeholder.com/50';"
                                 alt="{{ $item['name'] }}"
                                 class="w-12 h-12 rounded-lg object-cover flex-shrink-0">

                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <p class="font-bold text-gray-800 text-sm truncate" title="{{ $item['name'] }}">{{ $item['name'] }}</p>
                                    <button onclick="removeFromCart({{ $id }})" class="text-gray-400 hover:text-red-500 transition text-xs">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                                <p class="text-xs text-gray-500">Rp {{ number_format($item['selling_price'], 0, ',', '.') }}</p>
                                <div class="flex items-center gap-2 mt-1">
                                    <button onclick="updateQuantity({{ $id }}, -1)" class="quantity-btn minus text-sm">−</button>
                                    <span class="font-bold text-sm w-8 text-center">{{ $item['quantity'] }}</span>
                                    <button onclick="updateQuantity({{ $id }}, 1)" class="quantity-btn text-sm">+</button>
                                    <span class="text-xs text-gray-400 ml-1">= Rp {{ number_format($item['selling_price'] * $item['quantity'], 0, ',', '.') }}</span>
                                </div>
                                @if(!empty($item['note']))
                                <p class="text-[10px] text-gray-400 italic mt-1">📝 {{ $item['note'] }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @endif
                </div>

                <!-- Cart Summary -->
                <div class="border-t border-gray-100 pt-4 mt-2 space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Subtotal</span>
                        <span class="font-bold text-gray-800">Rp {{ number_format($subtotal ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Pajak (11%)</span>
                        <span class="font-bold text-gray-800">Rp {{ number_format($tax ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Diskon Member</span>
                        <span class="font-bold text-red-600">-Rp {{ number_format($discount ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-lg pt-2 border-t border-gray-200">
                        <span class="font-bold text-gray-800">Total</span>
                        <span class="font-bold text-blue-600 text-xl">Rp {{ number_format($total ?? 0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex gap-3 mt-3">
                        <button onclick="processCheckout()" class="flex-1 bg-blue-600 text-white py-2.5 rounded-xl font-bold hover:bg-blue-700 transition">
                            <i class="fas fa-credit-card mr-2"></i>Bayar Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Form Hidden -->
<form id="addForm" method="POST" action="{{ route('pos.add') }}" style="display:none;">
    @csrf
    <input type="hidden" name="product_id" id="addProductId">
    <input type="hidden" name="quantity" value="1">
</form>

<form id="updateForm" method="POST" action="{{ route('pos.update') }}" style="display:none;">
    @csrf
    <input type="hidden" name="product_id" id="updateProductId">
    <input type="hidden" name="quantity" id="updateQuantity">
</form>

<form id="removeForm" method="POST" action="{{ route('pos.remove', ['id' => 0]) }}" style="display:none;">
    @csrf
    @method('DELETE')
</form>

<form id="clearForm" method="POST" action="{{ route('pos.clear') }}" style="display:none;">
    @csrf
</form>

<form id="checkoutForm" method="POST" action="{{ route('pos.checkout') }}" style="display:none;">
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
        if (confirm('Yakin ingin mengosongkan keranjang?')) {
            document.getElementById('clearForm').submit();
        }
    }

    function processCheckout() {
        const cartItems = document.querySelectorAll('.cart-item-enter');
        if (cartItems.length === 0) {
            alert('Keranjang kosong! Tambahkan produk terlebih dahulu.');
            return;
        }
        if (confirm('Konfirmasi pembayaran?')) {
            document.getElementById('checkoutForm').submit();
        }
    }

    // Live Search Produk
    document.getElementById('searchProduct')?.addEventListener('keyup', function() {
        const search = this.value.toLowerCase();
        const products = document.querySelectorAll('.pos-product-card');
        products.forEach(product => {
            const name = product.querySelector('h4')?.textContent?.toLowerCase() || '';
            const category = product.querySelector('p.text-\\[10px\\]')?.textContent?.toLowerCase() || '';
            if (name.includes(search) || category.includes(search)) {
                product.style.display = 'block';
            } else {
                product.style.display = 'none';
            }
        });
    });
</script>

<!-- Toast Notifications -->
@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('✅ {{ session('success') }}', 'success');
    });
</script>
@endif

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        showToast('❌ {{ session('error') }}', 'error');
    });
</script>
@endif

<script>
    function showToast(message, type = 'success') {
        const colors = {
            success: 'bg-green-500',
            error: 'bg-red-500'
        };
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 ${colors[type] || 'bg-blue-500'} text-white px-6 py-3 rounded-xl shadow-lg z-50 transition-all duration-500 transform translate-x-full`;
        toast.innerHTML = message;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.classList.remove('translate-x-full');
            toast.classList.add('translate-x-0');
        }, 100);
        setTimeout(() => {
            toast.classList.remove('translate-x-0');
            toast.classList.add('translate-x-full');
            setTimeout(() => toast.remove(), 500);
        }, 3000);
    }
</script>
@endsection