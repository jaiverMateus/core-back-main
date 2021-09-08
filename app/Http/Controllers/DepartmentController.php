<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Traits\ApiResponser;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    use ApiResponser;

    public function index()
    {
        try {
            return $this->success(
                Department::orderBy('name', 'DESC')->get(['name As text', 'id As value'])
            );
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }

    public function paginate()
    {
        try {
            return $this->success(
                Department::orderBy('name')
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
            $dep = Department::updateOrCreate(['id' => request()->get('id')], request()->all());
            return ($dep->wasRecentlyCreated) ? $this->success('creado con exito') : $this->success('actualizado con exito');
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 400);
        }
    }
}
