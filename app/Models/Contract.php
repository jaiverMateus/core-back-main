<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'number',
        'code',
        'administrator_id',
        'contract_type',
        'payment_method_id',
        'benefit_plan_id',
        'start_date',
        'end_date',
        'policy',
        'price',
        'price_list_id',
        'variation',
        'status'
    ];

    
     public function administrator(){
        return $this->belongsTo(Administrator::class);
    }

    

    public function payment_method(){
        return $this->belongsTo(PaymentMethod::class);
        
    }

    public function benefitsPlan(){
        return $this->belongsTo(BenefitsPlan::class);
        
    }

    public function priceList(){
        return $this->belongsTo(PriceList::class);
        
    }
}
