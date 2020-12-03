<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class UserSite extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
      'pessoa', 'nome', 'cpf', 'rg', 'filiacao', 'profissao', 'empregador', 'sexo', 
      'estado_civil', 'tipo_uniao', 'conjuge', 'c_cpf', 'c_rg', 'razao_social', 'cnpj', 
      'insc_estadual', 'nome_fantasia', 'faturamento', 'segmento', 'socio', 's_cpf', 's_rg', 
      'email', 'telefone', 'celular', 'cep', 'endereco', 'numero', 'complemento', 'bairro', 'cidade', 
      'estado', 'informacoes', 'como_chegou', 'status', 'senha', 'changed_password'
    ];

    protected $table = 'cadastros';
    protected $primaryKey = 'codigo';
    public $timestamps = false; 

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        //'nome', 'telefone', 'email', 'tipo', 'senha',
        'senha'
    ];

    public function docPessoal()
    {
        return $this->hasOne('App\CadastrosDocumentos', 'idcadastro', 'codigo')
               ->latest('codigo');
    }

}