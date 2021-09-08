<?php

namespace App\Traits;

use Carbon\Carbon;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

trait manipulateDataFromExternalService
{
    public function getDateOfBirth($porciones)
    {
        switch (count($porciones)) {
            case '6':
                return  Carbon::now()->subYears($porciones[0])->subMonths($porciones[2])->subDays($porciones[4])->format('Y-m-d');
                break;
            case '5':
            case '4':
                return  Carbon::now()->subYears($porciones[0])->subMonths($porciones[2])->format('Y-m-d');
                break;
            default:
                throw new Exception('No se puede conocer la edad');
                break;
        }
        Carbon::now()->subYears($porciones[0])->subMonths($porciones[2])->subDays($porciones[4])->format('Y-m-d');
    }

    public function appendMunicipaly($cityName)
    {
        return  DB::table('municipalities')->where('nombre', normalize($cityName))->first();
    }

    public function appendDeparment($cityName)
    {
        $cities = DB::select('select nombre, department_id from municipalities');
        foreach ($cities as  $city) {
            if (normalize($city->nombre) == normalize($cityName)) {
                return DB::table('departments')->find($city->department_id);
            }
        }
    }

    public function appendRegional($dptoId)
    {
        return DB::table('departamento_regionals')->where('departamento_id', $dptoId)
            ->join('regionals', 'regionals.id', '=', 'departamento_regionals.regional_id')->value('regionals.id');
    }
}
