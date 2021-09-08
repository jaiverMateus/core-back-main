<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TecnicNote extends Model
{
    use HasFactory;
    protected $fillable=[
           'frequency',
           'alert_percentage',
           'unit_value',
           'date',
           'chance',
           'status'
    ];

    public function tecnicNoteCup(){

        return $this->hasMany(TecnicNoteCup::class);
    }

    public function regimeTecnicNote(){
        return $this->hasMany(RegimeTecnicNote::class);
    }
}
