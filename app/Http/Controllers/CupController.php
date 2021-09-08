<?php

namespace App\Http\Controllers;

use App\Models\Cup;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;

class CupController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            return $this->success(
                Cup::orderBy('description', 'DESC')->get(['description As text', 'id As value'])
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
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

    public function paginate()
    {
        try {
            return $this->success(
                Cup::orderBy('description')
                    ->when(request()->get('desciption'), function (Builder $q) {
                        $q->where('description', 'like', '%' . request()->get('description') . '%');
                    })
                    ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }
    public function store(Request $request)
    {
        try {
            $dep = Cup::updateOrCreate(['id' => request()->get('id')], request()->all());
            return ($dep->wasRecentlyCreated) ? $this->success('creado con exito') : $this->success('actualizado con exito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Cup  $Cup
     * @return \Illuminate\Http\Response
     */
    
    public function show($id)
    {
        try{
            $Cup = Cup::findOrFail($id);
            // return response()->json( $administrator);
            return Cup::with('tecnicNoteCup','priceList')->find(1);
        }catch(\Throwable $th){
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Cup  $Cup
     * @return \Illuminate\Http\Response
     */
    public function edit(Cup $Cup)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Cup  $Cup
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
       
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Cup  $Cup
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $Cup = Cup::findOrFail($id);
            $Cup->delete();
            return response()->json('Cup eliminado correctamente');
        }catch(\Throwable $th){
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }
}
