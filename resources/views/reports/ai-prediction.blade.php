<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Prediksi AI</title>

    <style>
        @page {
            margin: 24px;
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
            padding-bottom: 12px;
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

        .status-box {
            margin-bottom: 14px;
            padding: 10px;
            border: 1px solid #bfdbfe;
            background: #eff6ff;
        }

        .summary-table {
            width: 100%;
            border-spacing: 6px;
            margin: 0 -6px 16px;
        }

        .summary-card {
            padding: 10px;
            border: 1px solid #dbeafe;
            background: #f8fafc;
        }

        .summary-label {
            color: #64748b;
            text-transform: uppercase;
        }

        .summary-value {
            margin-top: 4px;
            font-size: 13px;
            font-weight: bold;
        }

        .section-title {
            margin-bottom: 8px;
            font-size: 12px;
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
            text-align: left;
            font-size: 6.5px;
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

        .restock {
            color: #1d4ed8;
            font-weight: bold;
        }

        .method {
            color: #2563eb;
            font-weight: bold;
        }

        .reason {
            color: #475569;
            line-height: 1.4;
        }

        .empty-state {
            padding: 30px;
            border: 1px solid #e2e8f0;
            color: #64748b;
            text-align: center;
        }

        .footer {
            margin-top: 16px;
            padding-top: 8px;
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

        $totalRestock = $restockProducts->sum('recommended_restock');
        $totalEstimatedCost = $restockProducts->sum('estimated_cost');
    @endphp

    <table class="header">
        <tr>
            <td>
                <div class="title">BusinessMate</div>
                <div class="business-name">{{ $businessName }}</div>
                <div class="muted">Laporan Prediksi Inventory AI</div>
            </td>

            <td class="text-right">
                <strong>
                    Prediksi {{ $prediction['forecast_days'] ?? 7 }} Hari
                </strong><br>

                <span class="muted">
                    Dibuat {{ $generatedAt->format('d/m/Y H:i') }} WIB
                </span>
            </td>
        </tr>
    </table>

    <div class="status-box">
        <strong>
            Status:
            {{ $prediction['service_status'] === 'online'
                ? 'Service AI Online'
                : 'Service AI Tidak Tersedia' }}
        </strong>

        <br>

        <span class="muted">
            {{ $prediction['service_message']
                ?? 'Informasi service tidak tersedia.' }}
        </span>

        <br><br>

        {{ $prediction['summary']
            ?? 'Belum tersedia ringkasan prediksi.' }}
    </div>

    <table class="summary-table">
        <tr>
            <td width="16.66%">
                <div class="summary-card">
                    <div class="summary-label">Mode Aktif</div>
                    <div class="summary-value">
                        {{ $prediction['mode_label'] ?? 'Belum Ada Prediksi' }}
                    </div>
                </div>
            </td>

            <td width="16.66%">
                <div class="summary-card">
                    <div class="summary-label">Periode Prediksi</div>
                    <div class="summary-value">
                        {{ $prediction['forecast_days'] ?? 7 }} Hari
                    </div>
                </div>
            </td>

            <td width="16.66%">
                <div class="summary-card">
                    <div class="summary-label">Prediksi Permintaan</div>
                    <div class="summary-value">
                        {{ number_format($prediction['predicted_total'] ?? 0) }}
                        Unit
                    </div>
                </div>
            </td>

            <td width="16.66%">
                <div class="summary-card">
                    <div class="summary-label">Produk Direstok</div>
                    <div class="summary-value">
                        {{ number_format($restockProducts->count()) }}
                    </div>
                </div>
            </td>

            <td width="16.66%">
                <div class="summary-card">
                    <div class="summary-label">Jumlah Restok</div>
                    <div class="summary-value">
                        {{ number_format($totalRestock) }} Unit
                    </div>
                </div>
            </td>

            <td width="16.66%">
                <div class="summary-card">
                    <div class="summary-label">Estimasi Biaya</div>
                    <div class="summary-value">
                        Rp {{ number_format($totalEstimatedCost, 0, ',', '.') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Prediksi dan Rekomendasi per Produk</div>

    @if($products->isEmpty())
        <div class="empty-state">
            Belum ada data produk yang dapat digunakan untuk prediksi.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr <tr>
                    <th width="3%">No.</th>
                    <th width="13%">Produk</th>
                    <th width="10%">Metode</th>
                    <th width="6%" class="center">Hari Data</th>
                    <th width="6%" class="center">Hari Jual</th>
                    <th width="7%" class="center">Prediksi</th>
                    <th width="6%" class="center">Stok</th>
                    <th width="6%" class="center">Minimum</th>
                    <th width="6%" class="center">Maksimum</th>
                    <th width="8%" class="center">Restok</th>
                    <th width="10%" class="money">Biaya</th>
                    <th width="8%">Keyakinan</th>
                    <th width="11%">Alasan</th>
                </tr>
            </thead>

            <tbody>
                @foreach($products as $product)
                    <tr>
                        <td class="center">{{ $loop->iteration }}</td>

                        <td>
                            <strong>
                                {{ $product['product_name'] ?? '-' }}
                            </strong>

                            <br>

                            <span class="muted">
                                {{ $product['unit'] ?? 'Unit' }}
                            </span>
                        </td>

                        <td class="method">
                            {{ $product['method_label']
                                ?? 'Belum Ada Prediksi' }}
                        </td>

                        <td class="center">
                            {{ number_format($product['data_days'] ?? 0) }}
                        </td>

                        <td class="center">
                            {{ number_format($product['sales_days'] ?? 0) }}
                        </td>

                        <td class="center">
                            {{ number_format($product['predicted_quantity'] ?? 0) }}
                        </td>

                        <td class="center">
                            {{ number_format($product['current_stock'] ?? 0) }}
                        </td>

                        <td class="center">
                            {{ number_format($product['minimum_stock'] ?? 0) }}
                        </td>

                        <td class="center">
                            {{ number_format($product['maximum_stock'] ?? 0) }}
                        </td>

                        <td class="center restock">
                            +{{ number_format($product['recommended_restock'] ?? 0) }}
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

                        <td class="reason">
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
        Hasil prediksi merupakan rekomendasi pendukung keputusan dan tetap
        memerlukan pertimbangan pengguna sebelum melakukan restok.
    </div>
</body>
</html>