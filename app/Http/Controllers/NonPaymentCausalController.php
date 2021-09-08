<?php

namespace App\Http\Controllers;

use App\Models\NonPaymentCausal;
use App\Traits\ApiResponser;
use Illuminate\Database\Query\Builder;

use Illuminate\Http\Request;

class NonPaymentCausalController extends Controller
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
                NonPaymentCausal::orderBy('name', 'DESC')->get(['name As text', 'id As value'])
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    public function paginate()
    {
        try {
            return $this->success(
                NonPaymentCausal::orderBy('name')
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
            $dep = NonPaymentCausal::updateOrCreate(['id' => request()->get('id')], request()->all());
            return ($dep->wasRecentlyCreated) ? $this->success('creado con exito') : $this->success('actualizado con exito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\NonPaymentCausal  $nonPaymentCausal
     * @return \Illuminate\Http\Response
     */
    public function show(NonPaymentCausal $nonPaymentCausal)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\NonPaymentCausal  $nonPaymentCausal
     * @return \Illuminate\Http\Response
     */
    public function edit(NonPaymentCausal $nonPaymentCausal)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\NonPaymentCausal  $nonPaymentCausal
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, NonPaymentCausal $nonPaymentCausal)
    {
        try {
            $nonPaymentCausal = NonPaymentCausal::find(request()->get('id'));
            $nonPaymentCausal->update(request()->all());
            return $this->success('Causal de no pago actualizado correctamente');
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\NonPaymentCausal  $nonPaymentCausal
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $nonPaymentCausal = NonPaymentCausal::findOrFail($id);
            $nonPaymentCausal->delete();
            return $this->success(' Causal de no pago eliminado correctamente', 204);
        } catch (\Throwable $th) {
            return response()->json([$th->getMessage(), $th->getLine()]);
        }
    }
}
