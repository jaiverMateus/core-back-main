<?php

namespace App\Http\Controllers;

use App\Http\Resources\SpecialityResource;
use App\Models\Speciality;
use App\Traits\ApiResponser;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class SpecialityController extends Controller
{
    use ApiResponser;
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($sede = 0)
    {
        try {
            return $this->success(
                Speciality::orderBy('name', 'DESC')->get(['name As text', 'id As value'])
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
                Speciality::orderBy('name')
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
            $dep = Speciality::updateOrCreate(['id' => request()->get('id')], request()->all());
            return ($dep->wasRecentlyCreated) ? $this->success('creado con exito') : $this->success('actualizado con exito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Speciality  $speciality
     * @return \Illuminate\Http\Response
     */
    public function show(Speciality $speciality)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Speciality  $speciality
     * @return \Illuminate\Http\Response
     */
    public function edit(Speciality $speciality)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Speciality  $speciality
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Speciality $speciality)
    {
        try{
            $speciality = Speciality::find(request()->get('id'));
            $speciality->update(request()->all());
        return response()->json('Especialidad actualizada correctamente');}
        catch(\Throwable $th){
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Speciality  $speciality
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try{
            $speciality = Speciality::findOrFail($id);
            $speciality->delete();
            return response()->json('Especialidad eliminada correctamente');
        }catch(\Throwable $th){
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }
}
