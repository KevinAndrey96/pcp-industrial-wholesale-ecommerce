<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class OrderService
{
    /**
     * Create a new wholesale order or quotation from cart data.
     *
     * @param  array  $cartItems Array of ['product_id' => int, 'quantity' => int]
     *
     * @throws InvalidArgumentException
     */
    public function createOrder(
        User $buyer,
        array $cartItems,
        string $address,
        string $city,
        ?string $comment = null,
        ?int $designatedSellerId = null
    ): Order {
        if (empty($cartItems)) {
            throw new InvalidArgumentException('El carrito de compras no puede estar vacío.');
        }

        // Determine seller role and seller ID
        if ($buyer->isIronmonger()) {
            $sellerRole = User::ROLE_DISTRIBUTOR;
            $sellerId = $designatedSellerId;

            // Fallback: assign first distributor in same city or primary distributor
            if (! $sellerId) {
                $defaultSeller = User::where('role', User::ROLE_DISTRIBUTOR)
                    ->where('city', $buyer->city)
                    ->first() ?? User::where('role', User::ROLE_DISTRIBUTOR)->first();
                $sellerId = $defaultSeller ? $defaultSeller->id : 1;
            }
        } else {
            $sellerRole = User::ROLE_ADMIN;
            $primaryAdmin = User::where('role', User::ROLE_ADMIN)->first();
            $sellerId = $primaryAdmin ? $primaryAdmin->id : 1;
        }

        $priceList = $buyer->priceList ?? PriceList::first();
        $currency = $priceList ? $priceList->currency : 'COP';

        return DB::transaction(function () use (
            $buyer,
            $cartItems,
            $address,
            $city,
            $comment,
            $sellerId,
            $sellerRole,
            $currency
        ) {
            $productIds = array_column($cartItems, 'product_id');
            $products = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $total = 0;
            $orderItemsData = [];

            foreach ($cartItems as $item) {
                $productId = $item['product_id'];
                $quantity = max(1, (int) $item['quantity']);

                if (! isset($products[$productId])) {
                    continue;
                }

                $product = $products[$productId];
                $linePrice = (float) $product->price;
                $lineTotal = $linePrice * $quantity;
                $total += $lineTotal;

                $orderItemsData[] = [
                    'product_id' => $productId,
                    'quantity' => $quantity,
                    'price' => $linePrice,
                    'currency' => $currency,
                ];
            }

            $order = Order::create([
                'seller_id' => $sellerId,
                'buyer_id' => $buyer->id,
                'seller_role' => $sellerRole,
                'buyer_role' => $buyer->role,
                'status' => Order::STATUS_INITIATED,
                'currency' => $currency,
                'total' => $total,
                'coment' => $comment,
                'delivery_address' => $address,
                'city' => $city,
            ]);

            foreach ($orderItemsData as $itemData) {
                $itemData['order_id'] = $order->id;
                OrderProduct::create($itemData);
            }

            return $order->load(['items.product', 'buyer', 'seller']);
        });
    }

    /**
     * Transition order status with audit validation.
     */
    public function transitionStatus(Order $order, string $newStatus, User $actingUser): Order
    {
        $validStatuses = [
            Order::STATUS_INITIATED,
            Order::STATUS_PENDING,
            Order::STATUS_APPROVED,
            Order::STATUS_IN_TRANSIT,
            Order::STATUS_DELIVERED,
            Order::STATUS_REJECTED,
            Order::STATUS_RETURN,
        ];

        if (! in_array($newStatus, $validStatuses, true)) {
            throw new InvalidArgumentException("Estado '{$newStatus}' no es un estado válido en el flujo de pedidos.");
        }

        $order->status = $newStatus;
        $order->save();

        return $order;
    }

    /**
     * Retrieve orders based on user permissions and role filtering.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getOrdersForUser(User $user, ?string $filterRole = null)
    {
        if ($user->isAdmin()) {
            $query = Order::with(['buyer', 'seller', 'items.product'])->orderBy('created_at', 'desc');
            if ($filterRole && $filterRole !== 'all') {
                $query->where('buyer_role', $filterRole);
            }

            return $query->get();
        }

        if ($user->isDistributor()) {
            return Order::with(['buyer', 'seller', 'items.product'])
                ->where(function ($q) use ($user) {
                    $q->where('seller_id', $user->id)
                      ->orWhere('buyer_id', $user->id);
                })
                ->orderBy('created_at', 'desc')
                ->get();
        }

        // Ironmonger: their own purchase orders
        return Order::with(['buyer', 'seller', 'items.product'])
            ->where('buyer_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
