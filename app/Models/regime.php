<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class regime extends Model
{
    use HasFactory;

    protected $fillable=[
        'name',
        'description',
        'status'
    
    ];

    public function regimeTecnicNote(){
        return $this->hasMany(RegimeTecnicNote::class);
    }

    protected $table = 'regimen_types'; 
}
