<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FacaSeuLeilao extends FormRequest
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
            'nome' => 'required',
            'processo' => 'requiredif:tipo_leilao,==,1',
            'dados_imovel' => 'requiredif:tipo_leilao,==,2',
            //'email' => 'email:rfc,dns',
            'email' => 'required|email:rfc',
            'telefone' => 'required'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'nome.required' => 'Informe seu nome',
            'processo.requiredif'  => 'Informe o número do processo',
            'dados_imovel.requiredif'  => 'Informe os dados do imóvel',
            'email.required'  => 'Informe seu e-mail',
            'telefone.required'  => 'Informe um telefone para contato',
        ];
    }
}
