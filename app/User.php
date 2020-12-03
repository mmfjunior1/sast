<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'telefone',
        'email',
        'tipo',
        'senha',
        'status',
        'last_login'
    ];

    protected $table = 'usuarios';
    protected $primaryKey = 'codigo';
    public $timestamps = false; 
    

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
      //  'nome', 'telefone', 'email', 'tipo', 'senha',
      'senha'
    ];

}