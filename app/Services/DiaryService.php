<?php

namespace App\Services;

use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\DB;

class DiaryService
{
	/**Funciones de estadisticas */
	public static function getPeople($id, $dates, $company_id)
	{
		$compare = Request()->get('turn_type') == 'Rotativo' ? 'rotating_turn_diaries' : 'fixed_turn_diaries';
		return DB::table("people as p")
			->join("work_contracts as w", function ($join) {
				$join->on(
					"p.id",
					"=",
					"w.person_id"
				)->whereRaw('w.id IN (select MAX(a2.id) from work_contracts as a2
                        join people as u2 on u2.id = a2.person_id group by u2.id)');
			})
			->join("positions as ps", "ps.id", "=", "w.position_id")
			->where("ps.dependency_id", $id)
			->where("w.company_id", $company_id)
			->whereExists(function ($query) use ($dates, $compare) {
				$query
					->select(DB::raw(1))
					->from("$compare as la")
					->whereColumn("la.person_id", "p.id")
					->whereBetween(DB::raw("DATE(la.date)"), $dates);
			})
			->select("p.first_name", "p.first_surname", "p.id", "p.image")
			->get();
	}
	public static function getDiaries($personId, $dates)
	{
		return DB::table("fixed_turn_diaries as la")
			->select("*")
			->selectRaw('(IF(FORMAT((TIME_TO_SEC(leave_time_one) - TIME_TO_SEC(entry_time_one))/3600,2)>=0,FORMAT((TIME_TO_SEC(leave_time_one) - TIME_TO_SEC(entry_time_one))/3600,2),0) +(IF(FORMAT((TIME_TO_SEC(leave_time_two) - TIME_TO_SEC(entry_time_two))/3600,2)>=0,FORMAT((TIME_TO_SEC(leave_time_two) - TIME_TO_SEC(entry_time_two))/3600,2),0))) as working_hours')
			->where("la.person_id", $personId)
			->whereBetween(DB::raw("DATE(la.date)"), $dates)
			->get();
	}


	//Consulta Turnos rotativos

	public static function getDiariesRotative($personId, $dates)
	{
		return DB::table("rotating_turn_diaries as la")
			->select("*")
			->selectRaw('TIMESTAMPDIFF(SECOND,CONCAT(date," ",entry_time_one),CONCAT(leave_date," ",leave_time_one))/3600 as working_hours')
			->where("la.person_id", $personId)
			->whereBetween(DB::raw("DATE(la.date)"), $dates)
			->get();
	}
}
