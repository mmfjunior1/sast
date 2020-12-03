<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Habilitados extends Model
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
        'data_habilitacao'
    ];

    protected $table = 'cadastros_habilitados';
    protected $primaryKey = 'codigo';
    public $timestamps = false;

    public function cadastro() {
        return $this->belongsTo('App\Cadastros', 'codigo', 'idcadastro')->withPivot('nome');
    }
}