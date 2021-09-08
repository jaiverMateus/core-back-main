<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{

    public function scopeFindCustom($query, $id)
    {
        return $query->select(['name', 'id'])->firstWhere('id', $id);
    }

    public function user()
    {
        return $this->BelongsToMany(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class);
    }
}
