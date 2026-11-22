<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Cotización PCP Industrial #{{ $order->id ?? '' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #333;
            margin: 20px;
        }
        .header {
            width: 100%;
            border-bottom: 2px solid #0056b3;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .title {
            font-size: 20px;
            font-weight: bold;
            color: #0056b3;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-cell {
            width: 50%;
            vertical-align: top;
            padding: 8px;
            background: #f8fafc;
            border: 1px solid #e2e8f0;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .items-table th, .items-table td {
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            font-size: 10px;
        }
        .items-table th {
            background-color: #0056b3;
            color: #fff;
            text-align: left;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>

    <table class="header">
        <tr>
            <td>
                <div class="title">PCP INDUSTRIAL</div>
                <div>Válvulas y Tuberías Plásticas B2B</div>
            </td>
            <td class="text-right">
                <div style="font-size: 16px; font-weight: bold;">COTIZACIÓN #{{ $order->id ?? '---' }}</div>
                <div>Fecha: {{ $sDate ?? date('Y-m-d') }}</div>
            </td>
        </tr>
    </table>

    <table class="info-table" cellspacing="10">
        <tr>
            <td class="info-cell">
                <strong>VENDEDOR / DISTRIBUIDOR</strong><br>
                Nombre: {{ $sellerUser->name ?? 'PCP Distribución' }}<br>
                Empresa: {{ $sellerUser->establishment_name ?? 'PCP Plásticos' }}<br>
                Email: {{ $sellerUser->email ?? '' }}<br>
                Teléfono: {{ $sellerUser->phone ?? '' }}<br>
                Ciudad: {{ $sellerUser->city ?? '' }}
            </td>
            <td class="info-cell">
                <strong>CLIENTE SOLICITANTE</strong><br>
                Nombre: {{ $user->name ?? 'Cliente' }}<br>
                Empresa: {{ $user->establishment_name ?? '' }}<br>
                Doc / NIT: {{ $user->document_number ?? '' }}<br>
                Email: {{ $user->email ?? '' }}<br>
                Ciudad: {{ $order->city ?? ($user->city ?? '') }}
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">#</th>
                <th style="width: 20%;">Código</th>
                <th style="width: 45%;">Descripción</th>
                <th class="text-center" style="width: 10%;">Cant.</th>
                <th class="text-right" style="width: 10%;">Precio Unit.</th>
                <th class="text-right" style="width: 10%;">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @if(isset($productsPrice))
                @foreach($productsPrice as $idx => $item)
                    @php
                        $prod = isset($product[$idx]) ? $product[$idx] : (is_array($product) ? ($product[$idx] ?? null) : $product->get($idx));
                    @endphp
                    <tr>
                        <td class="text-center">{{ $idx + 1 }}</td>
                        <td>{{ $prod->code ?? 'N/A' }}</td>
                        <td>{{ $prod->name ?? 'Ítem' }}</td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">${{ number_format((float) $item->price, 2) }}</td>
                        <td class="text-right">${{ number_format((float) $item->price * (int) $item->quantity, 2) }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>

    <table style="width: 40%; margin-left: auto; margin-bottom: 20px;">
        <tr style="font-weight: bold; font-size: 13px;">
            <td class="text-right">TOTAL:</td>
            <td class="text-right">${{ number_format((float) ($total ?? ($order->total ?? 0)), 2) }}</td>
        </tr>
    </table>

    <div style="font-size: 9px; color: #666; text-align: center; border-top: 1px solid #ddd; padding-top: 10px;">
        Plataforma B2B PCP Industrial Wholesale. Cotización válida por 30 días.
    </div>

</body>
</html>
