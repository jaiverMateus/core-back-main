<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alert extends Model
{
    use HasFactory;
    protected $fillable = [
        'person_id',
        'user_id',
        'user_id',
        'icon',
        'description',
        'type',
        'url',
        'destination_id'
    ];
}
