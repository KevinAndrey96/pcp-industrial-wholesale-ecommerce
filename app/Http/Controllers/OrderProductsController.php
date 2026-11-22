<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\PriceList;
use App\Models\Product;
use App\Models\User;
use App\Mail\GeneralMail;
use App\Services\OrderService;
use App\Services\InvoicePdfService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class OrderProductsController extends Controller
{
    protected OrderService $orderService;
    protected InvoicePdfService $pdfService;

    /**
     * OrderProductsController constructor.
     *
     * @param OrderService $orderService
     * @param InvoicePdfService $pdfService
     */
    public function __construct(OrderService $orderService, InvoicePdfService $pdfService)
    {
        $this->middleware('auth');
        $this->orderService = $orderService;
        $this->pdfService = $pdfService;
    }

    /**
     * Display the shopping cart trolley with selected products.
     *
     * @param Request $request
     * @return \Illuminate\View\View|\Illuminate\Http\RedirectResponse
     */
    public function trolley(Request $request)
    {
        if (empty($request->cart)) {
            return redirect('/orders')->with('error', 'No ha seleccionado ningún producto.');
        }

        $ids = array_filter(explode(',', $request->cart));

        if (empty($ids)) {
            return redirect('/orders');
        }

        $dataProduct = Product::whereIn('id', $ids)->orderBy('name', 'asc')->get();

        if (Auth::user()->isIronmonger()) {
            $distributors = User::where('role', User::ROLE_DISTRIBUTOR)
                ->where('city', Auth::user()->city)
                ->get();

            if ($distributors->isEmpty()) {
                $distributors = User::where('role', User::ROLE_DISTRIBUTOR)->get();
            }

            return view('orderProducts.index', compact('dataProduct', 'distributors'));
        }

        return view('orderProducts.index', compact('dataProduct'));
    }

    /**
     * Process checkout, create the order, and dispatch notifications.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function bill(Request $request)
    {
        $request->validate([
            'cart1' => 'required|string',
            'cart2' => 'required|string',
            'address' => 'required|string|max:200',
            'city' => 'nullable|string|max:100',
        ]);

        $quantities = explode(',', $request->cart1);
        $ids = explode(',', $request->cart2);

        if (count($quantities) !== count($ids)) {
            return redirect('/orders')->with('error', 'Error en la correspondencia de cantidades y productos.');
        }

        $cartItems = [];
        foreach ($ids as $index => $id) {
            $qty = isset($quantities[$index]) ? (int) $quantities[$index] : 0;
            if ($qty > 0 && ! empty($id)) {
                $cartItems[] = [
                    'product_id' => (int) $id,
                    'quantity' => $qty,
                ];
            }
        }

        if (empty($cartItems)) {
            return redirect('/orders')->with('error', 'Debe especificar cantidades válidas para los productos.');
        }

        try {
            $buyer = Auth::user();
            $city = $request->city ?: ($buyer->city ?: 'Principal');
            $sellerId = $buyer->isIronmonger() ? (int) $request->seller_id : null;

            $order = $this->orderService->createOrder(
                $buyer,
                $cartItems,
                $request->address,
                $city,
                $request->coment,
                $sellerId
            );

            // Send role-specific notification emails
            if ($buyer->isIronmonger()) {
                $subject = 'Transferencia solicitada exitosamente';
                $text = 'Su solicitud de pedido #'.$order->id.' fue enviada al distribuidor seleccionado. En breve recibirá la cotización formal con el valor final aprobado.';
                
                try {
                    Mail::to($buyer->email)->send(new GeneralMail($subject, $text));

                    if ($order->seller && $order->seller->email) {
                        $sellerSubject = 'Nueva solicitud de transferencia B2B';
                        $sellerText = 'La ferretería '.$buyer->establishment_name.' ('.$buyer->name.') acaba de solicitar una transferencia de pedido #'.$order->id.'. Por favor revise la plataforma para generar la cotización.';
                        Mail::to($order->seller->email)->send(new GeneralMail($sellerSubject, $sellerText));
                    }
                } catch (Exception $mailEx) {
                    Log::warning('Email delivery failed: '.$mailEx->getMessage());
                }

                return redirect('/orders')->with('messageSuccess', 'Su pedido ha sido enviado al distribuidor seleccionado con éxito.');
            }

            // Direct order by distributor to manufacturer (Administrator)
            try {
                $pdf = $this->pdfService->generateQuotationPdf($order);
                $subject = "Envío de cotización comercial - Pedido #".$order->id;
                $text = 'Apreciado Distribuidor,<br><br>En respuesta a su solicitud, adjuntamos la cotización de su pedido #'.$order->id.'.<br><br>Cordialmente,<br>PCP Industrial Wholesale.';

                Mail::send('mails.general', compact('subject', 'text'), function ($mail) use ($pdf, $buyer, $subject) {
                    $mail->to($buyer->email);
                    $mail->subject($subject);
                    $mail->attachData($pdf->output(), 'cotizacion_pedido_'.$buyer->id.'.pdf');
                });
            } catch (Exception $mailEx) {
                Log::warning('Quotation mail delivery failed: '.$mailEx->getMessage());
            }

            return redirect('/orders')->with('messageSuccess', 'Pedido solicitado con éxito. Cotización generada.');

        } catch (Exception $e) {
            Log::error('Order creation failed: '.$e->getMessage());
            return redirect('/orders')->with('error', 'Ocurrió un error al procesar el pedido: '.$e->getMessage());
        }
    }

    /**
     * List incoming orders for Administrator or Distributor.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function showOrder(Request $request)
    {
        $role = $request->input('role', Auth::user()->role);

        if (Auth::user()->isAdmin()) {
            $orders = Order::with(['buyer', 'seller'])->orderBy('created_at', 'desc')->get();
        } else {
            $orders = Order::with(['buyer', 'seller'])
                ->where('seller_id', Auth::id())
                ->orderBy('created_at', 'desc')
                ->get();
        }

        return view('orders.showOrder', compact('orders'));
    }

    /**
     * Show detailed view of an incoming order.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function detailOrder(Request $request)
    {
        $order = Order::with(['items.product', 'buyer', 'seller'])->findOrFail($request->id);
        $orders = $order->items;
        $products = $orders->map(function ($item) {
            return $item->product;
        })->values();

        $updaOrder = $orders->first();
        $idOrder = $order->id;
        $status = $order->status;

        return view('orders.detailOrder', compact('idOrder', 'status', 'orders', 'products', 'updaOrder'))->with('id', $request->id);
    }

    /**
     * Show detailed view of a customer's own order.
     *
     * @param Request $request
     * @return \Illuminate\View\View
     */
    public function detailMyOrder(Request $request)
    {
        $order = Order::with(['items.product', 'buyer', 'seller'])->findOrFail($request->id);
        $orders = $order->items;
        $products = $orders->map(function ($item) {
            return $item->product;
        })->values();

        $updaOrder = $orders->first();
        $idOrder = $order->id;
        $status = $order->status;

        return view('orders.detailMyOrder', compact('idOrder', 'status', 'orders', 'products', 'updaOrder'))->with('id', $request->id);
    }

    /**
     * Update order status with audit transition.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function changeStatus(Request $request)
    {
        $order = Order::findOrFail($request->id);
        $this->orderService->transitionStatus($order, $request->status, Auth::user());

        if ($order->buyer && $order->buyer->email) {
            $subject = "Actualización de estado en su pedido #".$order->id;
            $text = "Estimado/a {$order->buyer->name}, el estado de su pedido con identificador #{$order->id} ha cambiado a: <strong>{$order->status}</strong>.";
            try {
                Mail::to($order->buyer->email)->send(new GeneralMail($subject, $text));
            } catch (Exception $e) {
                Log::warning('Status notification email failed: '.$e->getMessage());
            }
        }

        return redirect('showOrders?role='.Auth::user()->role)->with('messageSuccess', 'Estado actualizado a: '.$order->status);
    }

    /**
     * Display current user's purchase orders.
     *
     * @return \Illuminate\View\View
     */
    public function myOrders()
    {
        $orders = Order::with(['seller', 'buyer', 'items.product'])
            ->where('buyer_id', Auth::id())
            ->orderBy('id', 'desc')
            ->get();

        return view('orders.myOrders', compact('orders'));
    }

    /**
     * Process wholesale quotation pricing adjustments and notify buyer.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function quotation(Request $request)
    {
        $ids = explode(',', $request->ids);
        $prices = explode(',', $request->prices);
        $order = Order::with(['buyer', 'seller', 'items.product'])->findOrFail($request->idOrder);

        $newTotal = 0;

        foreach ($ids as $index => $productId) {
            if (isset($prices[$index])) {
                $unitPrice = (float) $prices[$index];
                $orderProduct = OrderProduct::where('order_id', $order->id)
                    ->where('product_id', $productId)
                    ->first();

                if ($orderProduct) {
                    $orderProduct->price = $unitPrice;
                    $orderProduct->save();
                    $newTotal += $unitPrice * $orderProduct->quantity;
                }
            }
        }

        $order->total = $newTotal;
        $order->status = Order::STATUS_APPROVED;
        $order->save();

        try {
            $pdf = $this->pdfService->generateQuotationPdf($order);
            $buyer = $order->buyer;
            $seller = $order->seller;
            $subject = "Cotización aprobada para el pedido #".$order->id;
            $text = "Estimado cliente,<br><br>Su distribuidor {$seller->name} ha emitido la cotización formal con identificador interno #{$order->id}.<br><br>En el documento adjunto encontrará el desglose detallado.";

            Mail::send('mails.general', compact('subject', 'text'), function ($mail) use ($pdf, $buyer, $subject) {
                $mail->to($buyer->email);
                $mail->subject($subject);
                $mail->attachData($pdf->output(), 'cotizacion_pedido_'.$buyer->id.'.pdf');
            });
        } catch (Exception $e) {
            Log::warning('Quotation send failed: '.$e->getMessage());
        }

        return redirect()->back()->with('success', 'Cotización generada y enviada satisfactoriamente.');
    }

    /**
     * Download the official quotation PDF document.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function downloadPDF(Request $request)
    {
        $orderId = $request->id_order ?: $request->id;
        $order = Order::with(['buyer', 'seller', 'items.product'])->findOrFail($orderId);

        return $this->pdfService->downloadQuotationPdf($order);
    }

    /**
     * View all ironmonger transfers for administrative monitoring.
     *
     * @return \Illuminate\View\View
     */
    public function allTransfers()
    {
        $orders = Order::where('buyer_role', User::ROLE_IRONMONGER)
            ->with(['buyer', 'seller', 'items.product'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('orders.showOrder', compact('orders'));
    }

    /**
     * Return to order screen with preserved state.
     *
     * @return \Illuminate\View\View
     */
    public function orderReturn()
    {
        $ids = session('Sids', []);
        session()->forget('Sids');

        return view('orders.index', compact('ids'));
    }
}
