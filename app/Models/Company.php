<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    protected  $fillable =
    [
        'name',
        'tin',
        'dv',
        'address',
        'code',
        'agreements',
        'category',
        'city',
        'country_code',
        'creation_date',
        'disabled',
        'email',
        'encoding_characters',
        'interface_id',
        'logo',
        'parent_id',
        'pbx',
        'regional_id',
        'send_email',
        'settings',
        'slogan',
        'state',
        'telephone',
        'type',
        'api_key',
        'simbol',
        'globo_id',
        'status'



    ];
}
