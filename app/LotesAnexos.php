<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class LotesAnexos extends Model
{
    use Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'idlote',
        'arquivo',
        'nome',
    ];

    protected $table = 'lotes_anexos';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
    
}