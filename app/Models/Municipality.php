<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Municipality extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'code', 'codigo_dane', 'department_id'];
  
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
}
