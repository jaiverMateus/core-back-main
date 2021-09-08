<?php

namespace App\Http\Controllers;

use App\Models\WorkContract;
use App\Traits\ApiResponser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkContractController extends Controller
{
    //
    use ApiResponser;

    public function show($id){
        return $this->success(
            DB::table('people as p')
            ->select(
                'p.id as person_id',
                'posi.name as position_name',
                'd.name as dependency_name',
                'gr.name as group_name',
                'd.group_id',
                'w.rotating_turn_id',
                'posi.dependency_id',
                'f.name as fixed_turn_name',
                'c.name as company_name',
                'w.turn_type',
                'w.position_id',
                'w.company_id',
                'w.fixed_turn_id',
                'w.id'
            )
            ->join('work_contracts as w', function ($join) {
                $join->on('w.person_id', '=', 'p.id')
                    ->whereRaw('w.id IN (select MAX(a2.id) from work_contracts as a2 
                            join people as u2 on u2.id = a2.person_id group by u2.id)');
            })
            ->join('positions as posi', function ($join) {
                $join->on('posi.id', '=', 'w.position_id');
            })
            ->join('dependencies as d', function ($join) {
                $join->on('d.id', '=', 'posi.dependency_id');
            })
            ->join('groups as gr', function ($join) {
                $join->on('gr.id', '=', 'd.group_id');
            })
            ->leftJoin('fixed_turns as f', function ($join) {
                $join->on('f.id', '=', 'w.fixed_turn_id');
            })
            ->join('companies as c', function ($join) {
                $join->on('c.id', '=', 'w.company_id');
            })
            ->where('p.id', '=', $id)
            ->first()
            );
    }

    public function updateEnterpriseData(Request $request)
    {
        try {
            $work_contract = WorkContract::find($request->get('id'));
                $work_contract->update($request->all());
                return response()->json(['message' => 'Se ha actualizado con éxito']);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 500);
        }
    }
}
