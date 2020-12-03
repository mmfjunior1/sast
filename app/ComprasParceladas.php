<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class ComprasParceladas extends Model
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
        'idlance',
        'oferta',
        'entrada',
        'parcelas',
        'observacoes',
        'data',
    ];

    protected $table = 'compras_parceladas';
    protected $primaryKey = 'codigo';
    public $timestamps = false;

}