<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Lances extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'idcadastro',
        'idlote',
        'valor',
        'data_lance',
        'data_cronometro',
        'ind_cronometro',
        'ind_envio',
        'obs',
        'desativado',
        'data_desativa',
        'usuario_desativa',
        'automatico',
    ];

    protected $table = 'lances';
    protected $primaryKey = 'codigo';
    public $timestamps = false;

    public function usuarios() {
        return $this->hasMany('App\Cadastros', 'codigo', 'idcadastro');
    }

    public function lote() {
        return $this->hasOne('App\Lotes', 'codigo', 'idlote');
    }
}