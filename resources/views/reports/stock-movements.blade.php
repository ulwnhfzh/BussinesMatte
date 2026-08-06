<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">

    <title>Laporan Riwayat Stok</title>

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
            font-size: 14px;
            font-weight: bold;
        }

        .report-meta {
            color: #64748b;
            font-size: 8px;
            line-height: 1.6;
            text-align: right;
        }

        .summary-table {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: separate;
            border-spacing: 6px 0;
        }

        .summary-table td {
            width: 20%;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            background: #f8fafc;
            padding: 9px;
        }

        .summary-label {
            color: #64748b;
            font-size: 7px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .summary-value {
            margin-top: 5px;
            font-size: 13px;
            font-weight: bold;
        }

        .summary-in {
            border-color: #a7f3d0 !important;
            background: #ecfdf5 !important;
        }

        .summary-in .summary-value {
            color: #059669;
        }

        .summary-out {
            border-color: #fecaca !important;
            background: #fef2f2 !important;
        }

        .summary-out .summary-value {
            color: #dc2626;
        }

        .section-heading {
            margin: 0 0 8px;
            font-size: 11px;
            font-weight: bold;
        }

        .movement-table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        .movement-table thead {
            display: table-header-group;
        }

        .movement-table tr {
            page-break-inside: avoid;
        }

        .movement-table th {
            border: 1px solid #1d4ed8;
            background: #2563eb;
            padding: 7px 4px;
            color: #ffffff;
            font-size: 7px;
            text-align: left;
            text-transform: uppercase;
        }

        .movement-table td {
            border: 1px solid #e2e8f0;
            padding: 6px 4px;
            color: #334155;
            font-size: 7px;
            vertical-align: middle;
            word-wrap: break-word;
        }

        .movement-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .text-center {
            text-align: center !important;
        }

        .product-name {
            color: #0f172a;
            font-weight: bold;
        }

        .quantity-in {
            color: #059669;
            font-weight: bold;
        }

        .quantity-out {
            color: #dc2626;
            font-weight: bold;
        }

        .movement-type {
            display: inline-block;
            border-radius: 10px;
            padding: 3px 6px;
            font-size: 6px;
            font-weight: bold;
            white-space: nowrap;
        }

        .type-initial {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .type-sale {
            background: #fee2e2;
            color: #dc2626;
        }

        .type-adjustment {
            background: #fef3c7;
            color: #b45309;
        }

        .type-restock {
            background: #d1fae5;
            color: #047857;
        }

        .type-other {
            background: #e2e8f0;
            color: #475569;
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

<!-- Header -->
<div class="header">
    <table class="header-table">
        <tr>
            <td>
                <div class="brand">BusinessMate</div>

                <div class="report-title">
                    Laporan Riwayat Stok
                </div>
            </td>

            <td class="report-meta">
                <strong>{{ $businessName }}</strong><br>

                Periode:
                {{ $startDate->format('d/m/Y') }}
                –
                {{ $endDate->format('d/m/Y') }}
                <br>

                Dibuat pada:
                {{ $generatedAt->format('d/m/Y H:i') }} WIB
            </td>
        </tr>
    </table>
</div>

<!-- Ringkasan -->
<table class="summary-table">
    <tr>
        <td>
            <div class="summary-label">
                Total Pergerakan
            </div>

            <div class="summary-value">
                {{ number_format(
                    (int) ($summary->total_movements ?? 0),
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </td>

        <td>
            <div class="summary-label">
                Produk Terdampak
            </div>

            <div class="summary-value">
                {{ number_format(
                    (int) ($summary->affected_products ?? 0),
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </td>

        <td class="summary-in">
            <div class="summary-label">
                Stok Masuk
            </div>

            <div class="summary-value">
                +{{ number_format(
                    (int) ($summary->total_stock_in ?? 0),
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </td>

        <td class="summary-out">
            <div class="summary-label">
                Stok Keluar
            </div>

            <div class="summary-value">
                -{{ number_format(
                    (int) ($summary->total_stock_out ?? 0),
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </td>

        <td class="summary-out">
            <div class="summary-label">
                Unit Terjual
            </div>

            <div class="summary-value">
                {{ number_format(
                    (int) ($summary->total_sales ?? 0),
                    0,
                    ',',
                    '.'
                ) }}
            </div>
        </td>
    </tr>
</table>

<h2 class="section-heading">
    Detail Pergerakan Stok
</h2>

@if($movements->isEmpty())
    <div class="empty-state">
        Tidak ada riwayat stok pada periode yang dipilih.
    </div>
@else
    <table class="movement-table">
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">No.</th>
                <th style="width: 10%;">Tanggal</th>
                <th style="width: 8%;">Kode</th>
                <th style="width: 13%;">Produk</th>
                <th style="width: 10%;">Jenis</th>
                <th style="width: 7%;" class="text-center">Perubahan</th>
                <th style="width: 7%;" class="text-center">Sebelum</th>
                <th style="width: 7%;" class="text-center">Sesudah</th>
                <th style="width: 12%;">Referensi</th>
                <th style="width: 23%;">Catatan</th>
            </tr>
        </thead>

        <tbody>
            @foreach($movements as $index => $movement)
                @php
                    $typeLabel = match ($movement->type) {
                        'initial' => 'Stok Awal',
                        'adjustment' => 'Penyesuaian',
                        'sale' => 'Penjualan',
                        'restock' => 'Restok',
                        'return' => 'Retur',
                        'refund' => 'Refund',
                        'void' => 'Pembatalan',
                        default => $movement->type
                            ? \Illuminate\Support\Str::headline(
                                $movement->type
                            )
                            : 'Tidak Diketahui',
                    };

                    $typeClass = match ($movement->type) {
                        'initial' => 'type-initial',
                        'sale' => 'type-sale',
                        'adjustment' => 'type-adjustment',
                        'restock',
                        'return',
                        'refund',
                        'void' => 'type-restock',
                        default => 'type-other',
                    };

                    if (
                        $movement->reference_type
                        && $movement->reference_id
                    ) {
                        $reference = \Illuminate\Support\Str::headline(
                            class_basename(
                                $movement->reference_type
                            )
                        ) . ' #' . $movement->reference_id;
                    } else {
                        $reference = '-';
                    }
                @endphp

                <tr>
                    <td class="text-center">
                        {{ $index + 1 }}
                    </td>

                    <td>
                        {{ $movement->created_at
                            ? $movement->created_at
                                ->timezone('Asia/Jakarta')
                                ->format('d/m/Y H:i')
                            : '-' }}
                    </td>

                    <td>
                        {{ $movement->product_code ?: '-' }}
                    </td>

                    <td class="product-name">
                        {{ $movement->product_name
                            ?: 'Produk tidak tersedia' }}
                    </td>

                    <td>
                        <span class="movement-type {{ $typeClass }}">
                            {{ $typeLabel }}
                        </span>
                    </td>

                    <td
                        class="text-center
                            {{ (int) $movement->quantity >= 0
                                ? 'quantity-in'
                                : 'quantity-out' }}"
                    >
                        {{ (int) $movement->quantity > 0 ? '+' : '' }}
                        {{ number_format(
                            (int) $movement->quantity,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="text-center">
                        {{ number_format(
                            (int) $movement->stock_before,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td class="text-center">
                        {{ number_format(
                            (int) $movement->stock_after,
                            0,
                            ',',
                            '.'
                        ) }}
                    </td>

                    <td>
                        {{ $reference }}
                    </td>

                    <td>
                        {{ $movement->note ?: '-' }}
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