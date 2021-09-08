<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Mail;

use Illuminate\Http\Request;
use Intervention\Image\Facades\Image;
use App\Http\Controllers\FuncionariosController as Funcionario;
use App\Http\Controllers\DiariosController as Diarios;
use App\Http\Controllers\LlegadasTardeController as Llegadas;
use App\Models\Cliente;
use App\Models\Company;
use App\Models\Correo;
use App\Models\Empresa;
use App\Models\Marcation;
use App\Models\Person;
use App\Services\PersonService;
use Carbon\Carbon;
use Facade\FlareClient\Http\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Support\Facades\Log;

/* require_once $path = base_path('vendor/pear/http_request2/HTTP/Request2.php'); */

date_default_timezone_set('America/Bogota');


class AsistenciaController extends Controller
{
    public function validar()
    {
        try {
            $dias = array(
                0 => "Domingo",
                1 => "Lunes",
                2 => "Martes",
                3 => "Miercoles",
                4 => "Jueves",
                5 => "Viernes",
                6 => "Sabado"
            );

            $imgBase64 = request()->imagen;
            $temperatura = request()->temperatura;
            $image = base64_decode(preg_replace('#^data:image/\w+;base64,#i', '', $imgBase64));

            $file_path = 'temporales/' . Str::random(30) . time() . '.png';
            Storage::disk('public')->put($file_path, $image, 'public');

            $fully = Storage::disk('public')->url($file_path);
            //return $fully;
            //$fully = 'https://cms.modumb.com/storage/magazine/_800x422/guia-practica-para-identificar-el-rostro-de-un-cliente-8282.jpg';

            $empresa = Company::where('id', 1)->get();
            /*$ cliente = Cliente::with('face')->where('documento', $empresa[0]["numero_documento"])->get(); */

            $params = [
                'returnFaceId' => 'true',
                'returnFaceLandmarks' => 'false',
                'returnFaceAttributes' => 'mask',
                'recognitionModel' => 'recognition_04',
                'returnRecognitionModel' => 'true',
                'detectionModel' => 'detection_03'
            ];
            $ocpApimSubscriptionKey = 'df2f7a1cb9a14c66b11a7a2253999da5';
            $azure_grupo = 'personalnuevo';
            $uriBase = 'https://facemaqymon2021.cognitiveservices.azure.com/face/v1.0';
            $response = Http::accept('application/json')->withHeaders([
                'Content-Type' => 'application/json',
                'Ocp-Apim-Subscription-Key' => $ocpApimSubscriptionKey
            ])->post($uriBase  . '/detect?' . http_build_query($params), [

                'url' => $fully
            ]);
            $res = $response->json();
            $face_id = '';
            try {
                if ( !key_exists('error',$res) && key_exists('faceId', $res[0]) && count($res) > 0) {
                    $face_id = $res[0]['faceId'];
                    /* dd($face_id); */
                } else {
                    Marcation::create([
                        'type' => 'error',
                        'img' => $fully,
                        'description' => 'Error conectando al Servidor de rostros, es posible que no se vea un rostro con claridad',
                        'date' => date("Y-m-d H:i:s")
                    ]);

                    $error = array(
                        'title' => 'Opps!',
                        'text' => 'Error conectando al Servidor de rostros, es posible que no se vea un rostro con claridad',
                        'type' => 'error'
                    );
                    return $error;
                }
            } catch (HttpException $ex) {
                Marcation::create([
                    'type' => 'error',
                    'img' => $fully,
                    'description' => 'Error de Servidor: ' . $ex,
                    'date' => date("Y-m-d H:i:s")
                ]);
                $error = array(
                    'title' => 'Opps!',
                    'text' => 'Error de Servidor: ' . $ex,
                    'type' => 'error'
                );
                return $error;
            }

            if ($face_id != "") {
                /* INICIO DE IDENTIFICACIÓN DE ROSTRO */

                $response = Http::accept('application/json')->withHeaders([
                    'Content-Type' => 'application/json',
                    'Ocp-Apim-Subscription-Key' => $ocpApimSubscriptionKey
                ])

                    ->post($uriBase . '/identify', [
                        'personGroupId' => $azure_grupo,
                        'faceIds' => [
                            $face_id
                        ],
                        "confidenceThreshold" => 0.6,
                        "maxNumOfCandidatesReturned" => 1
                    ]);

                //return Response($response->json());
                try {
                    //$response = $request2->send();
                    //$resp = $response->getBody();
                    //Log::info($response->json());
                    $resp = $response->json();


                    if (key_exists('candidates', $resp[0]) && count($res) > 0) {
                        $candidatos = $resp[0]['candidates'];

                        if (count($candidatos) > 0) {
                            $candidato = $candidatos[0]['personId'];

                            if ($candidato != '') {
                                $hactual = date("H:i:s");
                                $hoy = date('Y-m-d');
                                $ayer = date("Y-m-d", strtotime(date("Y-m-d") . ' - 1 day'));
                                $funcionario = PersonService::funcionario_turno($candidato, $dias[date("w", strtotime($hoy))], $hoy, $ayer);

                             

                                if ($funcionario) {
                                    $tipo_turno = $funcionario->contractultimate->turn_type;
                                    switch ($tipo_turno) {
                                        case 'Fijo':
                                            return $this->ValidaTurnoFijo($funcionario, $hoy, $hactual, $fully, $temperatura, $empresa[0]);
                                            break;
                                        case 'Rotativo':
                                            return $this->ValidaTurnoRotativo($funcionario, $hoy, $ayer, $hactual, $fully, $temperatura, $empresa[0]);
                                            break;
                                        case 'Libre':
                                            return $this->ValidaTurnoLibre($funcionario, $hoy, $hactual, $fully, $temperatura, $empresa[0]);
                                            break;
                                    }
                                } else {
                                    Marcation::create([
                                        'type' => 'error',
                                        'img' => $fully,
                                        'description' => 'Se identifica un rostro pero al parecer no esta activo en el Sistema',
                                        'date' => Carbon::now(),
                                        // 'fecha'=>date("Y-m-d H:i:s")
                                    ]);
                                    $error = array(
                                        'title' => 'Error!',
                                        'img' => $fully,
                                        'html' => 'Identificamos un rostro pero al parecer no esta activo en el Sistema',
                                        'icon' => 'error'
                                    );
                                    return $error;
                                }
                            }
                        } else {
                            Marcation::create([
                                'type' => 'error',
                                'img' => $fully,
                                'description' => 'No se logra identificar el rosto',
                                'date' => date("Y-m-d H:i:s")
                            ]);
                            $error = array(
                                'title' => 'Acceso Denegado!',
                                'html' => 'Su rostro no se encuentra en nuestros registros',
                                'icon' => 'error'
                            );
                            return $error;
                        }
                    } else {
                        Marcation::create([
                            'type' => 'error',
                            'img' => $fully,
                            'description' => 'No se logra identificar el rosto',
                            'date' => date("Y-m-d H:i:s")
                        ]);
                        $error = array(
                            'title' => 'Acceso Denegado!',
                            'html' => 'Su rostro no se encuentra en nuestros registros',
                            'icon' => 'error'
                        );
                        return $error;
                    }
                } catch (HttpException $ex) {
                    Marcation::create([
                        'type' => 'error',
                        'img' => $fully,
                        'description' => 'Error de Servidor: ' . $ex,
                        'date' => date("Y-m-d H:i:s")
                    ]);
                    $error = array(
                        'title' => 'Opps!',
                        'html' => 'Error de Servidor: ' . $ex,
                        'icon' => 'error'
                    );
                    return $error;
                }
            }
        } catch (\Throwable $th) {
            //throw $th;
            return response($th->getMessage().$th->getLine().$th->getFile());
        }
    }

