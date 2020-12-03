<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;

class Cidades extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'idestado',
    ];

    protected $table = 'cidades';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
}