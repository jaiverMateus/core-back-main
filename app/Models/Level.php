<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Level extends Model
{

    protected $fillable = [
        'number',
        'code',
        'name',
        'cuote',
        'regimen_id',
        'type',
        'cuote_max',
        'status'


    ];
}
