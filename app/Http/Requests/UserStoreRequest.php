<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserStoreRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|min:6',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed',
        ];
    }

    public function messages()
    {
        return [
            'name.required'=> 'El campo nombre tiene que ser ingresado',
            'name.min'=> 'El campo nombre tiene que miínimo de 6 caracteres',
            'email.required'=> 'El campo email tiene que ser ingresado',
            'email.email'=> 'El campo email tiene que ser un email valido: prueba@prueba.com',
            'email.unique'=> 'Este email ya se encuentra asignado',
            'password.required'=> 'El campo password tiene que ser ingresado',
            'password.min'=> 'El campo password tiene que miínimo de 6 caracteres',
            'password.confirmed'=> 'Las contraseñas no coinciden',
        ];
    }
}
