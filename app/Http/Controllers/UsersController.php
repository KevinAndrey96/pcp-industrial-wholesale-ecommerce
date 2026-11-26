<?php

namespace App\Http\Controllers;

use App\Imports\usersImport;
use App\Models\Order;
use App\Models\PriceList;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class UsersController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display a listing of users, optionally filtered by role.
     *
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {
        $role = $request->input('role');
        $query = User::with('priceList')->orderBy('id', 'desc');

        if ($role && $role !== 'all') {
            $query->where('role', $role);
        }

        $admins = $query->get();

        return view('users.index', compact('admins'));
    }

    /**
     * Show the form for creating a new user.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $distributorLists = PriceList::where('role', User::ROLE_DISTRIBUTOR)->get();
        $ironmongerLists = PriceList::where('role', User::ROLE_IRONMONGER)->get();

        return view('users.create', compact('distributorLists', 'ironmongerLists'));
    }

    /**
     * Store a newly created user in storage.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        if (! Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'No tiene permisos para crear usuarios.');
        }

        $request->validate([
            'name' => 'required|string|max:100',
            'role' => 'required|string|in:Administrador,Distribuidor,Ferretero',
            'document_type' => 'required|string|max:50',
            'document_number' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:100|unique:users,email',
            'country' => 'required|string|max:40',
            'city' => 'required|string|max:40',
            'establishment_name' => 'required|string|max:100',
            'password' => 'required|string|min:6',
            'price_list_id' => 'nullable|exists:price_lists,id',
            'image' => 'nullable|file|image|max:2048',
        ], [
            'name.required' => 'El nombre completo o razón social es obligatorio.',
            'email.unique' => 'El correo electrónico ya se encuentra registrado.',
            'password.min' => 'La contraseña debe tener mínimo 6 caracteres.',
        ]);

        $userData = $request->except(['_token', 'password', 'image']);
        $userData['password'] = Hash::make($request->input('password'));

        if ($request->hasFile('image')) {
            $userData['image'] = $request->file('image')->store('profilePictures', 'public');
        }

        User::create($userData);

        return redirect('/users/create')->with('messageSuccess', 'Usuario registrado con éxito.');
    }

    /**
     * Show the form for editing the specified user.
     *
     * @param  int|string  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        $distributorLists = PriceList::where('role', User::ROLE_DISTRIBUTOR)->get();
        $ironmongerLists = PriceList::where('role', User::ROLE_IRONMONGER)->get();
        $list = $user->price_list_id ? PriceList::find($user->price_list_id) : null;

        return view('users.edit', compact('user', 'distributorLists', 'ironmongerLists', 'list'));
    }

    /**
     * Update the specified user in storage.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        if (! Auth::user()->isAdmin()) {
            return redirect()->back()->with('error', 'No tiene permisos para modificar usuarios.');
        }

        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:users,email,'.$id,
            'phone' => 'required|string|max:20',
            'country' => 'required|string|max:40',
            'city' => 'required|string|max:40',
            'establishment_name' => 'required|string|max:100',
            'password' => 'nullable|string|min:6',
            'price_list_id' => 'nullable|exists:price_lists,id',
            'image' => 'nullable|file|image|max:2048',
        ]);

        $userData = $request->except(['_token', '_method', 'password', 'image']);

        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->input('password'));
        }

        if ($request->hasFile('image')) {
            if ($user->image && Storage::disk('public')->exists($user->image)) {
                Storage::disk('public')->delete($user->image);
            }
            $userData['image'] = $request->file('image')->store('profilePictures', 'public');
        }

        $user->update($userData);

        return redirect('/users/'.$id.'/edit')->with('messageSuccess', 'Usuario modificado exitosamente.');
    }

    /**
     * Remove the specified user from storage.
     *
     * @param  int|string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        if (! Auth::user()->isAdmin()) {
            return back()->with('error', 'No tiene permisos para eliminar usuarios.');
        }

        if ((int) $id === (int) Auth::id()) {
            return back()->with('error', 'No puede eliminarse a sí mismo de la plataforma.');
        }

        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('deleteSuccess', 'El usuario ha sido eliminado exitosamente.');
    }

    /**
     * Display password edit form.
     *
     * @param  int|string  $id
     * @return \Illuminate\View\View
     */
    public function passwordEdit($id)
    {
        abort_unless((int) $id === Auth::id(), 404);
        $user = Auth::user();

        return view('users.passwordEdit', compact('user'));
    }

    /**
     * Process password update for logged-in user.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function passwordUpda(Request $request)
    {
        $request->validate([
            'oldPass' => 'required',
            'newPass1' => 'required|min:6',
            'newPass2' => 'required|same:newPass1',
        ], [
            'newPass2.same' => 'Las nuevas contraseñas no coinciden.',
            'newPass1.min' => 'La nueva contraseña debe tener mínimo 6 caracteres.',
        ]);

        $user = Auth::user();

        if (! Hash::check($request->oldPass, $user->password)) {
            return redirect('/users/'.$user->id.'/passwordEdit')
                ->with('failChange', 'La contraseña anterior no es correcta.');
        }

        $user->password = Hash::make($request->newPass1);
        $user->save();

        return redirect('/users/'.$user->id.'/passwordEdit')
            ->with('messageSuccess', 'Contraseña modificada con éxito.');
    }

    /**
     * View for choosing user list import.
     *
     * @return \Illuminate\View\View
     */
    public function chooseListUser()
    {
        return view('users.chooseListUser');
    }

    /**
     * Import users from Excel.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function importUsers(Request $request)
    {
        $request->validate([
            'list' => 'required|file|mimes:xlsx,xls,csv|max:10240',
        ]);

        Excel::import(new usersImport(), $request->file('list'));

        return back()->with('messageSuccess', 'Importación de usuarios completada satisfactoriamente.');
    }

    /**
     * Display ironmongers associated with a distributor based on purchase order history.
     *
     * @return \Illuminate\View\View
     */
    public function ironAsoc(Request $request)
    {
        $distributorId = Auth::user()->isAdmin() && $request->id ? $request->id : Auth::id();
        $buyerIds = Order::where('seller_id', $distributorId)->pluck('buyer_id')->unique()->filter();
        $users = User::whereIn('id', $buyerIds)->orderBy('name', 'asc')->get();

        return view('users.ironAsoc', compact('users'));
    }
}
