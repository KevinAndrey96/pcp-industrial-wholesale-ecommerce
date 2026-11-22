<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
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
            'name' => 'required|string|max:150',
            'code' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'list_id' => 'required|exists:price_lists,id',
            'image' => 'nullable|string|max:255',
        ];
    }

    /**
     * Get custom error messages.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'El nombre del producto es obligatorio.',
            'code.required' => 'El código de referencia es obligatorio.',
            'price.required' => 'El precio del producto es obligatorio.',
            'list_id.exists' => 'La lista de precios seleccionada no existe.',
        ];
    }
}
