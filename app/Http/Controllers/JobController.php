<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Http\ResponseTrait;

class JobController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $page = Request()->get('page');
        $page = $page ? $page : 1;

        $pageSize = Request()->get('pageSize');
        $pageSize = $pageSize ? $pageSize : 10;

        return $this->success( 
            Job::with([
            'position'=>function($q){
                $q->select('name','id','dependency_id');
            },
            'position.dependency'=>function($q){
                $q->select('name','id');
            },
            'municipality'=>function($q){
                $q->select('name','id','department_id');
            },
            'municipality.department'=>function($q){
                $q->select('name','id');
            }
            ])
            ->orderBy('id','DESC')
            ->paginate($pageSize, '*', 'page', $page)
        );
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            Job::create($request->all());
            return $this->success('creacion exitosa');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(),500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
        return $this->success( 
            Job::with([
            'position'=>function($q){
                $q->select('name','id','dependency_id');
            },
            'position.dependency'=>function($q){
                $q->select('name','id');
            },
            'municipality'=>function($q){
                $q->select('name','id','department_id');
            },
            'municipality.department'=>function($q){
                $q->select('name','id');
            }
            ])
            ->where('id',$id)
            ->first()
        );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    public function setState( $id, Request $request )
    {
        try {
            $job = Job::find($id);
            $job->state = $request->get('state');
            $job->save();
            return $this->success('actualizado exitosa');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(),500);
        }
        
    }

}
