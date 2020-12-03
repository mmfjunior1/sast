<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class LancesParcelados extends Model
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
        'tipo',
        'valor',
        'data_lance',
        'data_cronometro',
        'ind_cronometro',
        'ind_envio',
        'status',
        'obs',
        'desativado',
        'data_desativa',
        'usuario_desativa',
    ];

    protected $table = 'lances_parcelados';
    protected $primaryKey = 'codigo';
    public $timestamps = false;

}