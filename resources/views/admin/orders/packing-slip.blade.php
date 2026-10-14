<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Packing slip — {{ $order->order_number }}</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #111; margin: 2rem; }
        h1 { font-size: 18px; margin-bottom: 0.25rem; }
        table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        th, td { border-bottom: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        .totals td { border: none; text-align: right; }
        .muted { color: #555; }
    </style>
</head>
<body onload="window.print()">
    <h1>{{ config('app.name') }} — Packing slip</h1>
    <p class="muted">Order {{ $order->order_number }} · Placed {{ $order->placed_at->format('Y-m-d H:i') }} · {{ $order->payment_method->label() }}</p>

    <p>
        <strong>{{ $order->customer_name }}</strong><br>
        {{ $order->phone }}<br>
        {{ $order->address_line }}<br>
        {{ $order->area_name ? $order->area_name.', ' : '' }}{{ $order->district_name }}, {{ $order->division_name }}{{ $order->postcode ? ' — '.$order->postcode : '' }}
    </p>

    @if ($order->delivery_instructions)
        <p class="muted">Instructions: {{ $order->delivery_instructions }}</p>
    @endif

    <table>
        <thead>
            <tr><th>Item</th><th>SKU</th><th>Qty</th></tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}{{ $item->variant_label ? ' ('.$item->variant_label.')' : '' }}</td>
                    <td>{{ $item->sku }}</td>
                    <td>{{ $item->quantity }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="totals"><td colspan="2">Grand total</td><td>৳{{ $order->grand_total }}</td></tr>
        </tfoot>
    </table>
</body>
</html>
