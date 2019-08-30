<?php

namespace App\Http\Requests;

use App\User;
use Illuminate\Foundation\Http\FormRequest;

class UserUpdateRequest extends FormRequest
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
        $user = $this->route('user');
        return [
            'name' => 'min:6',
            'email' => 'email|unique:users,email,' . $user->id,
            'password' => 'min:6|confirmed',
            'admin' => 'in:' . User::USER_ADMIN . ',' . User::USER_REGULAR,
        ];
    }

    public function messages()
    {
        return [
            'name.min' => 'El campo nombre tiene que miínimo de 6 caracteres',
            'email.email' => 'El campo email tiene que ser un email valido: prueba@prueba.com',
            'email.unique' => 'Este email ya se encuentra asignado',
            'password.min' => 'El campo password tiene que miínimo de 6 caracteres',
        ];
    }
}
