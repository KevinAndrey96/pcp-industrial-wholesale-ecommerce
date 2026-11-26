<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Support\Facades\Auth;

class OrdersController extends Controller
{
    public function index()
    {
        $list_id = Auth::user()->price_list_id;
        $products = Product::where('list_id', '=', $list_id)->get();

        return view('orders.index', compact('products'));
    }
}
