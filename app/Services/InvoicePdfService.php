<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade as PDF;

class InvoicePdfService
{
    /**
     * Render a professional PDF instance for quotation or purchase order.
     *
     * @return \Barryvdh\DomPDF\PDF
     */
    public function generateQuotationPdf(Order $order)
    {
        $order->load(['buyer', 'seller', 'items.product']);

        return PDF::loadView('pdf.quotation', [
            'order' => $order,
            'buyer' => $order->buyer,
            'seller' => $order->seller,
            'items' => $order->items,
        ]);
    }

    /**
     * Render and download a professional PDF invoice / purchase order.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadOrderPdf(Order $order)
    {
        $order->load(['buyer', 'seller', 'items.product']);

        $pdf = PDF::loadView('pdf.order', [
            'order' => $order,
            'buyer' => $order->buyer,
            'seller' => $order->seller,
            'items' => $order->items,
        ]);

        $filename = "Orden_Compra_PCP_{$order->id}.pdf";

        return $pdf->download($filename);
    }

    /**
     * Render and download a formal B2B quotation PDF.
     *
     * @return \Illuminate\Http\Response
     */
    public function downloadQuotationPdf(Order $order)
    {
        $pdf = $this->generateQuotationPdf($order);
        $filename = "Cotizacion_Industrial_PCP_{$order->id}.pdf";

        return $pdf->download($filename);
    }
}
