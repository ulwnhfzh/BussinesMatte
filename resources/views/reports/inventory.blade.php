<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <title>Laporan Inventory BusinessMate</title>

    <style>
        @page {
            margin: 28px 30px;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            color: #0f172a;
            font-family: "DejaVu Sans", sans-serif;
            font-size: 9px;
        }

        .header {
            width: 100%;
            margin-bottom: 18px;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 12px;
        }

        .header-table {
            width: 100%;
            border-collapse: collapse;
        }

        .header-table td {
            vertical-align: middle;
        }

        .brand {
            color: #2563eb;
            font-size: 21px;
            font-weight: bold;
        }

        .report-title {
            margin-top: 3px;
            color: #0f172a;
            font-size: 14px;
            font-weight: bold;
        }

        .report-meta {
            text-align: right;
            color: #64748b;
            font-size: 8px;
            line-height: 1.6;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: separate;
            border-spacing: 6px 0;
        }

        .summary-table td {
            width: 16.66%;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
            padding: 9px;
            vertical-align: top;
        }

        .summary-label {
            color: #64748b;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .summary-value {
            margin-top: 5px;
            color: #0f172a;
            font-size: 13px;
            font-weight: bold;
        }

        .summary-critical {
            background: #fef2f2 !important;
            border-color: #fecaca !important;
        }

        .summary-critical .summary-value {
            color: #dc2626;
        }

        .summary-optimal {
            background: #ecfdf5 !important;
            border-color: #a7f3d0 !important;
        }

        .summary-optimal .summary-value {
            color: #059669;
        }

        .summary-excess {
            background: #eff6ff !important;
            border-color: #bfdbfe !important;
        }

        .summary-excess .summary-value {
            color: #2563eb;
        }

        .section-heading {
            margin: 0 0 8px;
            color: #0f172a;
            font-size: 11px;
            font-weight: bold;
        }

        .inventory-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .inventory-table thead {
            display: table-header-group;
        }

        .inventory-table tr {
            page-break-inside: avoid;
        }

        .inventory-table th {
            border: 1px solid #1d4ed8;
            background: #2563eb;
            padding: 7px 4px;
            color: #ffffff;
            font-size: 7px;
            text-align: left;
            text-transform: uppercase;
        }

        .inventory-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 4px;
            color: #334155;
            font-size: 7px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .inventory-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        .product-name {
            color: #0f172a;
            font-weight: bold;
        }

        .status {
            display: inline-block;
            border-radius: 10px;
            padding: 3px 6px;
            font-size: 6px;
            font-weight: bold;
            white-space: nowrap;
        }

        .status-critical {
            background: #fee2e2;
            color: #dc2626;
        }

        .status-optimal {
            background: #d1fae5;
            color: #047857;
        }

        .status-excess {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .empty-state {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            padding: 24px;
            color: #64748b;
            text-align: center;
        }

        .footer {
            position: fixed;
            right: 0;
            bottom: -14px;
            left: 0;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            color: #94a3b8;
            font-size: 7px;
            text-align: center;
        }
    </style>
</head>

<body>
@php
    $currentUser = Auth::user();

    $businessName = $currentUser->business_name
        ?: $currentUser->name;
@endphp

<!-- Header laporan -->
<div class="header">
    <table class="header-table">
        <tr>
            <td>
                <div class="brand">BusinessMate</div>

                <div class="report-title">
                    Laporan Inventory
                </div>
            </td>

            <td class="report-meta">
                <strong>{{ $businessName }}</strong><br>
                Dibuat pada:
                {{ $generatedAt->format('d/m/Y H:i') }} WIB<br>
                Data stok saat laporan dibuat
            </td>
        </tr>
    </table>
</div>

<!-- Ringkasan Inventory -->
<table class="summary-table">
    <tr>
        <td>
            <div class="summary-label">
                Total Produk
            </div>

            <div class="summary-value">
                {{ number_format(
                    (int) ($summary->total_products ?? 0),
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </td>

        <td>
            <div class="summary-label">
                Total Unit Stok
            </div>

            <div class="summary-value">
                {{ number_format(
                    (int) ($summary->total_stock ?? 0),
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </td>

        <td class="summary-optimal">
            <div class="summary-label">
                Stok Optimal
            </div>

            <div class="summary-value">
                {{ number_format(
                    (int) ($summary->optimal_products ?? 0),
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </td>

        <td class="summary-critical">
            <div class="summary-label">
                Stok Kritis
            </div>

            <div class="summary-value">
                {{ number_format(
                    (int) ($summary->critical_products ?? 0),
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </td>

        <td class="summary-excess">
            <div class="summary-label">
                Stok Berlebih
            </div>

            <div class="summary-value">
                {{ number_format(
                    (int) ($summary->excess_products ?? 0),
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </td>

        <td>
            <div class="summary-label">
                Nilai Persediaan
            </div>

            <div class="summary-value">
                Rp {{ number_format(
                    (float) ($summary->inventory_value ?? 0),
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </td>
    </tr>
</table>

<h2 class="section-heading">
    Daftar Produk
</h2>

@if($products->isEmpty())
    <div class="empty-state">
        Belum ada produk yang dapat dimasukkan ke dalam laporan.
    </div>
@else
    <table class="inventory-table">
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">No.</th>
                <th style="width: 8%;">Kode</th>
                <th style="width: 14%;">Nama Produk</th>
                <th style="width: 9%;">Kategori</th>
                <th style="width: 5%;">Satuan</th>
                <th style="width: 9%;" class="text-right">Harga Beli</th>
                <th style="width: 9%;" class="text-right">Harga Jual</th>
                <th style="width: 6%;" class="text-center">Stok</th>
                <th style="width: 6%;" class="text-center">Min.</th>
                <th style="width: 6%;" class="text-center">Maks.</th>
                <th style="width: 10%;" class="text-center">Status</th>
                <th style="width: 12%;" class="text-right">Nilai Persediaan</th>
            </tr>
        </thead>

        <tbody>
            @foreach($products as $index => $product)
                @php
                    if (
                        (int) $product->stock
                        < (int) $product->minimum_stock
                    ) {
                        $statusLabel = 'Kritis';
                        $statusClass = 'status-critical';
                    } elseif (
                        (int) $product->stock
                        > (int) $product->maximum_stock
                    ) {
                        $statusLabel = 'Berlebih';
                        $statusClass = 'status-excess';
                    } else {
                        $statusLabel = 'Optimal';
                        $statusClass = 'status-optimal';
                    }

                    $inventoryValue =
                        (float) $product->stock
                        * (float) $product->purchase_price;
                @endphp

                <tr>
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $product->product_code }}
                    </td>

                    <td class="product-name">
                        {{ $product->name }}
                    </td>

                    <td>
                        {{ $product->category ?: 'Tanpa Kategori' }}
                    </td>

                    <td>
                        {{ $product->unit }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format(
                            (float) $product->purchase_price,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="text-right">
                        Rp {{ number_format(
                            (float) $product->selling_price,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="text-center">
                        {{ number_format(
                            (int) $product->stock,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="text-center">
                        {{ number_format(
                            (int) $product->minimum_stock,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="text-center">
                        {{ number_format(
                            (int) $product->maximum_stock,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="text-center">
                        <span class="status {{ $statusClass }}">
                            {{ $statusLabel }}
                        </span>
                    </td>

                    <td class="text-right">
                        Rp {{ number_format(
                            $inventoryValue,
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

<div class="footer">
    Laporan ini dibuat secara otomatis oleh BusinessMate.
</div>
</body>
</html>