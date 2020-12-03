<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Localizacao extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'endereco',
        'telefone',
        'whatsapp',
        'email'
        
    ];

    protected $table = 'localizacao';
    protected $primaryKey = 'codigo';
    public $timestamps = false; 

}