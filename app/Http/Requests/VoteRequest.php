<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class VoteRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        return [
            'rating' => 'required|integer|between:1,5',
            'comment' => 'nullable|string|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'rating.required' => 'A avaliação é obrigatória',
            'rating.integer' => 'A avaliação deve ser um número inteiro',
            'rating.between' => 'A avaliação deve ser entre 1 e 5',
            'comment.max' => 'O comentário não pode ter mais que 1000 caracteres'
        ];
    }
} 