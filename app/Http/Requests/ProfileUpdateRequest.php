<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique(User::class)->ignore($this->user()->id)],
            'phone' => ['required', 'string', 'max:20'],
            'country' => ['required', 'string', 'max:100'],
            'profile_photo' => ['nullable', 'image', 'max:1024'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'current_password' => ['required_with:password', 'current_password'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome é obrigatório',
            'email.required' => 'O e-mail é obrigatório',
            'email.email' => 'Digite um e-mail válido',
            'email.unique' => 'Este e-mail já está em uso',
            'phone.required' => 'O telefone é obrigatório',
            'country.required' => 'O país é obrigatório',
            'profile_photo.image' => 'O arquivo deve ser uma imagem',
            'profile_photo.max' => 'A imagem deve ter no máximo 1MB',
            'password.min' => 'A senha deve ter no mínimo 8 caracteres',
            'password.confirmed' => 'As senhas não conferem',
            'current_password.required_with' => 'A senha atual é obrigatória para alterar a senha',
            'current_password.current_password' => 'A senha atual está incorreta',
        ];
    }
} 