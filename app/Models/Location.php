<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model
{
    protected $fillable = [

        'company_id',
        'name',
        'code',
        'address',
        'agreements',
        'category',
        'city',
        'globo_id',
        'country_code',
        'creation_date',
        'disabled',
        'email',
        'encoding_characters',
        'interface_id',
        'logo',
        'pbx',
        'regional_id',
        'send_email',
        'settings',
        'slogan',
        'state',
        'telephone',
        'tin',
        'allow_procedure',
        'type',
        'status'


    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
