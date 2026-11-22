<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Compra #{{ $order->id }} - PCP Industrial</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333333;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .brand-title {
            font-size: 22px;
            font-weight: bold;
            color: #0056b3;
            margin: 0;
        }
        .brand-subtitle {
            font-size: 11px;
            color: #666666;
            margin: 2px 0 0 0;
        }
        .document-title {
            text-align: right;
        }
        .document-title h2 {
            font-size: 18px;
            color: #222;
            margin: 0;
            text-transform: uppercase;
        }
        .document-title p {
            margin: 3px 0 0 0;
            color: #555;
            font-size: 11px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .info-col {
            width: 48%;
            vertical-align: top;
            background: #f9fbfd;
            border: 1px solid #e2e8f0;
            padding: 12px 15px;
            border-radius: 4px;
        }
        .info-col h3 {
            font-size: 13px;
            color: #0056b3;
            margin: 0 0 8px 0;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .info-col p {
            margin: 3px 0;
            font-size: 11px;
        }
        .info-col strong {
            color: #1e293b;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #0056b3;
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #0056b3;
        }
        .items-table td {
            padding: 8px 10px;
            font-size: 11px;
            border: 1px solid #e2e8f0;
        }
        .items-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .totals-table {
            width: 40%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .totals-table td {
            padding: 6px 10px;
            font-size: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals-table .total-row td {
            font-size: 14px;
            font-weight: bold;
            color: #0056b3;
            border-top: 2px solid #0056b3;
            border-bottom: none;
        }
        .notes-box {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 10px 15px;
            margin-top: 20px;
            font-size: 11px;
            color: #92400e;
        }
        .footer {
            margin-top: 40px;
            border-top: 1px solid #cbd5e1;
            padding-top: 10px;
            text-align: center;
            font-size: 10px;
            color: #64748b;
        }
    </style>
</head>
<body>

    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td style="vertical-align: middle;">
                <h1 class="brand-title">PCP INDUSTRIAL</h1>
                <p class="brand-subtitle">Válvulas, Tuberías y Soluciones en Plásticos Industriales</p>
            </td>
            <td class="document-title" style="vertical-align: middle;">
                <h2>Orden de Pedido</h2>
                <p><strong>N°:</strong> ORD-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Fecha:</strong> {{ $order->created_at ? $order->created_at->format('d/m/Y') : date('d/m/Y') }}</p>
                <p><strong>Estado:</strong> {{ strtoupper($order->status) }}</p>
            </td>
        </tr>
    </table>

    <table class="info-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="info-col">
                <h3>Proveedor / Vendedor</h3>
                <p><strong>Nombre:</strong> {{ $seller->name ?? 'PCP Distribución Industrial' }}</p>
                <p><strong>Rol:</strong> {{ $seller->role ?? 'Distribuidor Autorizado' }}</p>
                <p><strong>Empresa:</strong> {{ $seller->establishment_name ?? 'PCP Plásticos' }}</p>
                <p><strong>Ciudad:</strong> {{ $seller->city ?? 'Nacional' }} - {{ $seller->country ?? 'Colombia' }}</p>
                <p><strong>Email:</strong> {{ $seller->email ?? 'contacto@pcpplasticos.co' }}</p>
                <p><strong>Teléfono:</strong> {{ $seller->phone ?? 'PBX (+57)' }}</p>
            </td>
            <td style="width: 4%;"></td>
            <td class="info-col">
                <h3>Cliente / Comprador</h3>
                <p><strong>Nombre:</strong> {{ $buyer->name ?? 'Cliente B2B' }}</p>
                <p><strong>Establecimiento:</strong> {{ $buyer->establishment_name ?? 'N/A' }}</p>
                <p><strong>Doc / NIT:</strong> {{ $buyer->document_type ?? 'NIT' }} {{ $buyer->document_number ?? 'N/A' }}</p>
                <p><strong>Dirección Entrega:</strong> {{ $order->delivery_address ?? 'Entrega en bodega' }}</p>
                <p><strong>Ciudad:</strong> {{ $order->city ?? ($buyer->city ?? 'N/A') }}</p>
                <p><strong>Email:</strong> {{ $buyer->email ?? 'N/A' }}</p>
            </td>
        </tr>
    </table>

    <table class="items-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 15%;">Referencia</th>
                <th style="width: 45%;">Descripción del Producto</th>
                <th style="width: 10%;" class="text-center">Cant.</th>
                <th style="width: 12%;" class="text-right">Precio Unit.</th>
                <th style="width: 13%;" class="text-right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $index => $item)
                @php
                    $prod = $item->product;
                    $itemTotal = (float) $item->price * (int) $item->quantity;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $prod->code ?? 'REF-'.$item->product_id }}</strong></td>
                    <td>{{ $prod->name ?? 'Producto Industrial' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $item->currency ?? $order->currency }} ${{ number_format((float) $item->price, 2) }}</td>
                    <td class="text-right">{{ $item->currency ?? $order->currency }} ${{ number_format($itemTotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px;">No hay ítems registrados en este pedido.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="text-right"><strong>Subtotal:</strong></td>
            <td class="text-right">{{ $order->currency }} ${{ number_format((float) $order->total, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right"><strong>Impuestos (IVA):</strong></td>
            <td class="text-right">Incluido</td>
        </tr>
        <tr class="total-row">
            <td class="text-right"><strong>TOTAL:</strong></td>
            <td class="text-right">{{ $order->currency }} ${{ number_format((float) $order->total, 2) }}</td>
        </tr>
    </table>

    @if($order->coment)
        <div class="notes-box">
            <strong>Observaciones del Pedido:</strong><br>
            {{ $order->coment }}
        </div>
    @endif

    <div class="footer">
        <p>Documento generado electrónicamente por el Sistema B2B PCP Industrial Wholesale.</p>
        <p>Para consultas sobre despachos, garantías o facturación, comuníquese con su distribuidor asignado.</p>
    </div>

</body>
</html>
