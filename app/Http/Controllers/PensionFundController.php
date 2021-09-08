<?php

namespace App\Http\Controllers;

use App\Models\PensionFund;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class PensionFundController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return  $this->success( PensionFund::all(['id as value','name as text']) );
    }
}
