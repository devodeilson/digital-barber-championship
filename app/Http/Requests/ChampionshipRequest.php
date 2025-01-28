<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ChampionshipRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->user()->isAdmin();
    }

    public function rules()
    {
        $rules = [
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'rules' => 'required|string',
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after:start_date',
            'entry_fee' => 'required|numeric|min:0',
            'prize_pool' => 'required|numeric|min:0',
            'max_participants' => 'required|integer|min:1',
            'banner_image' => 'nullable|image|max:2048',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id'
        ];

        if ($this->isMethod('PUT')) {
            $rules['start_date'] = 'required|date';
            $rules['banner_image'] = 'nullable|image|max:2048';
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'name.required' => 'O nome do campeonato é obrigatório',
            'description.required' => 'A descrição é obrigatória',
            'rules.required' => 'As regras são obrigatórias',
            'start_date.required' => 'A data de início é obrigatória',
            'start_date.after' => 'A data de início deve ser posterior a hoje',
            'end_date.required' => 'A data de término é obrigatória',
            'end_date.after' => 'A data de término deve ser posterior à data de início',
            'entry_fee.required' => 'A taxa de inscrição é obrigatória',
            'entry_fee.numeric' => 'A taxa de inscrição deve ser um valor numérico',
            'prize_pool.required' => 'O valor da premiação é obrigatório',
            'prize_pool.numeric' => 'O valor da premiação deve ser um valor numérico',
            'max_participants.required' => 'O número máximo de participantes é obrigatório',
            'max_participants.integer' => 'O número máximo de participantes deve ser um número inteiro',
            'banner_image.image' => 'O arquivo deve ser uma imagem',
            'banner_image.max' => 'A imagem não pode ter mais que 2MB',
            'categories.required' => 'Selecione pelo menos uma categoria',
            'categories.array' => 'As categorias devem ser enviadas em formato de array',
            'categories.min' => 'Selecione pelo menos uma categoria',
            'categories.*.exists' => 'Uma das categorias selecionadas não existe'
        ];
    }
} 