<?php

namespace App\Traits;

use Illuminate\Http\Response;

trait filterDocumentType
{
    /**
     * Build a success response
     * @param  string|array $data
     * @param  int $code
     * @return Illuminate\Http\Response
     */
    public function filterDocumentType($data)
    {
        $filtered =  $this->documentypes->first(function ($value, $key) use ($data) {
            return  $value->name == $data;
        });

        if ($filtered) {
            return $filtered->id;
        }
        dd('Tipo de documento no existe');
    }
}
