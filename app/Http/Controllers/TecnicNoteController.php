<?php

namespace App\Http\Controllers;

use App\Models\TecnicNote;
use App\Models\TecnicNoteCup;
use App\Traits\ApiResponser;
use Illuminate\Database\Query\Builder;

use Illuminate\Http\Request;

class TecnicNoteController extends Controller
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
                TecnicNote::orderBy('frequency', 'DESC')->get(['frequency As text', 'id As value']));
            }catch (\Throwable $th) {
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
                TecnicNote::orderBy('frequency')
                    ->when(request()->get('frequency'), function (Builder $q) {
                        $q->where('frequency', 'like', '%' . request()->get('frequency') . '%');
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
            $dep = TecnicNote::updateOrCreate(['id' => request()->get('id')], request()->all());
            return ($dep->wasRecentlyCreated) ? $this->success('creado con exito') : $this->success('actualizado con exito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TecnicNote  $TecnicNote
     * @return \Illuminate\Http\Response
     */

     /* public function getTecnicNoteCupId($id){
        try{
            
            return TecnicNoteCup::with('tecnic_note')->Where('id',$id)->get();         
        }catch(\Throwable $th){
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
     } */
    public function show($id)
    {
        try{
            $TecnicNote = TecnicNote::findOrFail($id);
            // return response()->json( $administrator);
            return TecnicNote::with('tecnicNoteCup','regimeTecnicNote')->find(2);
        }catch(\Throwable $th){
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TecnicNote  $TecnicNote
     * @return \Illuminate\Http\Response
     */
    public function edit(TecnicNote $TecnicNote)
    {
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TecnicNote  $TecnicNote
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        try{

       
            $TecnicNote = TecnicNote::findOrFail($id);
            $TecnicNote->frequency = $request->frequency;
            $TecnicNote->alert_percentage = $request->alert_percentage;
            $TecnicNote->unit_value = $request->unit_value;
            $TecnicNote->date=$request->date;
            $TecnicNote->chance=$request->chance;
            $TecnicNote->save();
            return response()->json('Notas tecnicas actualizado correctamente');
            
         }catch(\Throwable $th){
             return response()->json([$th->getMessage(), $th->getLine()]);
         }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TecnicNote  $TecnicNote
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $TecnicNote = TecnicNote::findOrFail($id);
            $TecnicNote->delete();
            return response()->json('administrador eliminado correctamente');
        }catch(\Throwable $th){
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }
}
