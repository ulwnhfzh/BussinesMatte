<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel riwayat perubahan stok.
     */
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            // Pemisah data antar bisnis atau tenant.
            $table->foreignId('business_id')
                ->constrained('businesses')
                ->cascadeOnDelete();

            // Produk boleh terhapus, tetapi riwayat stok tetap disimpan.
            $table->foreignId('product_id')
                ->nullable()
                ->constrained('products')
                ->nullOnDelete();

            // User pencatat boleh terhapus tanpa menghapus riwayat stok.
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Snapshot identitas produk saat transaksi terjadi.
            $table->string('product_code', 50)->nullable();
            $table->string('product_name');

            // Jenis aktivitas: initial, sale, purchase, adjustment, atau return.
            $table->string('type', 30);

            /*
             * Perubahan jumlah stok:
             * nilai positif  = stok bertambah
             * nilai negatif  = stok berkurang
             */
            $table->integer('quantity');

            // Kondisi stok sebelum dan setelah aktivitas.
            $table->unsignedInteger('stock_before');
            $table->unsignedInteger('stock_after');

            /*
             * Referensi sumber perubahan stok.
             * Contoh: reference_type = transaction
             *         reference_id   = ID transaksi POS
             */
            $table->string('reference_type', 100)->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();

            // Keterangan tambahan untuk aktivitas stok.
            $table->text('note')->nullable();

            $table->timestamps();

            // Mempercepat pencarian berdasarkan bisnis dan waktu.
            $table->index(['business_id', 'created_at']);

            // Mempercepat pencarian riwayat per produk.
            $table->index(['business_id', 'product_id']);

            // Mempercepat pencarian berdasarkan transaksi sumber.
            $table->index(['reference_type', 'reference_id']);
        });
    }

    /**
     * Menghapus tabel ketika migration dibatalkan.
     */
    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};