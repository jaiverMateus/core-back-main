<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Marcation extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'img' ,
        'description' ,
        'date',
        'person_id'
    ];
}
