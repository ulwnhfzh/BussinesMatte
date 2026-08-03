<?php

namespace App\Services;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockMovementService
{
    /**
     * Mencatat stok awal ketika produk baru dibuat.
     *
     * Method ini hanya membuat riwayat karena stok produk
     * sudah disimpan oleh ProductController.
     */
    public function recordInitialStock(
        Product $product,
        ?string $note = null
    ): ?StockMovement {
        $businessId = $this->getCurrentBusinessId();

        $this->ensureProductBelongsToBusiness($product, $businessId);

        $initialStock = (int) $product->stock;

        // Stok nol tidak perlu menghasilkan aktivitas stok.
        if ($initialStock === 0) {
            return null;
        }

        return $this->createMovement(
            product: $product,
            businessId: $businessId,
            type: StockMovement::TYPE_INITIAL,
            quantity: $initialStock,
            stockBefore: 0,
            stockAfter: $initialStock,
            reference: null,
            note: $note ?? 'Stok awal ketika produk dibuat.'
        );
    }

    /**
     * Menambah atau mengurangi stok secara otomatis.
     *
     * Contoh:
     * Penjualan 2 barang menggunakan quantity -2.
     * Restok 10 barang menggunakan quantity 10.
     */
    public function changeStock(
        Product $product,
        string $type,
        int $quantity,
        ?Model $reference = null,
        ?string $note = null
    ): StockMovement {
        $businessId = $this->getCurrentBusinessId();

        $this->validateMovementType($type);
        $this->validateQuantityDirection($type, $quantity);

        return DB::transaction(function () use (
            $product,
            $businessId,
            $type,
            $quantity,
            $reference,
            $note
        ) {
            /*
             * Mengambil ulang produk dan menguncinya.
             * Tujuannya mencegah dua transaksi mengubah stok
             * produk yang sama pada waktu bersamaan.
             */
            $lockedProduct = Product::where('business_id', $businessId)
                ->lockForUpdate()
                ->findOrFail($product->id);

            $stockBefore = (int) $lockedProduct->stock;
            $stockAfter = $stockBefore + $quantity;

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'stock' => 'Stok tidak mencukupi untuk aktivitas ini.',
                ]);
            }

            $lockedProduct->stock = $stockAfter;
            $lockedProduct->save();

            return $this->createMovement(
                product: $lockedProduct,
                businessId: $businessId,
                type: $type,
                quantity: $quantity,
                stockBefore: $stockBefore,
                stockAfter: $stockAfter,
                reference: $reference,
                note: $note
            );
        });
    }

    /**
     * Mengubah stok menjadi jumlah tertentu.
     *
     * Digunakan saat stok diubah melalui edit produk
     * atau fitur stock opname.
     */
    public function adjustStock(
        Product $product,
        int $newStock,
        ?string $note = null
    ): ?StockMovement {
        if ($newStock < 0) {
            throw ValidationException::withMessages([
                'stock' => 'Stok tidak boleh kurang dari 0.',
            ]);
        }

        $businessId = $this->getCurrentBusinessId();

        return DB::transaction(function () use (
            $product,
            $businessId,
            $newStock,
            $note
        ) {
            $lockedProduct = Product::where('business_id', $businessId)
                ->lockForUpdate()
                ->findOrFail($product->id);

            $stockBefore = (int) $lockedProduct->stock;
            $quantity = $newStock - $stockBefore;

            // Tidak membuat riwayat jika jumlah stok tidak berubah.
            if ($quantity === 0) {
                return null;
            }

            $lockedProduct->stock = $newStock;
            $lockedProduct->save();

            return $this->createMovement(
                product: $lockedProduct,
                businessId: $businessId,
                type: StockMovement::TYPE_ADJUSTMENT,
                quantity: $quantity,
                stockBefore: $stockBefore,
                stockAfter: $newStock,
                reference: null,
                note: $note ?? 'Penyesuaian stok melalui edit produk.'
            );
        });
    }

    /**
     * Membuat data riwayat perubahan stok.
     */
    private function createMovement(
        Product $product,
        int $businessId,
        string $type,
        int $quantity,
        int $stockBefore,
        int $stockAfter,
        ?Model $reference,
        ?string $note
    ): StockMovement {
        return StockMovement::create([
            'business_id' => $businessId,
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'product_code' => $product->product_code,
            'product_name' => $product->name,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => $reference?->getMorphClass(),
            'reference_id' => $reference?->getKey(),
            'note' => $note,
        ]);
    }

    /**
     * Mendapatkan business_id user yang sedang login.
     */
    private function getCurrentBusinessId(): int
    {
        $user = Auth::user();

        if (!$user || !$user->business_id) {
            throw new AuthorizationException(
                'Business pengguna tidak ditemukan.'
            );
        }

        return (int) $user->business_id;
    }

    /**
     * Memastikan produk merupakan milik bisnis user.
     */
    private function ensureProductBelongsToBusiness(
        Product $product,
        int $businessId
    ): void {
        if ((int) $product->business_id !== $businessId) {
            throw new AuthorizationException(
                'Produk bukan milik business yang sedang login.'
            );
        }
    }

    /**
     * Memastikan jenis aktivitas stok dikenali sistem.
     */
    private function validateMovementType(string $type): void
    {
        $allowedTypes = [
            StockMovement::TYPE_SALE,
            StockMovement::TYPE_PURCHASE,
            StockMovement::TYPE_ADJUSTMENT,
            StockMovement::TYPE_RETURN,
        ];

        if (!in_array($type, $allowedTypes, true)) {
            throw ValidationException::withMessages([
                'type' => 'Jenis aktivitas stok tidak valid.',
            ]);
        }
    }

    /**
     * Memastikan arah jumlah sesuai jenis aktivitas.
     */
    private function validateQuantityDirection(
        string $type,
        int $quantity
    ): void {
        if ($quantity === 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah perubahan stok tidak boleh 0.',
            ]);
        }

        if (
            $type === StockMovement::TYPE_SALE
            && $quantity > 0
        ) {
            throw ValidationException::withMessages([
                'quantity' => 'Jumlah penjualan harus mengurangi stok.',
            ]);
        }

        if (
            in_array(
                $type,
                [
                    StockMovement::TYPE_PURCHASE,
                    StockMovement::TYPE_RETURN,
                ],
                true
            )
            && $quantity < 0
        ) {
            throw ValidationException::withMessages([
                'quantity' => 'Stok masuk dan retur harus menambah stok.',
            ]);
        }
    }
}