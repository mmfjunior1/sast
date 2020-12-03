<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NewsLetter extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nome',
        'email',
    ];

    protected $table = 'newsletter';
    protected $primaryKey = 'codigo';
    public $timestamps = false;
}