    public function comprobarAccesoInstantaneo($func, $journy): bool
    {

        $horaEntrada = Carbon::parse($func->diariosTurnoFijo[0]['hora_' . $journy]);
        $horaActual = Carbon::now();
        $diferencia = $horaEntrada->diffInMinutes($horaActual);

        if ($diferencia <= 9) {
            return false;
        }
        return true;
    }

    public function responseAccesoInstantaneo($func): array
    {
        $respuesta = array(
            'title' => 'Acceso muy pronto',
            'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Acabas de ingresar, Espera unos minutos </strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong>",
            'icon' => 'warning'
        );
        return $respuesta;
    }


    private function ValidaTurnoFijo($func, $hoy, $hactual, $fully, $temperatura, $empresa)
    {
        if (count($func->diariosTurnoFijo) != 0) {

            if ($func->diariosTurnoFijo[0]['leave_time_one'] == null) {
                if (!$this->comprobarAccesoInstantaneo($func, 'entrada_uno')) {
                    return $this->responseAccesoInstantaneo($func);
                }
            }

            if ($func->diariosTurnoFijo[0]['leave_time_two'] == null && $func->diariosTurnoFijo[0]['entry_time_two'] != null) {
                if (!$this->comprobarAccesoInstantaneo($func, 'entrada_dos')) {
                    return $this->responseAccesoInstantaneo($func);
                }
            }

            if ($func->diariosTurnoFijo[0]['entry_time_two'] == null && $func->diariosTurnoFijo[0]['leave_time_one'] != null) {
                if (!$this->comprobarAccesoInstantaneo($func, 'salida_uno')) {
                    return $this->responseAccesoInstantaneo($func);
                }
            }
        }



        /** VALIDACION DE TURNO FIJO ASIGNADO AL FUNCIONARIO */


        if (count($func->diariosTurnoFijo) == 0) {
            /** VALIDO LA ENTRADA */
            if (isset($func->contractultimate->fixedTurn->horariosTurnoFijo[0])) {
                $hora = $func->contractultimate->fixedTurn->horariosTurnoFijo[0];

                $tipo_dia = date("w", strtotime($hoy));

                if ($hactual <= '12:00:00' && ($tipo_dia != 6 && $tipo_dia != 0)) {
                    $diferencia = $this->RestarHoras($hactual, $hora->entry_time_one);
                    $h_inicio = $hora->entry_time_one;
                } elseif ($hactual <= '12:00:00' && ($tipo_dia == 6 || $tipo_dia == 0)) {
                    $diferencia = $this->RestarHoras($hactual, $hora->entry_time_one);
                    $h_inicio = $hora->entry_time_one;
                } else {
                    $diferencia = $this->RestarHoras($hactual, $hora->entry_time_two);
                    $h_inicio = $hora->entry_time_two;
                }
                $dife = $diferencia;
                $diferencia = explode(":", $diferencia);

                $sig = 1;
                if (strpos($diferencia[0], "-") !== false) {
                    $sig = -1;
                    $diferencia[0] = str_replace("-", "", $diferencia[0]);
                }
                $diff_a = $diferencia[0] * 60;
                $diff_b = ($diff_a + $diferencia[1]) * $sig;

                $diff = (($diferencia[0] * 60 * 60) + ($diferencia[1] * 60) + ($diferencia[2])) * $sig;
                $tol_ent = ($hora->leave_tolerance * 60);

                /** GUARDO LOS DATOS DEL HORARIO DEL DIA */
                $datos = array(
                    'person_id' => $func->id,
                    'fecha' => $hoy,
                    'fixed_turn_id' => $hora->fixed_turn_id,
                    'entry_time_one' => $hactual,
                    'img_one' => $fully,
                    'temp_one' => $temperatura
                );
                Diarios::guardarDiarioTurnoFijo($datos);
                /** FIN DEL GUARDAR */


                if ($diff <= $tol_ent) {
                    if ($func->email != '') {
                        $obj = new \stdClass();
                        $obj->nombre = $func->nombres . " " . $func->apellidos;
                        $obj->imagen = $fully;
                        $obj->tipo = 'Ingreso';
                        $obj->hora = date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual));
                        $obj->ubicacion = 'entrada';
                        $obj->destino = $func->email;
                       /*  $obj->cargo = $func->cargo->nombre; */
                        /** Datos Empresa */
                        $obj->empresa = $empresa->razon_social;
                        $obj->nit = $empresa->numero_documento;
                        $obj->temperatura = $temperatura;
                        $obj->tarde = '';
                        /** Fin Datos Empresa */
                        // Mail::to($func->email)->send(new Correo($obj));
                    }

                    $respuesta = array(
                        'title' => 'Acceso Autorizado',
                        'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Bienvenido, Hoy ha llegado temprano</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong><br>" . date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual)),
                        'icon' => 'success'
                    );
                    return $respuesta;
                } else {

                    /** GUARDO LA LLEGADA TARDE */
                    $datos_llegada = array(
                        'person_id' => $func->id,
                        'date' => $hoy,
                        'time' => $diff,
                        'real_entry' => $hactual,
                        'entry' => $h_inicio
                    );
                    
                    Llegadas::guardarLlegadaTarde($datos_llegada);
                    /** FIN GUARDAR LLEGADA */

                    $lleg = 'Hoy ha Llegado tarde';
                    if ($func->email != '') {
                        $obj = new \stdClass();
                        $obj->nombre = $func->fist_name . " " . $func->first_surname;
                        $obj->imagen = $fully;
                        $obj->tipo = 'Ingreso';
                        $obj->hora = date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual));
                        $obj->ubicacion = 'entrada';
                        $obj->destino = $func->email;
                        /* $obj->cargo = $func->cargo->nombre; */
                        /** Datos Empresa */
                        $obj->empresa = $empresa->razon_social;
                        $obj->nit = $empresa->numero_documento;
                        $obj->temperatura = $temperatura;
                        $obj->tarde = $lleg;
                        // /** Fin Datos Empresa */
                        // Mail::to($func->email)->send(new Correo($obj));
                    }

