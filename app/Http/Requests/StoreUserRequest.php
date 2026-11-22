<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return $this->user() && $this->user()->isAdmin();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:100',
            'role' => 'required|string|in:Administrador,Distribuidor,Ferretero',
            'document_type' => 'required|string|max:50',
            'document_number' => 'required|string|max:50',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:100|unique:users,email',
            'country' => 'required|string|max:60',
            'city' => 'required|string|max:60',
            'establishment_name' => 'required|string|max:100',
            'password' => 'required|string|min:6',
            'price_list_id' => 'nullable|exists:price_lists,id',
            'image' => 'nullable|file|image|max:2048',
        ];
    }
}
