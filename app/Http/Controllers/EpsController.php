<?php

namespace App\Http\Controllers;

use App\Models\Eps;
use App\Traits\ApiResponser;
use Illuminate\Database\Query\Builder;

use Illuminate\Http\Request;

class EpsController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = Request()->all();
        $page = key_exists('page', $data) ? $data['page'] : 1;
        $pageSize = key_exists('pageSize', $data) ? $data['pageSize'] : 5;
        return $this->success(
            Eps::when( Request()->get('name') , function($q, $fill)
            {
                $q->where('name','like','%'.$fill.'%');
            })
            ->when( Request()->get('code'), function($q, $fill){
                $q->where('code', 'like','%'.$fill.'%');
            })
            ->paginate($pageSize, ['*'],'page', $page)

        );
    }
    public function paginate()
    {
        try {
            return $this->success(
                Eps::orderBy('name')
                    ->when(request()->get('name'), function (Builder $q) {
                        $q->where('name', 'like', '%' . request()->get('name') . '%');
                    })
                    ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }
    public function store( Request $request )
    {
        try {
            $validate =  Eps::where('nit', $request->get('nit'))->first();
            if ($validate) {
                return $this->error('El nit ya está registrado', 423); 
            }
            $v = Eps::where('code', $request->get('code'))->first();
            if ($v) {
                return $this->error('El código de la EPS ya existe', 423);
            }
            Eps::updateOrCreate( [ 'id'=> $request->get('id')], $request->all());
            return $this->success('creacion exitosa');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 200);
        }
    }
}
