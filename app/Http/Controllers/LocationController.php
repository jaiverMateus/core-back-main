<?php

namespace App\Http\Controllers;

use App\Http\Resources\SedeResource;
use App\Models\Location;
use App\Traits\ApiResponser;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class LocationController extends Controller
{

    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    // ${this.company}/${this.subappointment.procedure

    public function index()
    {
        // return response()->json('jghkghj');
        try {
            return $this->success(
                Location::orderBy('name', 'DESC')->get(['name As text', 'id As value'])
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
                Location::orderBy('name')
                    ->when(request()->get('name'), function (Builder $q) {
                        $q->where('name', 'like', '%' . request()->get('name') . '%');
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
            $dep = Location::updateOrCreate(['id' => request()->get('id')], request()->all());
            return ($dep->wasRecentlyCreated) ? $this->success('creado con exito') : $this->success('actualizado con exito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  App\Models\Location  $sede
     * @return \Illuminate\Http\Response
     */
    public function show(Location $sede)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  App\Models\Location  $sede
     * @return \Illuminate\Http\Response
     */
    public function edit(Location $sede)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  App\Models\Location  $sede
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Location $sede)
    {
        try {
            $sede = Location::find(request()->get('id'));
            $sede->update(request()->all());
            return $this->success('Sede actualizado correctamente');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  App\Models\Location  $sede
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $sede = Location::findOrFail($id);
            $sede->delete();
            return $this->success('Sede eliminada correctamente', 204);
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }
}
