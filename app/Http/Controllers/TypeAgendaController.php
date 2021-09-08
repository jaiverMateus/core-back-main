<?php

namespace App\Http\Controllers;

use App\Models\TypeAgenda;
use App\Traits\ApiResponser;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;


class TypeAgendaController extends Controller
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
                TypeAgenda::orderBy('name', 'DESC')->get(['name As text', 'id As value'])
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
                TypeAgenda::orderBy('name')
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
            $dep = TypeAgenda::updateOrCreate(['id' => request()->get('id')], request()->all());
            return ($dep->wasRecentlyCreated) ? $this->success('creado con exito') : $this->success('actualizado con exito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\TypeAgenda  $typeAgenda
     * @return \Illuminate\Http\Response
     */
    public function show(TypeAgenda $typeAgenda)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\TypeAgenda  $typeAgenda
     * @return \Illuminate\Http\Response
     */
    public function edit(TypeAgenda $typeAgenda)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\TypeAgenda  $typeAgenda
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, TypeAgenda $typeAgenda)
    {
        /* try {
            $typeAgenda = TypeAgenda::find(request()->get('id'));
            $typeAgenda->update(request()->all());
            return $this->success('Tipo de agenda actualizado correctamente');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        } */
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\TypeAgenda  $typeAgenda
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        /* try {
            $typeAgenda = TypeAgenda::findOrFail($id);
            $typeAgenda->delete();
            return $this->success('Tipo de agenda eliminada correctamente', 204);
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        } */
    }
}
