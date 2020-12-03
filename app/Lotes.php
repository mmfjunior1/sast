<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Lotes extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'idleilao',
        'categoria',
        'subcategoria',
        'idestado',
        'idcidade',
        'bairro',
        'endereco',
        'numero',
        'titulo',
        'subtitulo',
        'avaliacao',
        'min_venda',
        'debitos',
        'lance_data_1',
        'lance_data_2',
        'incremento',
        'abertura',
        'fechamento',
        'num_processo',
        'url_consulta',
        'vara',
        'juiz',
        'comissao_leiloeiro',
        'nome_exequente',
        'doc_exequente',
        'nome_executado',
        'doc_executado',
        'nome_depositario',
        'cpf_depositario',
        'rg_depositario',
        'edital',
        'publicacao',
        'doe',
        'cda',
        'chb',
        'descricao',
        'visitacao',
        'compra_parcelada',
        'mapa',
        'lote_destaque',
        'requer_habilitacao',
        'exibir_valor',
        'encerrado',
        'restrito',
        'suspender',
        'ind_parcelada',
        'ind_envio',
        'ind_status',
    ];

    protected $table = 'lotes';
    protected $primaryKey = 'codigo';
    public $timestamps = false;

    public function habilitados() 
    {
        return $this->hasManyThrough('App\Cadastros', 'App\Habilitados', 'idlote', 'codigo', 'codigo', 'idcadastro');
    }

    public function leilao() 
    {
        return $this->hasOne('App\Leiloes', 'codigo', 'idleilao');
    }

    public function estado() 
    {
        return $this->hasOne('App\Estados', 'codigo', 'idestado');
    }

    public function cidade() 
    {
        return $this->hasOne('App\Cidades', 'codigo', 'idcidade');
    }

    public function imagensLote()
    {
        return $this->hasMany('App\LotesImagens', 'idlote', 'codigo')->orderBy('ordem');
    }

    public function maiorLance()
    {
        return $this->hasMany('App\Lances', 'idlote', 'codigo')
                ->join('cadastros', 'cadastros.codigo', '=', 'lances.idcadastro')
                ->where('lances.desativado', '!=', 1)
                ->select('lances.*', 'cadastros.email', 'cadastros.apelido')
                ->orderBy('valor', 'desc');
    }

    public function maiorLanceParcelado()
    {
        return $this->hasMany('App\LancesParcelados', 'idlote', 'codigo')
                ->join('cadastros', 'cadastros.codigo', '=', 'lances_parcelados.idcadastro')
                ->join('compras_parceladas', 'compras_parceladas.idlance', '=', 'lances_parcelados.codigo')
                ->where('lances_parcelados.desativado', '!=', 1)
                ->select('lances_parcelados.*', 'cadastros.email', 'parcelas', 'cadastros.apelido')
                ->orderBy('valor', 'desc')
                ->orderBy('parcelas', 'asc');
    }

    public function lanceParceladoEscolhido()
    {
        return $this->hasMany('App\LancesParcelados', 'idlote', 'codigo')
                ->join('cadastros', 'cadastros.codigo', '=', 'lances_parcelados.idcadastro')
                ->join('compras_parceladas', 'compras_parceladas.idlance', '=', 'lances_parcelados.codigo')
                ->where('lances_parcelados.desativado', '!=', 1)
                ->where('lances_parcelados.status', '=', 1)
                ->select('lances_parcelados.*', 'cadastros.email', 'parcelas')
                ->orderBy('valor', 'desc')
                ->orderBy('parcelas', 'asc');
    }

    public function anexosLote()
    {
        return $this->hasMany('App\LotesAnexos', 'idlote', 'codigo')->orderBy('codigo');
    }
}