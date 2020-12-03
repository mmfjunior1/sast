<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class LanceAutomatico extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'idcadastro', 'idlote', 'valor',
    ];

    protected $table = 'lance_automatico';
    protected $primaryKey = 'codigo';
}