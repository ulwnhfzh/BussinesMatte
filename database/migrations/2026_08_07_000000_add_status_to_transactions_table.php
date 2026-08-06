<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Menambahkan kolom status pada transaksi.
     *
     * Status yang mungkin:
     * - completed : transaksi selesai dan stok sudah dikurangi.
     * - returned  : seluruh atau sebagian item dikembalikan (stok kembali).
     * - refunded  : pengembalian dana penuh (stok item kembali).
     * - voided    : transaksi dibatalkan (stok item kembali).
     */
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('status', 20)
                ->default('completed')
                ->after('payment_method');
        });
    }

    /**
     * Menghapus kolom status ketika migrasi dibatalkan.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};
