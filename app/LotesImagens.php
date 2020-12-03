<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class LotesImagens extends Model
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
        'ordem',
    ];

    protected $table = 'lotes_imagens';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
    
}