<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderProduct;

class IndustrialSupplySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // 1. Seed Price Lists
        $distributorList = PriceList::firstOrCreate(
            ['name' => 'Lista Mayorista Distribución Nacional (COP)'],
            [
                'role' => 'Distribuidor',
                'currency' => 'COP',
                'country' => 'Colombia',
            ]
        );

        $ironmongerList = PriceList::firstOrCreate(
            ['name' => 'Lista Ferreterías y Constructoras (COP)'],
            [
                'role' => 'Ferretero',
                'currency' => 'COP',
                'country' => 'Colombia',
            ]
        );

        $exportList = PriceList::firstOrCreate(
            ['name' => 'Lista Exportación Internacional (USD)'],
            [
                'role' => 'Distribuidor',
                'currency' => 'USD',
                'country' => 'Internacional',
            ]
        );

        // 2. Seed Users
        $admin = User::firstOrCreate(
            ['email' => 'admin@pcp-industrial.local'],
            [
                'name' => 'Director de Operaciones PCP',
                'phone' => '+57 (4) 444-1234',
                'country' => 'Colombia',
                'city' => 'Medellín',
                'role' => 'Administrador',
                'establishment_name' => 'PCP Partes y Complementos Plásticos S.A.S.',
                'password' => Hash::make('AdminPassword123!'),
                'price_list_id' => $distributorList->id,
            ]
        );

        $distributor = User::firstOrCreate(
            ['email' => 'distribuidor@pcp-industrial.local'],
            [
                'name' => 'Distribuciones Hidráulicas del Valle S.A.S.',
                'phone' => '+57 (2) 555-5678',
                'country' => 'Colombia',
                'city' => 'Cali',
                'role' => 'Distribuidor',
                'establishment_name' => 'DisHidráulicas del Valle',
                'password' => Hash::make('DistribuidorPass123!'),
                'price_list_id' => $distributorList->id,
            ]
        );

        $ironmonger = User::firstOrCreate(
            ['email' => 'ferretero@pcp-industrial.local'],
            [
                'name' => 'Ferretería & Suministros La Principal',
                'phone' => '+57 (1) 310-9876',
                'country' => 'Colombia',
                'city' => 'Bogotá',
                'role' => 'Ferretero',
                'establishment_name' => 'Ferretería La Principal',
                'password' => Hash::make('FerreteroPass123!'),
                'price_list_id' => $ironmongerList->id,
            ]
        );

        // 3. Seed Products for Distributor List
        $distributorProducts = [
            ['code' => 'TUB-PVC-P-050', 'name' => 'Tubería PVC Presión RDE 21 1/2" Tramo 6m', 'price' => 14800, 'url' => 'tuberia_presion.jpg'],
            ['code' => 'TUB-PVC-P-075', 'name' => 'Tubería PVC Presión RDE 21 3/4" Tramo 6m', 'price' => 21400, 'url' => 'tuberia_presion.jpg'],
            ['code' => 'TUB-PVC-S-200', 'name' => 'Tubería PVC Sanitaria 2" Tramo 3m', 'price' => 25900, 'url' => 'tuberia_sanitaria.jpg'],
            ['code' => 'TUB-PVC-S-300', 'name' => 'Tubería PVC Sanitaria 3" Tramo 3m', 'price' => 43900, 'url' => 'tuberia_sanitaria.jpg'],
            ['code' => 'TUB-CPVC-050', 'name' => 'Tubería CPVC Agua Caliente CTS 1/2" x 6m', 'price' => 33600, 'url' => 'tuberia_cpvc.jpg'],
            ['code' => 'VAL-BOL-PVC-050', 'name' => 'Válvula de Bola PVC Lisa Cementar 1/2"', 'price' => 7800, 'url' => 'valvula_bola_pvc.jpg'],
            ['code' => 'VAL-BOL-PVC-075', 'name' => 'Válvula de Bola PVC Lisa Cementar 3/4"', 'price' => 11600, 'url' => 'valvula_bola_pvc.jpg'],
            ['code' => 'VAL-BOL-PVC-100', 'name' => 'Válvula de Bola PVC Roscada NPT 1"', 'price' => 17600, 'url' => 'valvula_roscada.jpg'],
            ['code' => 'VAL-CHK-PVC-100', 'name' => 'Válvula de Retención Check PVC 1"', 'price' => 22800, 'url' => 'valvula_check.jpg'],
            ['code' => 'ACC-COD-90-050', 'name' => 'Codo PVC Presión 90° Campana x Campana 1/2"', 'price' => 950, 'url' => 'codo_pvc.jpg'],
            ['code' => 'ACC-TEE-PVC-050', 'name' => 'Tee PVC Presión 1/2" C x C', 'price' => 1480, 'url' => 'tee_pvc.jpg'],
            ['code' => 'ACC-UNI-UNI-050', 'name' => 'Unión Universal PVC Presión 1/2" Roscada', 'price' => 6500, 'url' => 'union_universal.jpg'],
        ];

        foreach ($distributorProducts as $prod) {
            Product::firstOrCreate(
                ['code' => $prod['code'], 'list_id' => $distributorList->id],
                [
                    'name' => $prod['name'],
                    'price' => $prod['price'],
                    'url' => $prod['url'],
                ]
            );
        }

        // 4. Seed Products for Hardware Store List (Retail/Ferretero price +20-25%)
        $ironmongerProducts = [
            ['code' => 'TUB-PVC-P-050', 'name' => 'Tubería PVC Presión RDE 21 1/2" Tramo 6m', 'price' => 18500, 'url' => 'tuberia_presion.jpg'],
            ['code' => 'TUB-PVC-P-075', 'name' => 'Tubería PVC Presión RDE 21 3/4" Tramo 6m', 'price' => 26800, 'url' => 'tuberia_presion.jpg'],
            ['code' => 'TUB-PVC-S-200', 'name' => 'Tubería PVC Sanitaria 2" Tramo 3m', 'price' => 32400, 'url' => 'tuberia_sanitaria.jpg'],
            ['code' => 'TUB-PVC-S-300', 'name' => 'Tubería PVC Sanitaria 3" Tramo 3m', 'price' => 54900, 'url' => 'tuberia_sanitaria.jpg'],
            ['code' => 'TUB-CPVC-050', 'name' => 'Tubería CPVC Agua Caliente CTS 1/2" x 6m', 'price' => 42000, 'url' => 'tuberia_cpvc.jpg'],
            ['code' => 'VAL-BOL-PVC-050', 'name' => 'Válvula de Bola PVC Lisa Cementar 1/2"', 'price' => 9800, 'url' => 'valvula_bola_pvc.jpg'],
            ['code' => 'VAL-BOL-PVC-075', 'name' => 'Válvula de Bola PVC Lisa Cementar 3/4"', 'price' => 14500, 'url' => 'valvula_bola_pvc.jpg'],
            ['code' => 'VAL-BOL-PVC-100', 'name' => 'Válvula de Bola PVC Roscada NPT 1"', 'price' => 22000, 'url' => 'valvula_roscada.jpg'],
            ['code' => 'VAL-CHK-PVC-100', 'name' => 'Válvula de Retención Check PVC 1"', 'price' => 28500, 'url' => 'valvula_check.jpg'],
            ['code' => 'ACC-COD-90-050', 'name' => 'Codo PVC Presión 90° Campana x Campana 1/2"', 'price' => 1200, 'url' => 'codo_pvc.jpg'],
            ['code' => 'ACC-TEE-PVC-050', 'name' => 'Tee PVC Presión 1/2" C x C', 'price' => 1850, 'url' => 'tee_pvc.jpg'],
            ['code' => 'ACC-UNI-UNI-050', 'name' => 'Unión Universal PVC Presión 1/2" Roscada', 'price' => 8200, 'url' => 'union_universal.jpg'],
        ];

        foreach ($ironmongerProducts as $prod) {
            Product::firstOrCreate(
                ['code' => $prod['code'], 'list_id' => $ironmongerList->id],
                [
                    'name' => $prod['name'],
                    'price' => $prod['price'],
                    'url' => $prod['url'],
                ]
            );
        }

        // 5. Seed Sample Order
        $sampleOrder = Order::firstOrCreate(
            ['delivery_address' => 'Zona Industrial Calle 13 # 68-20, Bogotá'],
            [
                'seller_id' => $distributor->id,
                'buyer_id' => $ironmonger->id,
                'seller_role' => 'Distribuidor',
                'buyer_role' => 'Ferretero',
                'status' => 'Pendiente',
                'currency' => 'COP',
                'total' => 546000,
                'coment' => 'Pedido de reposición mensual para mostrador de fontanería y construcción.',
            ]
        );

        $firstProd = Product::where('list_id', $ironmongerList->id)->first();
        if ($firstProd) {
            OrderProduct::firstOrCreate(
                ['order_id' => $sampleOrder->id, 'product_id' => $firstProd->id],
                [
                    'quantity' => 20,
                    'price' => $firstProd->price,
                    'currency' => 'COP',
                ]
            );
        }
    }
}
