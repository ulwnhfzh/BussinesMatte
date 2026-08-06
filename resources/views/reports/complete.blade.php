<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Lengkap BusinessMate</title>

    <style>
        @page {
            margin: 22px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #0f172a;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        .header {
            width: 100%;
            margin-bottom: 16px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
        }

        .header td {
            vertical-align: top;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            color: #1d4ed8;
        }

        .business-name {
            margin-top: 4px;
            font-size: 12px;
            font-weight: bold;
        }

        .muted {
            color: #64748b;
        }

        .text-right {
            text-align: right;
        }

        .section {
            margin-top: 14px;
        }

        .page-break {
            page-break-before: always;
        }

        .section-title {
            padding: 8px 10px;
            margin-bottom: 10px;
            background: #1d4ed8;
            color: white;
            font-size: 13px;
            font-weight: bold;
        }

        .summary-table {
            width: 100%;
            border-spacing: 5px;
            margin: 0 -5px 12px;
        }

        .summary-card {
            padding: 9px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
        }

        .summary-label {
            font-size: 7px;
            color: #64748b;
            text-transform: uppercase;
        }

        .summary-value {
            margin-top: 3px;
            font-size: 11px;
            font-weight: bold;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            padding: 6px 4px;
            background: #2563eb;
            color: white;
            font-size: 6.5px;
            text-align: left;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 6px 4px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .center {
            text-align: center;
        }

        .money {
            text-align: right;
            white-space: nowrap;
        }

        .positive {
            color: #059669;
            font-weight: bold;
        }

        .negative {
            color: #dc2626;
            font-weight: bold;
        }

        .empty-state {
            padding: 24px;
            border: 1px solid #e2e8f0;
            color: #64748b;
            text-align: center;
        }

        .note-box {
            padding: 10px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
            line-height: 1.5;
        }

        .footer {
            margin-top: 14px;
            padding-top: 7px;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
        }
    </style>
</head>

<body>
    @php
        $predictionProducts = collect($prediction['products'] ?? []);

        $restockProducts = $predictionProducts->filter(function ($product) {
            return (int) ($product['recommended_restock'] ?? 0) > 0;
        });

        $totalRestockCost = $restockProducts->sum('estimated_cost');

        $movementLabels = [
            'initial' => 'Stok Awal',
            'adjustment' => 'Penyesuaian',
            'sale' => 'Penjualan',
            'restock' => 'Restok',
            'return' => 'Retur',
            'refund' => 'Refund',
            'void' => 'Void',
        ];

        $paymentLabels = [
            'cash' => 'Tunai',
            'transfer' => 'Transfer',
            'qris' => 'QRIS',
            'debit' => 'Kartu Debit',
            'credit' => 'Kartu Kredit',
        ];
    @endphp

    {{-- HALAMAN RINGKASAN --}}
    <table class="header">
        <tr>
            <td>
                <div class="title">BusinessMate</div>
                <div class="business-name">{{ $businessName }}</div>
                <div class="muted">Laporan Bisnis Lengkap</div>
            </td>

            <td class="text-right">
                <strong>{{ $periodLabel }}</strong><br>
                {{ $startDate->format('d/m/Y') }}
                sampai
                {{ $endDate->format('d/m/Y') }}<br>
                <span class="muted">
                    Dibuat {{ $generatedAt->format('d/m/Y H:i') }} WIB
                </span>
            </td>
        </tr>
    </table>

    <div class="section-title">Ringkasan Eksekutif</div>

    <table class="summary-table">
        <tr>
            <td width="20%">
                <div class="summary-card">
                    <div class="summary-label">Total Produk</div>
                    <div class="summary-value">
                        {{ number_format($inventory['summary']['total_products']) }}
                    </div>
                </div>
            </td>

            <td width="20%">
                <div class="summary-card">
                    <div class="summary-label">Nilai Inventory</div>
                    <div class="summary-value">
                        Rp {{ number_format(
                            $inventory['summary']['inventory_value'],
                            0,
                            ',',
                            '.'
                        ) }}
                    </div>
                </div>
            </td>

            <td width="20%">
                <div class="summary-card">
                    <div class="summary-label">Transaksi</div>
                    <div class="summary-value">
                        {{ number_format($sales['summary']['total_transactions']) }}
                    </div>
                </div>
            </td>

            <td width="20%">
                <div class="summary-card">
                    <div class="summary-label">Pendapatan</div>
                    <div class="summary-value">
                        Rp {{ number_format(
                            $sales['summary']['total_revenue'],
                            0,
                            ',',
                            '.'
                        ) }}
                    </div>
                </div>
            </td>

            <td width="20%">
                <div class="summary-card">
                    <div class="summary-label">Laba</div>
                    <div class="summary-value">
                        Rp {{ number_format(
                            $sales['summary']['total_profit'],
                            0,
                            ',',
                            '.'
                        ) }}
                    </div>
                </div>
            </td>
        </tr>

        <tr>
            <td>
                <div class="summary-card">
                    <div class="summary-label">Stok Kritis</div>
                    <div class="summary-value">
                        {{ number_format(
                            $inventory['summary']['critical_products']
                        ) }}
                    </div>
                </div>
            </td>

            <td>
                <div class="summary-card">
                    <div class="summary-label">Pergerakan Stok</div>
                    <div class="summary-value">
                        {{ number_format(
                            $stockMovements['summary']['total_movements']
                        ) }}
                    </div>
                </div>
            </td>

            <td>
                <div class="summary-card">
                    <div class="summary-label">Produk Terjual</div>
                    <div class="summary-value">
                        {{ number_format(
                            $sales['summary']['total_items_sold']
                        ) }}
                    </div>
                </div>
            </td>

            <td>
                <div class="summary-card">
                    <div class="summary-label">Prediksi Permintaan</div>
                    <div class="summary-value">
                        {{ number_format($prediction['predicted_total'] ?? 0) }}
                    </div>
                </div>
            </td>

            <td>
                <div class="summary-card">
                    <div class="summary-label">Biaya Restok AI</div>
                    <div class="summary-value">
                        Rp {{ number_format($totalRestockCost, 0, ',', '.') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="note-box">
        <strong>Ringkasan Prediksi AI:</strong><br>
        {{ $prediction['summary']
            ?? 'Belum tersedia ringkasan prediksi.' }}
    </div>


    {{-- INVENTORY --}}
    <div class="page-break"></div>
    <div class="section-title">1. Laporan Inventory</div>

    @if($inventory['products']->isEmpty())
        <div class="empty-state">Belum ada produk dalam inventory.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Kode</th>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Satuan</th>
                    <th class="money">Harga Beli</th>
                    <th class="money">Harga Jual</th>
                    <th class="center">Stok</th>
                    <th class="center">Minimum</th>
                    <th class="center">Maksimum</th>
                    <th>Status</th>
                    <th class="money">Nilai Stok</th>
                </tr>
            </thead>

            <tbody>
                @foreach($inventory['products'] as $product)
                    @php
                        $status = (int) $product->stock
                            < (int) $product->minimum_stock
                                ? 'Kritis'
                                : (
                                    (int) $product->stock
                                    > (int) $product->maximum_stock
                                        ? 'Berlebih'
                                        : 'Optimal'
                                );
                    @endphp

                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td>{{ $product->product_code }}</td>
                        <td><strong>{{ $product->name }}</strong></td>
                        <td>{{ $product->category ?: '-' }}</td>
                        <td>{{ $product->unit }}</td>

                        <td class="money">
                            Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                        </td>

                        <td class="money">
                            Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                        </td>

                        <td class="center">{{ $product->stock }}</td>
                        <td class="center">{{ $product->minimum_stock }}</td>
                        <td class="center">{{ $product->maximum_stock }}</td>
                        <td>{{ $status }}</td>

                        <td class="money">
                            Rp {{ number_format(
                                $product->purchase_price * $product->stock,
                                0,
                                ',',
                                '.'
                            ) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif


    {{-- RIWAYAT STOK --}}
    <div class="page-break"></div>
    <div class="section-title">2. Laporan Riwayat Stok</div>

    @if($stockMovements['movements']->isEmpty())
        <div class="empty-state">
            Belum ada pergerakan stok pada periode ini.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>Kode</th>
                    <th>Produk</th>
                    <th>Jenis</th>
                    <th class="center">Perubahan</th>
                    <th class="center">Sebelum</th>
                    <th class="center">Sesudah</th>
                    <th>Referensi</th>
                    <th>Catatan</th>
                </tr>
            </thead>

            <tbody>
                @foreach($stockMovements['movements'] as $movement)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>

                        <td>
                            {{ $movement->created_at
                                ? $movement->created_at
                                    ->timezone('Asia/Jakarta')
                                    ->format('d/m/Y H:i')
                                : '-' }}
                        </td>

                        <td>{{ $movement->product_code ?: '-' }}</td>
                        <td><strong>{{ $movement->product_name ?: '-' }}</strong></td>

                        <td>
                            {{ $movementLabels[$movement->type]
                                ?? ucwords(
                                    str_replace('_', ' ', $movement->type)
                                ) }}
                        </td>

                        <td class="center {{ $movement->quantity >= 0
                            ? 'positive'
                            : 'negative' }}">
                            {{ $movement->quantity >= 0 ? '+' : '' }}
                            {{ $movement->quantity }}
                        </td>

                        <td class="center">{{ $movement->stock_before }}</td>
                        <td class="center">{{ $movement->stock_after }}</td>

                        <td>
                            {{ $movement->reference_id
                                ? '#' . $movement->reference_id
                                : '-' }}
                        </td>

                        <td>{{ $movement->note ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif


    {{-- PENJUALAN --}}
    <div class="page-break"></div>
    <div class="section-title">3. Laporan Penjualan POS</div>

    @if($sales['transactions']->isEmpty())
        <div class="empty-state">
            Belum ada transaksi POS pada periode ini.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>Invoice</th>
                    <th>Pembayaran</th>
                    <th class="money">Subtotal</th>
                    <th class="money">Pajak</th>
                    <th class="money">Diskon</th>
                    <th class="money">Total</th>
                    <th class="money">Modal</th>
                    <th class="money">Laba</th>
                </tr>
            </thead>

            <tbody>
                @foreach($sales['transactions'] as $transaction)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>

                        <td>
                            {{ $transaction->created_at
                                ? $transaction->created_at
                                    ->timezone('Asia/Jakarta')
                                    ->format('d/m/Y H:i')
                                : '-' }}
                        </td>

                        <td>{{ $transaction->invoice_number }}</td>

                        <td>
                            {{ $paymentLabels[
                                strtolower((string) $transaction->payment_method)
                            ] ?? ucwords(
                                str_replace(
                                    '_',
                                    ' ',
                                    (string) $transaction->payment_method
                                )
                            ) }}
                        </td>

                        <td class="money">
                            Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}
                        </td>

                        <td class="money">
                            Rp {{ number_format($transaction->tax, 0, ',', '.') }}
                        </td>

                        <td class="money">
                            Rp {{ number_format($transaction->discount, 0, ',', '.') }}
                        </td>

                        <td class="money">
                            Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}
                        </td>

                        <td class="money">
                            Rp {{ number_format($transaction->total_cost, 0, ',', '.') }}
                        </td>

                        <td class="money positive">
                            Rp {{ number_format($transaction->total_profit, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif


    {{-- ANALYTICS --}}
    <div class="page-break"></div>
    <div class="section-title">4. Laporan Analytics</div>

    @if($analytics['daily_analytics']->isEmpty())
        <div class="empty-state">
            Belum ada data Analytics pada periode ini.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th class="center">Transaksi</th>
                    <th class="center">Terjual</th>
                    <th class="money">Pendapatan</th>
                    <th class="money">Modal</th>
                    <th class="money">Laba</th>
                    <th class="money">Margin</th>
                </tr>
            </thead>

            <tbody>
                @foreach($analytics['daily_analytics'] as $row)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>

                        <td>
                            {{ \Carbon\Carbon::parse($row->transaction_date)
                                ->format('d/m/Y') }}
                        </td>

                        <td class="center">{{ $row->total_transactions }}</td>
                        <td class="center">{{ $row->total_items }}</td>

                        <td class="money">
                            Rp {{ number_format($row->total_revenue, 0, ',', '.') }}
                        </td>

                        <td class="money">
                            Rp {{ number_format($row->total_cost, 0, ',', '.') }}
                        </td>

                        <td class="money positive">
                            Rp {{ number_format($row->total_profit, 0, ',', '.') }}
                        </td>

                        <td class="money">
                            {{ number_format($row->profit_margin, 1, ',', '.') }}%
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif


    {{-- PREDIKSI AI --}}
    <div class="page-break"></div>
    <div class="section-title">5. Laporan Prediksi Inventory AI</div>

    <div class="note-box">
        <strong>
            Mode: {{ $prediction['mode_label'] ?? 'Belum Ada Prediksi' }}
        </strong>

        <br>

        {{ $prediction['summary']
            ?? 'Belum tersedia ringkasan prediksi.' }}
    </div>

    <br>

    @if($predictionProducts->isEmpty())
        <div class="empty-state">
            Belum ada produk yang dapat diprediksi.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Produk</th>
                    <th>Metode</th>
                    <th class="center">Prediksi</th>
                    <th class="center">Stok</th>
                    <th class="center">Minimum</th>
                    <th class="center">Restok</th>
                    <th class="money">Biaya</th>
                    <th>Keyakinan</th>
                    <th>Alasan</th>
                </tr>
            </thead>

            <tbody>
                @foreach($predictionProducts as $product)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>
                        <td><strong>{{ $product['product_name'] ?? '-' }}</strong></td>
                        <td>{{ $product['method_label'] ?? '-' }}</td>

                        <td class="center">
                            {{ $product['predicted_quantity'] ?? 0 }}
                        </td>

                        <td class="center">
                            {{ $product['current_stock'] ?? 0 }}
                        </td>

                        <td class="center">
                            {{ $product['minimum_stock'] ?? 0 }}
                        </td>

                        <td class="center positive">
                            +{{ $product['recommended_restock'] ?? 0 }}
                        </td>

                        <td class="money">
                            Rp {{ number_format(
                                $product['estimated_cost'] ?? 0,
                                0,
                                ',',
                                '.'
                            ) }}
                        </td>

                        <td>
                            {{ $product['confidence_label']
                                ?? 'Belum tersedia' }}
                        </td>

                        <td>
                            {{ $product['method_reason']
                                ?? $product['reason']
                                ?? '-' }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Laporan lengkap dibuat otomatis oleh BusinessMate. Prediksi AI merupakan
        pendukung keputusan dan tetap memerlukan pertimbangan pengguna.
    </div>
</body>
</html>