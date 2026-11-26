<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\GeneralMail;
use App\Models\PriceList;
use App\Models\User;
use Exception;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/';

    private const COUNTRY_CURRENCIES = [
        'Colombia' => 'COP',
        'Peru' => 'PEN',
        'Mexico' => 'MXN',
    ];

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'phone' => ['required', 'string', 'max:30'],
            'country' => ['required', 'string', Rule::in(array_keys(self::COUNTRY_CURRENCIES))],
            'city' => ['required', 'string', 'max:100'],
            'document_type' => ['required', 'string', Rule::in(['CC', 'TI', 'NIT'])],
            'document_number' => ['required', 'string', 'max:30'],
            'establishment_name' => ['required', 'string', 'max:255'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $list = PriceList::where('currency', self::COUNTRY_CURRENCIES[$data['country']])
            ->where('role', User::ROLE_IRONMONGER)
            ->first();

        $user = User::create([
            'name' => $data['name'],
            'phone' => $data['phone'],
            'email' => $data['email'],
            'country' => $data['country'],
            'city' => $data['city'],
            'price_list_id' => $list ? $list->id : null,
            'role' => User::ROLE_IRONMONGER,
            'document_type' => $data['document_type'],
            'document_number' => $data['document_number'],
            'establishment_name' => $data['establishment_name'],
            'password' => Hash::make($data['password']),
        ]);

        $subject = 'Notificación de registro';
        $text = 'Sr o Sra '.$data['name'].' usted ha sido registrado en nuestra plataforma correctamente';

        try {
            Mail::to($data['email'])->send(new GeneralMail($subject, $text));
        } catch (Exception $e) {
            Log::warning('registration welcome email: '.$e->getMessage());
        }

        return $user;
    }
}
