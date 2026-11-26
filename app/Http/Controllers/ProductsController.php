<?php

namespace App\Http\Controllers;

use App\Exports\ProductsExport;
use App\Imports\ProductsImport;
use App\Models\PriceList;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class ProductsController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of products for a specific price list.
     *
     * @param  int|string  $id
     * @return \Illuminate\View\View
     */
    public function index(Request $request, $id)
    {
        $list = PriceList::findOrFail($id);
        $products = Product::where('list_id', $id)
            ->with('priceList')
            ->orderBy('id', 'asc')
            ->get();

        return view('products.index', compact('products', 'list'));
    }

    /**
     * Show the form for creating a new product in a price list.
     *
     * @param  int|string  $id
     * @return \Illuminate\View\View
     */
    public function create(Request $request, $id)
    {
        $list = PriceList::findOrFail($id);

        return view('products.create', compact('list'));
    }

    /**
     * Store a newly created product in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'list_id' => 'required|exists:price_lists,id',
            'image' => 'nullable|string|max:255',
        ], [
            'name.required' => 'El nombre del producto es requerido.',
            'code.required' => 'El código de referencia es requerido.',
            'price.required' => 'El precio del producto es requerido.',
        ]);

        Product::create($validated);

        return redirect('/products/create/'.$request->list_id)
            ->with('messageSuccess', 'Producto registrado con éxito.');
    }

    /**
     * Show the form for editing the specified product.
     *
     * @param  int|string  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $product = Product::with('priceList')->findOrFail($id);

        return view('products.edit', compact('product'));
    }

    /**
     * Update the specified product in storage.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'image' => 'nullable|string|max:255',
        ]);

        $product->update($validated);

        return redirect('/products/'.$id.'/edit')
            ->with('message', 'El producto ha sido modificado con éxito.');
    }

    /**
     * Remove the specified product from storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Product $product)
    {
        if (Auth::user()->isAdmin()) {
            $listId = $product->list_id;
            $product->delete();

            return redirect('/products/'.$listId)->with('deleteSuccess', 'Producto eliminado exitosamente.');
        }

        return redirect()->back()->with('error', 'No tiene permisos para eliminar productos.');
    }

    /**
     * Display product catalog for Distributor and Ironmonger based on assigned price list.
     *
     * @return \Illuminate\View\View
     */
    public function productsDistriIron()
    {
        $listId = Auth::user()->price_list_id;
        $products = Product::where('list_id', $listId)->orderBy('name', 'asc')->get();

        return view('productsDistIron.index', compact('products'));
    }

    /**
     * View for choosing price lists for import/export.
     *
     * @return \Illuminate\View\View
     */
    public function chooseList(Request $request)
    {
        if ($request->id === 'all') {
            return view('lists.chooseList')->with('id', $request->id);
        }

        $list = PriceList::findOrFail($request->id);

        return view('lists.chooseList', compact('list'));
    }

    /**
     * Export price list products to Excel.
     *
     * @param  int|string  $id
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function export($id)
    {
        if ($id === 'all') {
            return Excel::download(new ProductsExport($id), 'LISTA_PRECIOS_GENERAL_PCP.xlsx');
        }

        $list = PriceList::findOrFail($id);
        $cleanName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $list->name);

        return Excel::download(new ProductsExport($id), 'LISTA_PCP_'.$cleanName.'.xlsx');
    }

    /**
     * Import products from Excel into specified price list.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function import(Request $request, $id)
    {
        $request->validate([
            'list' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new ProductsImport($id), $request->file('list'));

        return back()->with('messageSuccess', 'Importación de productos completada satisfactoriamente.');
    }
}
