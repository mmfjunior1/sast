<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Leiloes extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'tipo',
        'modalidade',
        'modelo',
        'idleiloeiro',
        'habilitacao',
        'titulo',
        'subtitulo',
        'numero',
        'publicacao',
        'jornal',
        'edital',
        'leilao_data_tipo',
        'leilao_data_inicial',
        'leilao_hora_inicial',
        'leilao_data_final',
        'leilao_hora_final',
        'leilao2_data_tipo',
        'leilao2_data_inicial',
        'leilao2_hora_inicial',
        'leilao2_data_final',
        'leilao2_hora_final',
        'idcomitente',
        'forum',
        'juiz',
        'responsavel',
        'endereco',
        'cidade',
        'visitacao',
        'encerrado',
        'logo',
        'destaque',
        'condicoes',
        'regras',
        'restrito',
        'suspender',
        'data_cadastro',
        'ind_status',
        'imagem_360',
        'youtube',
        'usar_cronometro',
        'status_leilao',
        'desconto',
        'condicao',
        'desconto_p'
    ];

    protected $table = 'leiloes';
    protected $primaryKey = 'codigo';
    public $timestamps = false; 

    public function lots()
    {
        return $this->hasMany('App\Lotes', 'idleilao', 'codigo')->orderBy('categoria', 'asc');
    }
}