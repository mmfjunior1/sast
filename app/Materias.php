<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Materias extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'idcategoria',
        'titulo',
        'subtitulo',
        'texto',
        'data',
        'imagem',
        'visualizacoes',
    ];

    protected $table = 'materias';
    protected $primaryKey = 'codigo';
    
}