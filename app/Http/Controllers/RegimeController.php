<?php

namespace App\Http\Controllers;

use App\Models\Regime;
use App\Models\TecnicNote;
use App\Traits\ApiResponser;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class RegimeController extends Controller
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
                Regime::orderBy('name', 'DESC')->get(['name As text', 'id As value'])
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

    public function paginate()
    {
        try {
            return $this->success(
                Regime::orderBy('name')
                    ->when(request()->get('name'), function (Builder $q) {
                        $q->where('name', 'like', '%' . request()->get('name') . '%');
                    })
                    ->paginate(request()->get('pageSize', 10), ['*'], 'page', request()->get('page', 1))
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
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
            $dep = Regime::updateOrCreate(['id' => request()->get('id')], request()->all());
            return ($dep->wasRecentlyCreated) ? $this->success('creado con exito') : $this->success('actualizado con exito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Regime  $Regime
     * @return \Illuminate\Http\Response
     */

     public function getRegimeTecnicNoteId($id){
        try{
            
            return Regime::with('regimeTecnicNote')->Where('id',$id)->get();         
        }catch(\Throwable $th){
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
     }
    public function show($id)
    {
        try{
            $Regime = Regime::findOrFail($id);
            // return response()->json( $administrator);
            return Regime::with('regimeTecnicNote')->find(1);
        }catch(\Throwable $th){
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Regime  $Regime
     * @return \Illuminate\Http\Response
     */
    public function edit(Regime $Regime)
    {
       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Regime  $Regime
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{

       
            $Regime = Regime::findOrFail($id);
            $Regime->name = $request->name;
            $Regime->description = $request->description;
            
            $Regime->save();
            return response()->json('Regimen actualizado correctamente');
            
         }catch(\Throwable $th){
             return response()->json([$th->getMessage(), $th->getLine()]);
         }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Regime  $Regime
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $Regime = Regime::findOrFail($id);
            $Regime->delete();
            return response()->json('Regimen eliminado correctamente');
        }catch(\Throwable $th){
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }
}
