# TODO - Fitur 7 & 8: Double Checkout + Retur/Refund/Void

## Langkah Implementasi

- [x] 1. Menyusun rencana dan mendapatkan persetujuan
- [x] 2. Membuat migrasi penambahan kolom `status` pada tabel `transactions`
- [x] 3. Memperbarui model `Transaction` (status + konstanta)
- [x] 4. Menambahkan proteksi double checkout pada `POSCashierController@checkout`
- [x] 5. Menambahkan method `voidTransaction`, `refundTransaction`, `returnTransaction`
- [x] 6. Menambahkan routes baru untuk void/refund/retur
- [x] 7. Memperbarui view `pos-cashier/index.blade.php` (disable tombol saat checkout)
- [x] 8. Memperbarui view `transactions/index.blade.php` (badge status)
- [x] 9. Memperbarui view `transactions/show.blade.php` (status + tombol aksi)
- [x] 10. Memperbarui view `transactions/print.blade.php` (tampilkan status)
- [x] 11. Menjalankan `php artisan migrate`
- [ ] 12. Menguji alur klik ganda dan void/refund/retur (perlu server berjalan)
