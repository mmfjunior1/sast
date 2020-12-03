<?php

namespace App;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Model;

class Estados extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'uf',
    ];

    protected $table = 'estados';
    protected $primaryKey = 'codigo';
    public $timestamps = false;

    public function cidades() {
        return $this->hasMany('App\Cidades', 'idestado', 'codigo');
    }
}