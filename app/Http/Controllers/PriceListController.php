<?php

namespace App\Http\Controllers;

use App\Models\PriceList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PriceListController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of price lists filtered by role.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $role = $request->input('role');
        $query = PriceList::query();

        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }

        $lists = $query->orderBy('id', 'asc')->get();

        return view('lists.index', compact('lists'));
    }

    /**
     * Show the form for creating a new price list.
     *
     * @return \Illuminate\View\View
     */
    public function create(Request $request)
    {
        return view('lists.createList', [
            'role' => $request->input('role', 'Distribuidor'),
        ]);
    }

    /**
     * Store a newly created price list in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|string|in:Distribuidor,Ferretero,Administrador',
            'currency' => 'nullable|string|max:10',
        ], [
            'name.required' => 'El nombre de la lista de precios es requerido.',
            'role.required' => 'El rol asociado es requerido.',
        ]);

        if (empty($validated['currency'])) {
            $validated['currency'] = 'COP';
        }

        PriceList::create($validated);

        return redirect('/list/createList?role='.$request->role)
            ->with('messageSuccess', 'Lista de precios registrada con éxito.');
    }

    /**
     * Show the form for editing the specified price list.
     *
     * @param  int|string  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $list = PriceList::findOrFail($id);

        return view('lists.editList', compact('list'));
    }

    /**
     * Update the specified price list in storage.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $list = PriceList::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'currency' => 'nullable|string|max:10',
        ]);

        $list->update($validated);

        return redirect('home')->with('Mensaje', 'La lista de precios ha sido modificada con éxito.');
    }

    /**
     * Remove the specified price list from storage.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (! Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'No tiene permisos para eliminar listas de precios.');
        }

        $list = PriceList::findOrFail($id);
        $list->delete();

        return redirect()->back()->with('deleteSuccess', 'Lista de precios eliminada correctamente.');
    }
}
