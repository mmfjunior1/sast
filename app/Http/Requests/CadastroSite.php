<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Auth;

class CadastroSite extends FormRequest
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
            'nome' => 'required_unless:pessoa,"j"',
            'cpf' => 'required_unless:pessoa,"j"',
            'rg' => 'required_unless:pessoa,"j"',
            'profissao' => 'required_unless:pessoa,"j"',
            'razao_social' => 'required_unless:pessoa,"f"',
            'cnpj' => 'required_unless:pessoa,"f"',
            'socio' => 'required_unless:pessoa,"f"',
            's_cpf' => 'required_unless:pessoa,"f"',
            's_rg' => 'required_unless:pessoa,"f"',
            'data_nascimento' => 'date_format:"d/m/Y"|required_unless:pessoa,"j"',
            'email' => 'required|email:rfc|unique:cadastros,email,' . (\Auth::user()->codigo  ?? 0).',codigo',
            'apelido' => 'required|unique:cadastros,apelido,' . (\Auth::user()->codigo  ?? 0).',codigo',
            'celular' => 'required_without:telefone',
            'desc_como_chegou' => 'required_if:como_chegou,"5"',
            'cep' => 'required',
            'endereco' => 'required',
            'numero' => 'required',
            'bairro' => 'required',
            'cidade' => 'required',
            'estado' => 'required',
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
            'nome.required_unless' => 'Você deve informar seu nome.',
            'data_nascimento.required_unless' => 'Informe a data de seu nascimento.',
            'data_nascimento.date_format' => 'A data de nascimento informada não é válida.',
            'cpf.required_unless'  => 'Informe seu CPF.',
            'rg.required_unless'  => 'Informe seu RG.',
            'profissao.required_unless'  => 'Informe sua profissão.',
            'razao_social.required_unless'  => 'Informe a razão social da empresa.',
            'cnpj.required_unless'  => 'Informe o CNPJ da empresa.',
            'socio.required_unless'  => 'Informe o nome de um dos sócios da empresa.',
            's_cpf.required_unless'  => 'Informe o CPF do sócio já informado no campo acima.',
            's_rg.required_unless'  => 'Informe o RG do sócio já informado no campo acima.',
            'email.required'  => 'Você deve informar um e-mail válido.',
            'email.unique' => 'O endereço :input já está cadastrado em nossa base de dados.',
            'apelido.unique' => 'O apelido :input já está sendo usado por outro usuário do site. Informe outro apelido.',
            'celular.required_without' => 'Informe ao menos número de telefone para contato.',
            'cep.required' => 'Informe o seu CEP.',
            'endereco.required' => 'Informe seu endereço.',
            'numero.required' => 'Informe o número do endereço informado.',
            'bairro.required' => 'Informe o bairro.',
            'cidade.required' => 'Informe a cidade.',
            'estado.required' => 'Informe o estado.',
            'desc_como_chegou.required_if' => 'Descreva como chegou no site',
        ];
    }
}
