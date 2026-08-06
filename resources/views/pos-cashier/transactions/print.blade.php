<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk {{ $transaction->invoice_number }}</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            width: 78mm;
            margin: 0 auto;
            padding: 8px;
            font-size: 12px;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .line { border-bottom: 1px dashed #000; margin: 8px 0; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 2px 0; vertical-align: top; }
        @media print {
            body { width: 100%; padding: 0; }
            @page { margin: 0; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="text-center">
        <h3 style="margin: 0; font-size: 16px;">UsahaMate</h3>
        <p style="margin: 2px 0; font-size: 11px;">Struk Pembayaran Penjualan</p>
    </div>

    <div class="line"></div>

<table style="font-size: 11px;">
        <tr><td>No. Inv</td><td>: {{ $transaction->invoice_number }}</td></tr>
        <tr><td>Tanggal</td><td>: {{ $transaction->created_at->format('d/m/Y H:i') }}</td></tr>
        <tr><td>Kasir</td><td>: {{ $transaction->user->name ?? 'Kasir' }}</td></tr>
        @if($transaction->status !== 'completed')
        <tr>
            <td>Status</td>
            <td>: {{ strtoupper($transaction->status_label) }}</td>
        </tr>
        @endif
    </table>

    <div class="line"></div>

    <table>
        @foreach($transaction->details as $item)
        <tr>
            <td colspan="2"><strong>{{ $item->product->name ?? 'Produk' }}</strong></td>
        </tr>
        <tr>
            <td>{{ $item->quantity }} x {{ number_format($item->selling_price, 0, ',', '.') }}</td>
            <td class="text-right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
        </tr>
        @endforeach
    </table>

    <div class="line"></div>

    <table>
        <tr>
            <td>Subtotal</td>
            <td class="text-right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($transaction->tax > 0)
        <tr>
            <td>Pajak</td>
            <td class="text-right">Rp {{ number_format($transaction->tax, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($transaction->discount > 0)
        <tr>
            <td>Diskon</td>
            <td class="text-right">- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td><strong>TOTAL</strong></td>
            <td class="text-right"><strong>Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</strong></td>
        </tr>
        <tr>
            <td>Bayar ({{ strtoupper($transaction->payment_method) }})</td>
            <td class="text-right">Rp {{ number_format($transaction->total_amount, 0, ',', '.') }}</td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="text-center" style="font-size: 11px;">
        <p style="margin: 4px 0;">Terima Kasih Atas Kunjungan Anda!</p>
        <p style="margin: 0;">UsahaMate POS</p>
    </div>
</body>
</html>