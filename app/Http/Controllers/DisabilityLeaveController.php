<?php

namespace App\Http\Controllers;

use App\Models\DisabilityLeave;
use App\Models\PayrollFactor;
use App\Traits\ApiResponser;
use App\Traits\DisabilityLeaveDates;
use Illuminate\Http\Request;

class DisabilityLeaveController extends Controller
{
    use ApiResponser;
    //
    public function index(){
        return $this->success(DisabilityLeave::all(['id as value','concept as text']));
    }

    
  
}