                    $respuesta = array(
                        'title' => 'Acceso Autorizado',
                        'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Bienvenido</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong><br><strong style='color:red;'>" . $lleg . "</strong><br>" . date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual)),
                        'icon' => 'success'
                    );
                    return $respuesta;
                }
            } else {

                Marcation::create([
                    'type' => 'error',
                    'img' => $fully,
                    'description' => $func->id,
                    'dateles' => 'El Funcionario no tiene Turno Asignado',
                    'fecha' => date("Y-m-d H:i:s")
                ]);

                /** NO TIENE UN TURNO/HORARIO PARA ESE DIA */
                $error = array(
                    'title' => 'Sin Turno Asignado',
                    'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Hoy no Tiene un Turno Asignado</strong>",
                    'icon' => 'error'
                );
                return $error;
            }
        } else {
            $diario = $func->diariosTurnoFijo[0];
            if ($diario->leave_time_one == null) {
                if ($func->email != '') {
                    $obj = new \stdClass();
                    $obj->nombre = $func->nombres . " " . $func->apellidos;
                    $obj->imagen = $fully;
                    $obj->tipo = 'Salida';
                    $obj->hora = date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual));
                    $obj->ubicacion = 'entrada';
                    $obj->destino = $func->email;
                   /*  $obj->cargo = $func->cargo->nombre; */
                    /** Datos Empresa */
                    $obj->empresa = $empresa->razon_social;
                    $obj->nit = $empresa->numero_documento;
                    $obj->temperatura = $temperatura;
                    $obj->tarde = '';
                    /** Fin Datos Empresa */
                    // Mail::to($func->email)->send(new Correo($obj));
                }

                /** VALIDO LA SALIDA */
                $datos = array(
                    'leave_time_one' => $hactual,
                    'img_two' => $fully,
                    'temp_two' => $temperatura
                );
                Diarios::actualizaDiarioTurnoFijo($datos, $diario->id);
                $respuesta = array(
                    'title' => 'Hasta Luego',
                    'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Hasta Luego</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong><br>" . date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual)),
                    'icon' => 'success'
                );
                return $respuesta;
            } elseif ($diario->entry_time_two == null) {

                $hora = $func->contractultimate->fixedTurn->horariosTurnoFijo[0];

                //$hora = $func->turnoFijo->horariosTurnoFijo[0];
                $datos = array(
                    'entry_time_two' => $hactual,
                    'img_three' => $fully,
                    'temp_three' => $temperatura
                );
                Diarios::actualizaDiarioTurnoFijo($datos, $diario->id);

                $diferencia = $this->RestarHoras($hactual, $hora->entry_time_two);
                $diferencia = explode(":", $diferencia);
                $sig = 1;
                if (strpos($diferencia[0], "-") !== false) {
                    $sig = -1;
                    $diferencia[0] = str_replace("-", "", $diferencia[0]);
                }
                $diff = (($diferencia[0] * 60 * 60) + ($diferencia[1] * 60) + ($diferencia[2])) * $sig;
                $tol_ent = ($hora->leave_tolerance * 60);

                if ($diff >= $tol_ent) {
                    $datos_llegada = array(
                        'person_id' => $func->id,
                        'date' => $hoy,
                        'time' => $diff,
                        'real_entry' => $hactual,
                        'entry' => $hora->entry_time_two
                    );

                    Llegadas::guardarLlegadaTarde($datos_llegada);
                    /** FIN GUARDAR LLEGADA */

                    $lleg = 'Hoy ha Llegado tarde';

                    if ($func->email != '') {
                        $obj = new \stdClass();
                        $obj->nombre = $func->first_name . " " . $func->first_surname;
                        $obj->imagen = $fully;
                        $obj->tipo = 'Ingreso';
                        $obj->hora = date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual));
                        $obj->ubicacion = 'entrada';
                        $obj->destino = $func->email;
                        /* $obj->cargo = $func->cargo->nombre; */
                        /** Datos Empresa */
                        $obj->empresa = $empresa->razon_social;
                        $obj->nit = $empresa->numero_documento;
                        $obj->temperatura = $temperatura;
                        $obj->tarde = $lleg;
                        /** Fin Datos Empresa */
                        // Mail::to($func->email)->send(new Correo($obj));
                    }

                    $respuesta = array(
                        'title' => 'Bienvenido de Nuevo',
                        'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Bienvenido</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong><br><strong style='color:red;'>" . $lleg . "</strong><br>" . date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual)),
                        'icon' => 'success'
                    );
                    return $respuesta;
                } else {
                    if ($func->email != '') {
                        $obj = new \stdClass();
                        $obj->nombre = $func->nombres . " " . $func->apellidos;
                        $obj->imagen = $fully;
                        $obj->tipo = 'Ingreso';
                        $obj->hora = date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual));
                        $obj->ubicacion = 'entrada';
                        $obj->destino = $func->email;
                        /* $obj->cargo = $func->cargo->nombre; */
                        /** Datos Empresa */
                        $obj->empresa = $empresa->razon_social;
                        $obj->nit = $empresa->numero_documento;
                        $obj->temperatura = $temperatura;
                        $obj->tarde = '';
                        /** Fin Datos Empresa */
                        // Mail::to($func->email)->send(new Correo($obj));
                    }
                    $respuesta = array(
                        'title' => 'Bienvenido de Nuevo',
                        'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Bienvenido de Nuevo</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong><br>" . date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual)),
                        'icon' => 'success'
                    );
                    return $respuesta;
                }
            } elseif ($diario->leave_time_two == null) {
                if ($func->email != '') {
                    $obj = new \stdClass();
                    $obj->nombre = $func->nombres . " " . $func->apellidos;
                    $obj->imagen = $fully;
                    $obj->tipo = 'Salida';
                    $obj->hora = date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual));
                    $obj->ubicacion = 'entrada';
                    $obj->destino = $func->email;
                   /*  $obj->cargo = $func->cargo->nombre; */
                    /** Datos Empresa */
                    $obj->empresa = $empresa->razon_social;
                    $obj->nit = $empresa->numero_documento;
                    $obj->temperatura = $temperatura;
                    $obj->tarde = '';
                    /** Fin Datos Empresa */
                    // Mail::to($func->email)->send(new Correo($obj));
                }

                $datos = array(
                    'leave_time_two' => $hactual,
                    'img_four' => $fully,
                    'temp_four' => $temperatura
                );
                Diarios::actualizaDiarioTurnoFijo($datos, $diario->id);
                $respuesta = array(
                    'title' => 'Hasta Mañana',
                    'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Hasta Mañana</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong><br>" . date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual)),
                    'icon' => 'success'
                );
                return $respuesta;
            } else {
                Marcation::create([
                    'type' => 'error',
                    'img' => $fully,
                    'person_id' => $func->id,
                    'description' => 'El funcionario ya había reportado Turno',
                    'date' => date("Y-m-d H:i:s")
                ]);

                $respuesta = array(
                    'title' => 'Ya has reportado Turno',
                    'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Ya reportaste entrada y salida el día de hoy</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong>",
                    'icon' => 'warning'
                );
                return $respuesta;
            }
        }
    }
    private function ValidaTurnoRotativo($func, $hoy, $ayer, $hactual, $fully, $temperatura, $empresa)
    {

        if (count($func->diariosTurnoRotativoAyer) > 0) {
            $rotativo_ayer = $func->diariosTurnoRotativoAyer[0];
            $turno_rotativo_ayer = $rotativo_ayer->RotatingTurn;

            $startTime = Carbon::parse($hoy . " " . $hactual);
            $finishTime = Carbon::parse($ayer . " " . $turno_rotativo_ayer->hora_inicio_uno);
            $totalDuration = $finishTime->diffInSeconds($startTime) / 3600;

            if ($totalDuration < 24) {
                if ($func->email != '') {
                    $obj = new \stdClass();
                    $obj->nombre = $func->nombres . " " . $func->apellidos;
                    $obj->imagen = $fully;
                    $obj->tipo = 'Salida';
                    $obj->hora = date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual));
                    $obj->ubicacion = 'entrada';
                    $obj->destino = $func->email;
                    /* $obj->cargo = $func->cargo->nombre; */
                    /** Datos Empresa */
                    $obj->empresa = $empresa->razon_social;
                    $obj->nit = $empresa->numero_documento;
                    $obj->temperatura = $temperatura;
                    $obj->tarde = '';
                    /** Fin Datos Empresa */
                    // Mail::to($func->email)->send(new Correo($obj));
                }

                $datos = array(
                    'leave_date' => $hoy,
                    'leave_time_one' => $hactual,
                    'img_two' => $fully,
                    'temp_two' => $temperatura
                );
                Diarios::actualizaDiarioTurnoRotativo($datos, $rotativo_ayer->id);
                $respuesta = array(
                    'title' => 'Hasta Mañana',
                    'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Hasta Mañana</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong><br>" . date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual)),
                    'icon' => 'success'
                );
                return $respuesta;
            }
        }
        if (count($func->diariosTurnoRotativoHoy) > 0) {
            $rotativo_hoy = $func->diariosTurnoRotativoHoy[0];

            $startTime = Carbon::parse($hoy . " " . $hactual);
            $finishTime = Carbon::parse($rotativo_hoy->date . " " . $rotativo_hoy->entry_time_one);
            $totalDuration = $finishTime->diffInSeconds($startTime);

            if ($totalDuration > 600) {
                if ($rotativo_hoy->leave_time_one == null) {

                    if ($func->email != '') {
                        $obj = new \stdClass();
                        $obj->nombre = $func->nombres . " " . $func->apellidos;
                        $obj->imagen = $fully;
                        $obj->tipo = 'Salida';
                        $obj->hora = date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual));
                        $obj->ubicacion = 'entrada';
                        $obj->destino = $func->email;
                       /*  $obj->cargo = $func->cargo->nombre; */
                        /** Datos Empresa */
                        $obj->empresa = $empresa->razon_social;
                        $obj->nit = $empresa->numero_documento;
                        $obj->temperatura = $temperatura;
                        $obj->tarde = '';
                        /** Fin Datos Empresa */
                        // Mail::to($func->email)->send(new Correo($obj));
                    }

                    $datos = array(
                        'leave_date' => $hoy,
                        'leave_time_one' => $hactual,
                        'img_two' => $fully,
                        'temp_two' => $temperatura
                    );
                    Diarios::actualizaDiarioTurnoRotativo($datos, $rotativo_hoy->id);
                    $respuesta = array(
                        'title' => 'Hasta Mañana',
                        'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Hasta Mañana</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong><br>" . date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual)),
                        'icon' => 'success'
                    );
                    return $respuesta;
                } else {
                    Marcation::create([
                        'type' => 'error',
                        'img' => $fully,
                        'description' => $func->id,
                        'dateles' => 'El funcionario ya había reportado Turno',
                        'fecha' => date("Y-m-d H:i:s")
                    ]);

                    $respuesta = array(
                        'title' => 'Ya has reportado Turno',
                        'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Ya reportaste entrada y salida del turno de hoy</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong>",
                        'icon' => 'warning'
                    );
                    return $respuesta;
                }
            } else {
                Marcation::create([
                    'type' => 'error',
                    'img' => $fully,
                    'description' => $func->id,
                    'dateles' => 'El funcionario ya había reportado Ingreso',
                    'fecha' => date("Y-m-d H:i:s")
                ]);

                $respuesta = array(
                    'title' => 'Ya has reportado Ingreso',
                    'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Ya marcaste ingreso en un rango de 10 minutos</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong>",
                    'icon' => 'warning'
                );
                return $respuesta;
            }
        } else {
            if (count($func->horariosTurnoRotativo) > 0) {

                if ($func->horariosTurnoRotativo[0]->rotating_turn_id == 0) {
                    Marcation::create([
                        'type' => 'error',
                        'img' => $fully,
                        'description' => $func->id,
                        'dateles' => 'El funcionario tenía día de descanso',
                        'fecha' => date("Y-m-d H:i:s")
                    ]);

                    $respuesta = array(
                        'title' => 'Día de Descanso',
                        'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>De acuerdo a la programación, hoy es su día libre</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong>",
                        'icon' => 'warning'
                    );
                    return $respuesta;
                } else {
                    $turno_asignado = $func->horariosTurnoRotativo[0]->turnoRotativo;

                    $startTime = Carbon::parse($hoy . " " . $hactual);
                    $finishTime = Carbon::parse($hoy . " " . $turno_asignado->hora_inicio_uno);
                    $totalDuration = $finishTime->diffInSeconds($startTime, false);

                    $datos = array(
                        'person_id' => $func->id,
                        'date' => $hoy,
                        'rotating_turn_id' => $turno_asignado->id,
                        'entry_time_one' => $hactual,
                        'img_one' => $fully,
                        'temp_one' => $temperatura
                    );

                    if ($totalDuration > ($turno_asignado->leave_tolerance * 60)) {

                        Diarios::guardarDiarioTurnoRotativo($datos);

                        $datos_llegada = array(
                            'person_id' => $func->id,
                            'date' => $hoy,
                            'time' => $totalDuration,
                            'real_entry' => $hactual,
                            'entry' => $turno_asignado->hora_inicio_uno
                        );
                      
                        Llegadas::guardarLlegadaTarde($datos_llegada);
                        /** FIN GUARDAR LLEGADA */

                        $lleg = 'Hoy ha Llegado tarde';

                        if ($func->email != '') {
                            $obj = new \stdClass();
                            $obj->nombre = $func->nombres . " " . $func->apellidos;
                            $obj->imagen = $fully;
                            $obj->tipo = 'Ingreso';
                            $obj->hora = date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual));
                            $obj->ubicacion = 'entrada';
                            $obj->destino = $func->email;
                           /*  $obj->cargo = $func->cargo->nombre; */
                            /** Datos Empresa */
                            $obj->empresa = $empresa->razon_social;
                            $obj->nit = $empresa->numero_documento;
                            $obj->temperatura = $temperatura;
                            $obj->tarde = $lleg;
                            /** Fin Datos Empresa */
                            // Mail::to($func->email)->send(new Correo($obj));
                        }

                        $respuesta = array(
                            'title' => 'Acceso Autorizado',
                            'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Bienvenido</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong><br><strong style='color:red;'>" . $lleg . "</strong><br>" . date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual)),
                            'icon' => 'success'
                        );
                        return $respuesta;
                    } elseif ($totalDuration < (-3600)) {
                        Marcation::create([
                            'type' => 'error',
                            'img' => $fully,
                            'description' => $func->id,
                            'dateles' => 'El funcionario estaba marcando turno muy temprano',
                            'fecha' => date("Y-m-d H:i:s")
                        ]);

                        $respuesta = array(
                            'title' => 'Muy Temprano',
                            'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Su turno asignado para hoy,<br> tiene hora de Ingreso a las " . $turno_asignado->hora_inicio_uno . "</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong>",
                            'icon' => 'warning'
                        );
                        return $respuesta;
                    } else {
                        if ($func->email != '') {
                            $obj = new \stdClass();
                            $obj->nombre = $func->nombres . " " . $func->apellidos;
                            $obj->imagen = $fully;
                            $obj->tipo = 'Ingreso';
                            $obj->hora = date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual));
                            $obj->ubicacion = 'entrada';
                            $obj->destino = $func->email;
                           /*  $obj->cargo = $func->cargo->nombre; */
                            /** Datos Empresa */
                            $obj->empresa = $empresa->razon_social;
                            $obj->nit = $empresa->numero_documento;
                            $obj->temperatura = $temperatura;
                            $obj->tarde = '';
                            /** Fin Datos Empresa */
                            // Mail::to($func->email)->send(new Correo($obj));
                        }

                        Diarios::guardarDiarioTurnoRotativo($datos);

                        $respuesta = array(
                            'title' => 'Acceso Autorizado',
                            'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>Bienvenido, Hoy ha llegado temprano</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong><br>" . date("d/m/Y H:i:s", strtotime($hoy . " " . $hactual)),
                            'icon' => 'success'
                        );
                        return $respuesta;
                    }
                }
            } else {
                Marcation::create([
                    'type' => 'error',
                    'img' => $fully,
                    'description' => $func->id,
                    'dateles' => 'El funcionario no tenía turno asignado',
                    'fecha' => date("Y-m-d H:i:s")
                ]);

                $respuesta = array(
                    'title' => 'Sin Turno Asignado',
                    'html' => "<img src='" . $func->image . "' class='img-thumbnail rounded-circle img-fluid' style='max-width:140px;'  /><br><strong>No tiene un turno asignado para este día, por favor comuníquese con su superior.</strong><br><strong>" . $func->nombres . " " . $func->apellidos . "</strong>",
                    'icon' => 'warning'
                );
                return $respuesta;
            }
        }
    }
    private function ValidaTurnoLibre($func, $hoy, $hactual, $fully, $empresa)
    {
        return "Turno Libre";
    }
    private function RestarHoras($horaini, $horafin)
    {


        $horai = (int)substr($horaini, 0, 2);
        $mini = (int)substr($horaini, 3, 2);
        $segi = (int)substr($horaini, 6, 2);

        $horaf = (int)substr($horafin, 0, 2);
        $minf = (int)substr($horafin, 3, 2);
        $segf = (int)substr($horafin, 6, 2);

        $ini = ((($horai * 60) * 60) + ($mini * 60) + $segi);
        $fin = ((($horaf * 60) * 60) + ($minf * 60) + $segf);

        $dif = $fin - $ini;
        $band = 0;
        if ($dif < 0) {
            $dif = $dif * (-1);
            $band = 1;
        }

        $difh = floor($dif / 3600);
        $difm = floor(($dif - ($difh * 3600)) / 60);
        $difs = $dif - ($difm * 60) - ($difh * 3600);
        if ($band == 0) {
            return "-" . date("H:i:s", mktime($difh, $difm, $difs));
        } else {
            return date("H:i:s", mktime($difh, $difm, $difs));
        }
    }
}
