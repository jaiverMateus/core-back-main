<?php

namespace App\Http\Controllers;

use App\Models\SeveranceFund;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;

class SeveranceFundController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return  $this->success( SeveranceFund::all(['id as value','name as text']) );
    }
}
