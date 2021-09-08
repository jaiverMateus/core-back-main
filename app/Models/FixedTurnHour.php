<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FixedTurnHour extends Model
{
	#protected $guarded = ["id"];
	/*    protected $table = 'horario_turno_fijo'; */
	protected $fillable = [
		"day",
		"fixed_turn_id",
		"entry_time_one",
		"leave_time_one",
		"entry_time_two",
		"leave_time_two",
	];
	protected $hidden = ["created_at", "updated_at"];

	public function turnoFijo()
	{
		return $this->belongsTo(FixedTurn::class);
	}
}
