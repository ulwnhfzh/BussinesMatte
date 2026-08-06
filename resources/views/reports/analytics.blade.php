<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Analytics</title>

    <style>
        @page {
            margin: 24px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #0f172a;
        }

        h1, h2, h3, p {
            margin: 0;
        }

        .header {
            width: 100%;
            margin-bottom: 18px;
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

        .summary-table {
            width: 100%;
            border-spacing: 6px;
            margin: 0 -6px 16px;
        }

        .summary-card {
            padding: 11px;
            border: 1px solid #dbeafe;
            background: #eff6ff;
        }

        .summary-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
        }

        .summary-value {
            margin-top: 4px;
            font-size: 13px;
            font-weight: bold;
        }

        .section {
            margin-top: 18px;
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
            padding: 7px 5px;
            background: #2563eb;
            color: white;
            text-align: left;
            font-size: 7.5px;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 7px 5px;
            border-bottom: 1px solid #e2e8f0;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .money {
            text-align: right;
            white-space: nowrap;
        }

        .center {
            text-align: center;
        }

        .profit {
            color: #059669;
            font-weight: bold;
        }

        .empty-state {
            padding: 25px;
            border: 1px solid #e2e8f0;
            color: #64748b;
            text-align: center;
        }

        .footer {
            margin-top: 16px;
            padding-top: 8px;
            border-top: 1px solid #e2e8f0;
            color: #94a3b8;
            font-size: 8px;
        }
    </style>
</head>

<body>
    <table class="header">
        <tr>
            <td>
                <div class="title">BusinessMate</div>
                <div class="business-name">{{ $businessName }}</div>
                <div class="muted">Laporan Analytics Bisnis</div>
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

    <table class="summary-table">
        <tr>
            <td width="14.28%">
                <div class="summary-card">
                    <div class="summary-label">Transaksi</div>
                    <div class="summary-value">
                        {{ number_format($summary['total_transactions']) }}
                    </div>
                </div>
            </td>

            <td width="14.28%">
                <div class="summary-card">
                    <div class="summary-label">Produk Terjual</div>
                    <div class="summary-value">
                        {{ number_format($summary['total_items_sold']) }}
                    </div>
                </div>
            </td>

            <td width="14.28%">
                <div class="summary-card">
                    <div class="summary-label">Pendapatan</div>
                    <div class="summary-value">
                        Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                    </div>
                </div>
            </td>

            <td width="14.28%">
                <div class="summary-card">
                    <div class="summary-label">Modal</div>
                    <div class="summary-value">
                        Rp {{ number_format($summary['total_cost'], 0, ',', '.') }}
                    </div>
                </div>
            </td>

            <td width="14.28%">
                <div class="summary-card">
                    <div class="summary-label">Laba</div>
                    <div class="summary-value">
                        Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}
                    </div>
                </div>
            </td>

            <td width="14.28%">
                <div class="summary-card">
                    <div class="summary-label">Margin Laba</div>
                    <div class="summary-value">
                        {{ number_format($summary['profit_margin'], 1, ',', '.') }}%
                    </div>
                </div>
            </td>

            <td width="14.28%">
                <div class="summary-card">
                    <div class="summary-label">Rata-rata</div>
                    <div class="summary-value">
                        Rp {{ number_format($summary['average_transaction'], 0, ',', '.') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section">
        <div class="section-title">Performa Harian</div>

        @if($dailyAnalytics->isEmpty())
            <div class="empty-state">
                Belum ada data Analytics pada periode yang dipilih.
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="4%">No.</th>
                        <th width="15%">Tanggal</th>
                        <th width="11%" class="center">Transaksi</th>
                        <th width="12%" class="center">Produk Terjual</th>
                        <th width="15%" class="money">Pendapatan</th>
                        <th width="14%" class="money">Modal</th>
                        <th width="14%" class="money">Laba</th>
                        <th width="15%" class="money">Margin</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($dailyAnalytics as $row)
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>

                            <td>
                                {{ \Carbon\Carbon::parse($row->transaction_date)
                                    ->locale('id')
                                    ->translatedFormat('d F Y') }}
                            </td>

                            <td class="center">
                                {{ number_format($row->total_transactions) }}
                            </td>

                            <td class="center">
                                {{ number_format($row->total_items) }}
                            </td>

                            <td class="money">
                                Rp {{ number_format($row->total_revenue, 0, ',', '.') }}
                            </td>

                            <td class="money">
                                Rp {{ number_format($row->total_cost, 0, ',', '.') }}
                            </td>

                            <td class="money profit">
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
    </div>

    <div class="section">
        <div class="section-title">Produk dengan Penjualan Terbaik</div>

        @if($topProducts->isEmpty())
            <div class="empty-state">
                Belum ada produk yang terjual pada periode ini.
            </div>
        @else
            <table class="data-table">
                <thead>
                    <tr>
                        <th width="5%">Peringkat</th>
                        <th width="16%">Kode</th>
                        <th width="29%">Produk</th>
                        <th width="12%" class="center">Jumlah Terjual</th>
                        <th width="15%" class="money">Pendapatan</th>
                        <th width="13%" class="money">Laba</th>
                        <th width="10%" class="money">Margin</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach($topProducts as $product)
                        <tr>
                            <td class="center">{{ $loop->iteration }}</td>
                            <td>{{ $product->product_code }}</td>
                            <td><strong>{{ $product->name }}</strong></td>

                            <td class="center">
                                {{ number_format($product->total_quantity) }}
                            </td>

                            <td class="money">
                                Rp {{ number_format($product->total_revenue, 0, ',', '.') }}
                            </td>

                            <td class="money profit">
                                Rp {{ number_format($product->total_profit, 0, ',', '.') }}
                            </td>

                            <td class="money">
                                {{ number_format($product->profit_margin, 1, ',', '.') }}%
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    <div class="footer">
        Laporan dibuat otomatis berdasarkan transaksi POS milik
        {{ $businessName }}.
    </div>
</body>
</html>