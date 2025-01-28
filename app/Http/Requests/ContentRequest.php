<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ContentRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check();
    }

    public function rules()
    {
        $rules = [
            'championship_id' => 'required|exists:championships,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'media' => 'required|file|max:10240|mimes:jpeg,png,jpg,gif,mp4,mov,avi',
            'category_id' => 'required|exists:categories,id'
        ];

        if ($this->isMethod('PUT')) {
            $rules['media'] = 'nullable|file|max:10240|mimes:jpeg,png,jpg,gif,mp4,mov,avi';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'championship_id.required' => 'O campeonato é obrigatório',
            'championship_id.exists' => 'O campeonato selecionado não existe',
            'title.required' => 'O título é obrigatório',
            'title.max' => 'O título não pode ter mais que 255 caracteres',
            'description.required' => 'A descrição é obrigatória',
            'media.required' => 'O arquivo de mídia é obrigatório',
            'media.file' => 'O arquivo de mídia é inválido',
            'media.max' => 'O arquivo não pode ter mais que 10MB',
            'media.mimes' => 'O arquivo deve ser uma imagem (jpeg, png, jpg, gif) ou vídeo (mp4, mov, avi)',
            'category_id.required' => 'A categoria é obrigatória',
            'category_id.exists' => 'A categoria selecionada não existe'
        ];
    }
} 