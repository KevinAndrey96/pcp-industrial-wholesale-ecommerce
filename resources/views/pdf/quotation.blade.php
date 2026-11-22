<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización Formal #{{ $order->id }} - PCP Industrial</title>
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
            border-bottom: 2px solid #0284c7;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .brand-title {
            font-size: 22px;
            font-weight: bold;
            color: #0284c7;
            margin: 0;
        }
        .brand-subtitle {
            font-size: 11px;
            color: #64748b;
            margin: 2px 0 0 0;
        }
        .document-title {
            text-align: right;
        }
        .document-title h2 {
            font-size: 18px;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
        }
        .document-title p {
            margin: 3px 0 0 0;
            color: #475569;
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
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 12px 15px;
            border-radius: 4px;
        }
        .info-col h3 {
            font-size: 13px;
            color: #0284c7;
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
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th {
            background-color: #0284c7;
            color: #ffffff;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #0284c7;
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
            color: #0284c7;
            border-top: 2px solid #0284c7;
            border-bottom: none;
        }
        .conditions-box {
            background: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 10px 15px;
            margin-top: 20px;
            font-size: 11px;
            color: #334155;
            border-radius: 4px;
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
                <p class="brand-subtitle">Válvulas de Bola, Cheques, Filtros y Conexiones Termoplásticas</p>
            </td>
            <td class="document-title" style="vertical-align: middle;">
                <h2>Cotización Comercial</h2>
                <p><strong>Cotización N°:</strong> COT-{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</p>
                <p><strong>Fecha de Emisión:</strong> {{ $order->created_at ? $order->created_at->format('d/m/Y') : date('d/m/Y') }}</p>
                <p><strong>Validez de Oferta:</strong> 30 días calendario</p>
            </td>
        </tr>
    </table>

    <table class="info-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="info-col">
                <h3>Distribuidor / Oferente</h3>
                <p><strong>Razón Social:</strong> {{ $seller->establishment_name ?? 'PCP Plásticos S.A.S.' }}</p>
                <p><strong>Asesor Encargado:</strong> {{ $seller->name ?? 'Ventas Institucionales' }}</p>
                <p><strong>Email:</strong> {{ $seller->email ?? 'ventas@pcpplasticos.co' }}</p>
                <p><strong>Teléfono:</strong> {{ $seller->phone ?? 'PBX (+57)' }}</p>
                <p><strong>Ciudad:</strong> {{ $seller->city ?? 'Nacional' }} - {{ $seller->country ?? 'Colombia' }}</p>
            </td>
            <td style="width: 4%;"></td>
            <td class="info-col">
                <h3>Cliente / Solicitante</h3>
                <p><strong>Contacto:</strong> {{ $buyer->name ?? 'Cliente B2B' }}</p>
                <p><strong>Empresa / Ferretería:</strong> {{ $buyer->establishment_name ?? 'N/A' }}</p>
                <p><strong>NIT / Cédula:</strong> {{ $buyer->document_type ?? 'NIT' }} {{ $buyer->document_number ?? 'N/A' }}</p>
                <p><strong>Destino:</strong> {{ $order->city ?? ($buyer->city ?? 'N/A') }}</p>
                <p><strong>Email:</strong> {{ $buyer->email ?? 'N/A' }}</p>
            </td>
        </tr>
    </table>

    <table class="items-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th style="width: 5%;" class="text-center">#</th>
                <th style="width: 15%;">Código</th>
                <th style="width: 45%;">Descripción del Ítem</th>
                <th style="width: 10%;" class="text-center">Cant.</th>
                <th style="width: 12%;" class="text-right">Precio Unit.</th>
                <th style="width: 13%;" class="text-right">Total Ítem</th>
            </tr>
        </thead>
        <tbody>
            @php $calculatedTotal = 0; @endphp
            @forelse($items as $index => $item)
                @php
                    $prod = $item->product;
                    $itemTotal = (float) $item->price * (int) $item->quantity;
                    $calculatedTotal += $itemTotal;
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td><strong>{{ $prod->code ?? 'REF-'.$item->product_id }}</strong></td>
                    <td>{{ $prod->name ?? 'Válvula / Accesorio Industrial' }}</td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">{{ $item->currency ?? $order->currency }} ${{ number_format((float) $item->price, 2) }}</td>
                    <td class="text-right">{{ $item->currency ?? $order->currency }} ${{ number_format($itemTotal, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 15px;">No hay productos cotizados.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <table class="totals-table" cellpadding="0" cellspacing="0">
        <tr>
            <td class="text-right"><strong>Subtotal Oferta:</strong></td>
            <td class="text-right">{{ $order->currency }} ${{ number_format($calculatedTotal > 0 ? $calculatedTotal : (float) $order->total, 2) }}</td>
        </tr>
        <tr>
            <td class="text-right"><strong>Impuestos (IVA):</strong></td>
            <td class="text-right">Conforme a tarifa legal vigente</td>
        </tr>
        <tr class="total-row">
            <td class="text-right"><strong>TOTAL COTIZADO:</strong></td>
            <td class="text-right">{{ $order->currency }} ${{ number_format($calculatedTotal > 0 ? $calculatedTotal : (float) $order->total, 2) }}</td>
        </tr>
    </table>

    <div class="conditions-box">
        <strong>Condiciones Comerciales y de Garantía:</strong>
        <ul style="margin: 5px 0 0 15px; padding: 0;">
            <li>Los precios son válidos por 30 días a partir de la fecha de emisión.</li>
            <li>Productos certificados con normas técnicas NTC / ASTM para redes hidráulicas a presión.</li>
            <li>Tiempos de entrega sujetos a disponibilidad de inventario en bodega central o regional.</li>
        </ul>
    </div>

    <div class="footer">
        <p>Documento oficial emitido a través de la plataforma B2B PCP Industrial Wholesale.</p>
    </div>

</body>
</html>
