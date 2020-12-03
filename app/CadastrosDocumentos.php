<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class CadastrosDocumentos extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'idcadastro',
        'arq_pessoal',
        'status_pessoal',
        'arq_residencia',
        'status_residencia',
    ];

    protected $table = 'cadastros_documentos';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
    
}