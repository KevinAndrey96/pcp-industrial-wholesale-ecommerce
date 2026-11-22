<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard or redirect to administrative panel.
     *
     * @return Renderable|RedirectResponse
     */
    public function index()
    {
        if (Auth::user()->isAdmin()) {
            return redirect('/users?role=all');
        }

        return view('home');
    }
}
