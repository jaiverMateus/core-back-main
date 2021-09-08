<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bonifications extends Model
{
    use HasFactory;
    protected $table = 'bonifications';
    protected $fillable = [
        'countable_income_id',
        'value',
        'work_contract_id',
        'status'
    ];
}
