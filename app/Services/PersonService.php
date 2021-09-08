<?php

namespace App\Services;

use App\Models\Person;
use Illuminate\Support\Facades\DB;

class PersonService
{
    static public function getPeople($data = [])
    {

        return DB::table('people as p')
            ->select(
                'p.id',
                'p.identifier',
                'p.image',
                'p.status',
                'p.full_name',
                'p.first_surname',
                'p.first_name',
                'pos.name as position',
                'd.name as dependency',
                'p.id as value',
                DB::raw('CONCAT_WS(" ",first_name,first_surname) as text '),
                'c.name as company',
                DB::raw('w.id AS work_contract_id')
            )
            ->join('work_contracts as w', function ($join) {
                $join->on('p.id', '=', 'w.person_id')
                    ->whereRaw('w.id IN (select MAX(a2.id) from work_contracts as a2
                        join people as u2 on u2.id = a2.person_id group by u2.id)');
            })
            ->join('companies as c', 'c.id', '=', 'w.company_id')
            ->join('positions as pos', 'pos.id', '=', 'w.position_id')
            ->join('dependencies as d', 'd.id', '=', 'pos.dependency_id')
            ->join('groups as g', 'g.id', '=', 'd.group_id')
            ->where('p.status', 'Activo')
            ->when(key_exists('name', $data), function ($q) use ($data) {
                $q->where('p.identifier', 'like', '%' . $data['name'] . '%')
                    ->orWhere(DB::raw('concat(p.first_name," ",p.first_surname)'), 'LIKE', '%' . $data['name'] . '%');
            })

            ->when(key_exists('dependencies', $data), function ($q) use ($data) {
                $q->whereIn('d.id', $data['dependencies']);
            })
            ->when(key_exists('groups', $data), function ($q) use ($data) {
                $q->whereIn('g.id', $data['groups']);
            })

            ->when(key_exists('status', $data), function ($q) use ($data) {
                $q->whereIn('p.status', $data['status']);
            })
            ->get();
    }


    public static function funcionario_turno($personId, $dia, $hoy, $ayer)
    {
        $funcionario =  Person::where('personId', $personId)
            /* ->with('cargo') */
            ->with('contractultimate')
            ->with('contractultimate.fixedTurn')
            ->with('contractultimate.fixedTurn.horariosTurnoFijo')
            ->with(['diariosTurnoFijo' => function ($query) use ($hoy) {
                $query->where('fecha', '=', $hoy);
            }])->with(['turnoFijo.horariosTurnoFijo' => function ($query) use ($dia) {
                $query->where('dia', '=', $dia);
            }])->with(['diariosTurnoRotativoAyer' => function ($query) use ($ayer) {
                $query->with('turnoRotativo')->where('fecha', '=', $ayer)->whereNull('fecha_salida');
            }])->with(['diariosTurnoRotativoHoy' => function ($query) use ($hoy) {
                $query->with('turnoRotativo')->where('fecha', '=', $hoy);
            }])->with(
                ['horariosTurnoRotativo' => function ($query) use ($hoy) {
                    $query->with('turnoRotativo')->where('fecha', '=', $hoy);
                }]
            )->first();

        if (!$funcionario) {
            return false;
        }
        return $funcionario;
    }
}
