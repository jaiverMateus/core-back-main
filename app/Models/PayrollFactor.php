<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PayrollFactor extends Model 
{
    use HasFactory;

    protected $fillable = [
        'person_id',
        'disability_leave_id',
        'disability_type',
        'date_start',
        'date_end',
        'modality',
        'observation',
        'sum'
    ];


    public function disability_leave()
    {
        return $this->belongsTo(DisabilityLeave::class);
    }

}
