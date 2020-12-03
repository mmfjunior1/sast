<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class PoliticaPrivacidade extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'texto'
    ];

    protected $table = 'politica_privacidade';
    protected $primaryKey = 'codigo';
    public $timestamps = false; 

}