<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Penjualan POS</title>

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
            border-radius: 8px;
        }

        .summary-label {
            font-size: 8px;
            color: #64748b;
            text-transform: uppercase;
        }

        .summary-value {
            margin-top: 4px;
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
        }

        .data-table th {
            padding: 7px 5px;
            background: #2563eb;
            color: white;
            font-size: 7.5px;
            text-align: left;
            text-transform: uppercase;
        }

        .data-table td {
            padding: 7px 5px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: top;
        }

        .data-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        .money {
            white-space: nowrap;
            text-align: right;
        }

        .center {
            text-align: center;
        }

        .profit {
            color: #059669;
            font-weight: bold;
        }

        .empty-state {
            padding: 30px;
            text-align: center;
            border: 1px solid #e2e8f0;
            color: #64748b;
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
    @php
        $paymentLabels = [
            'cash' => 'Tunai',
            'transfer' => 'Transfer',
            'qris' => 'QRIS',
            'debit' => 'Kartu Debit',
            'credit' => 'Kartu Kredit',
        ];
    @endphp

    <table class="header">
        <tr>
            <td>
                <div class="title">BusinessMate</div>
                <div class="business-name">{{ $businessName }}</div>
                <div class="muted">Laporan Penjualan POS</div>
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
            <td width="16.66%">
                <div class="summary-card">
                    <div class="summary-label">Transaksi</div>
                    <div class="summary-value">
                        {{ number_format($summary['total_transactions']) }}
                    </div>
                </div>
            </td>

            <td width="16.66%">
                <div class="summary-card">
                    <div class="summary-label">Produk Terjual</div>
                    <div class="summary-value">
                        {{ number_format($summary['total_items_sold']) }}
                    </div>
                </div>
            </td>

            <td width="16.66%">
                <div class="summary-card">
                    <div class="summary-label">Subtotal</div>
                    <div class="summary-value">
                        Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}
                    </div>
                </div>
            </td>

            <td width="16.66%">
                <div class="summary-card">
                    <div class="summary-label">Total Pembayaran</div>
                    <div class="summary-value">
                        Rp {{ number_format($summary['total_revenue'], 0, ',', '.') }}
                    </div>
                </div>
            </td>

            <td width="16.66%">
                <div class="summary-card">
                    <div class="summary-label">Laba</div>
                    <div class="summary-value">
                        Rp {{ number_format($summary['total_profit'], 0, ',', '.') }}
                    </div>
                </div>
            </td>

            <td width="16.66%">
                <div class="summary-card">
                    <div class="summary-label">Rata-rata</div>
                    <div class="summary-value">
                        Rp {{ number_format($summary['average_transaction'], 0, ',', '.') }}
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <div class="section-title">Daftar Transaksi</div>

    @if($transactions->isEmpty())
        <div class="empty-state">
            Belum ada transaksi POS pada periode yang dipilih.
        </div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th width="3%">No.</th>
                    <th width="12%">Tanggal</th>
                    <th width="14%">Nomor Invoice</th>
                    <th width="10%">Pembayaran</th>
                    <th width="10%" class="money">Subtotal</th>
                    <th width="8%" class="money">Pajak</th>
                    <th width="8%" class="money">Diskon</th>
                    <th width="12%" class="money">Total</th>
                    <th width="11%" class="money">Modal</th>
                    <th width="12%" class="money">Laba</th>
                </tr>
            </thead>

            <tbody>
                @foreach($transactions as $transaction)
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

                        <td class="money profit">
                            Rp {{ number_format($transaction->total_profit, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    <div class="footer">
        Laporan dibuat otomatis oleh BusinessMate berdasarkan transaksi bisnis
        {{ $businessName }}.
    </div>
</body>
</html>