<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cadastros extends Model
{
    use Notifiable;
    use SoftDeletes;

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
        'estado', 'informacoes', 'como_chegou', 'status', 'senha','desc_como_chegou'
    ];

    protected $table = 'cadastros';
    protected $primaryKey = 'codigo';
    protected $dates = ['deleted_at'];

    public function docPessoal()
    {
        return $this->hasOne('App\CadastrosDocumentos', 'idcadastro', 'codigo')
               ->whereNotNull('arq_pessoal')->latest('codigo');
    }

    public function docEndereco()
    {
        return $this->hasOne('App\CadastrosDocumentos', 'idcadastro', 'codigo')
               ->whereNotNull('arq_residencia')->latest('codigo');
    }

    public function habilitados()
    {
        return $this->hasMany('App\CadastrosHabilitados', 'idcadastro', 'codigo')
                ->join('lotes', 'cadastros_habilitados.idlote', '=', 'lotes.codigo')
                ->select('cadastros_habilitados.*', 'lotes.titulo', 'lotes.encerrado', 'lotes.codigo')
                ->orderBy('data_habilitacao', 'desc');
    }
    public function lances()
    {
        return $this->hasMany('App\Lances', 'idcadastro', 'codigo')
                ->join('lotes', 'lances.idlote', '=', 'lotes.codigo')
                ->join('leiloes', 'lotes.idleilao', '=', 'leiloes.codigo')
                ->select('lances.*', 'lotes.titulo', 'leiloes.titulo as leilao_titulo', 'lotes.codigo')
                ->orderBy('data_lance', 'desc');
    }

    public function arrematacao()
    {
        return $this->hasMany('App\Lances', 'idcadastro', 'codigo')
                ->join('lotes', 'lances.idlote', '=', 'lotes.codigo')
                ->join('leiloes', 'lotes.idleilao', '=', 'leiloes.codigo')
                ->select('lances.*', 'lotes.titulo', 'leiloes.titulo as leilao_titulo', 'lotes.codigo', 'lotes.encerrado')
                ->where('lotes.encerrado', '=', 8)
                ->orderBy('data_lance', 'desc');
    }

    public function ultimoLanceAutomatico() {
        return $this->hasMany('App\Lances', 'idcadastro', 'codigo')
                ->join('lotes', 'lances.idlote', '=', 'lotes.codigo')
                ->join('leiloes', 'lotes.idleilao', '=', 'leiloes.codigo')
                ->select('lances.*', 'lotes.titulo', 'leiloes.titulo as leilao_titulo', 'lotes.codigo', 'lotes.encerrado')
                ->where('lances.desativado', '!=', 1)
                ->where('lotes.encerrado', '=', 1)
                ->groupBy('idlote')
                ->orderBy('lances.codigo', 'desc');
                
    }
}