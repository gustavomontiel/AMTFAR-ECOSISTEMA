<?php
namespace App\Action\Boleta;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Farmacia;
use App\Domain\Models\Boleta;
use App\Domain\Models\BoletaRemuneracion;
use App\Domain\Models\BoletaConcepto;
use App\Domain\Models\Concepto;
use App\Domain\Models\Persona;
use App\Domain\Models\Empleado;
use App\Domain\Models\Periodo;
use Illuminate\Database\Capsule\Manager as DB;

class CrearBoletaAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('jwt_data');
        $id_farmacia = $user->id_farmacia ?? 1;

        $payload = $request->getParsedBody();
        $periodo_str = $payload['periodo'] ?? date('Ym');
        $empleadosPayload = $payload['empleados'] ?? [];

        if (empty($empleadosPayload)) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Debe incluir al menos un empleado.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $isDraft = !empty($payload['is_draft']);
        $estado = $isDraft ? 0 : 1;

        // --- VALIDACIÓN ESTRICTA: BLOQUEO CRONOLÓGICO ---
        // Evitamos que guarden/generen un periodo si faltan meses anteriores (Boletas Salteadas)
        $farmacia = Farmacia::find($id_farmacia);
        $primeraBoleta = Boleta::where('farmacia_id', $id_farmacia)->where('estado', '>', 0)->orderBy('periodo_id', 'asc')->first();
        
        $anyoReq = substr($periodo_str, 0, 4);
        $mesReq = substr($periodo_str, 4, 2);
        $fechaTope = new \DateTime("$anyoReq-$mesReq-01");
        $fechaTope->modify('-1 month'); // Exigimos hasta el mes anterior exacto al que intenta declarar

        $fechaAlta = null;
        if ($farmacia && $farmacia->fecha_alta) {
            $fechaAlta = new \DateTime($farmacia->fecha_alta);
        } else if ($primeraBoleta && $primeraBoleta->periodo_id < $periodo_str) {
            $anyoPrim = substr($primeraBoleta->periodo_id, 0, 4);
            $mesPrim = substr($primeraBoleta->periodo_id, 4, 2);
            $fechaAlta = new \DateTime("$anyoPrim-$mesPrim-01");
        }

        if ($fechaAlta && $fechaAlta <= $fechaTope) {
            $fechaAlta->modify('first day of this month');
            $boletasValidadas = Boleta::where('farmacia_id', $id_farmacia)->get()->keyBy('periodo_id');
            
            $currentDate = clone $fechaAlta;
            while ($currentDate <= $fechaTope) {
                $perIdCheck = $currentDate->format('Ym');
                // Si falta la boleta por completo del mes anterior...
                if (!$boletasValidadas->has($perIdCheck)) {
                    $response->getBody()->write(json_encode(['status' => 'error', 'message' => "Bloqueo Cronológico: No puede declarar el período actual porque falta registrar la nómina del período previo ($perIdCheck)."]));
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
                } else {
                    $bolCheck = $boletasValidadas->get($perIdCheck);
                    // Si la boleta del mes anterior existe pero se quedó en borrador.
                    if ($bolCheck->estado == 0 && $perIdCheck != $periodo_str) {
                        $response->getBody()->write(json_encode(['status' => 'error', 'message' => "Bloqueo Cronológico: El período previo ($perIdCheck) aún está en estado Borrador. Debe finalizarlo antes de continuar con meses posteriores."]));
                        return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
                    }
                }
                $currentDate->modify('+1 month');
            }
        }
        // --- FIN VALIDACIÓN ---

        DB::beginTransaction();
        try {
            // Verificar o Crear el periodo
            $periodo = Periodo::firstOrCreate(['id' => $periodo_str], ['estado' => 1]);

            $totalRemunerativo = 0;
            $boletaRemuneraciones = [];
            
            // Si NO es borrador, procesamos las tablas relacionales de verdad.
            // Si ES borrador, solo lo guardamos en la cabecera como JSON y calculamos visualmente.
            if (!$isDraft) {
                // Procesar Empleados
                $incomingCuils = [];
                foreach ($empleadosPayload as $reqEmp) {
                    $incomingCuils[] = preg_replace('/[^0-9]/', '', (string)$reqEmp['cuil']);

                    // Upsert Persona
                    $persona = Persona::updateOrCreate(
                        ['cuil' => $reqEmp['cuil']],
                        ['nombre' => $reqEmp['nombre']]
                    );

                    // Upsert Empleado para mantener el listado activo de la farmacia actualizado
                    $empleado = Empleado::updateOrCreate(
                        [
                            'farmacia_id' => $id_farmacia,
                            'persona_id' => $persona->id
                        ],
                        [
                            'categoria_id' => $reqEmp['categoria_id'],
                            'fecha_ingreso' => empty($reqEmp['fecha_ingreso']) ? null : $reqEmp['fecha_ingreso'],
                            'fecha_egreso' => empty($reqEmp['fecha_egreso']) ? null : $reqEmp['fecha_egreso'],
                            'importe_remunerativo' => $reqEmp['importe_remunerativo'],
                            'importe_no_remunerativo' => $reqEmp['importe_no_remunerativo'],
                            'estado_baja' => empty($reqEmp['fecha_egreso']) ? 0 : 1
                        ]
                    );

                    $totalRemunerativo += floatval($reqEmp['importe_remunerativo']);

                    $boletaRemuneraciones[] = new BoletaRemuneracion([
                        'empleado_id' => $empleado->id,
                        'categoria_id' => $reqEmp['categoria_id'],
                        'importe_remunerativo' => $reqEmp['importe_remunerativo'],
                        'importe_no_remunerativo' => $reqEmp['importe_no_remunerativo'],
                        'fecha_ingreso_historica' => empty($reqEmp['fecha_ingreso']) ? null : $reqEmp['fecha_ingreso']
                    ]);
                }

                // --- REGLA DE NEGOCIO: Bajas automáticas para empleados omitidos ---
                $activeEmpleados = Empleado::with('persona')->where('farmacia_id', $id_farmacia)->where('estado_baja', 0)->get();
                // Fecha de egreso arbitraria: último día del mes anterior al periodo declarado.
                // Ej: si periodo es "202603", strtotime("20260301 -1 month") -> Feb 2026, "Y-m-t" da el día 28.
                $lastDayPrevMonth = date("Y-m-t", strtotime($periodo_str . "01 -1 month"));
                
                foreach ($activeEmpleados as $empDb) {
                    if ($empDb->persona && $empDb->persona->cuil) {
                        $dbCuil = preg_replace('/[^0-9]/', '', (string)$empDb->persona->cuil);
                        if (!in_array($dbCuil, $incomingCuils)) {
                            // Si NO está en la boleta actual, se da de baja
                            $empDb->fecha_egreso = $lastDayPrevMonth;
                            $empDb->estado_baja = 1;
                            $empDb->save();
                        }
                    }
                }

            } else {
                // Cálculo simple de total para el draft sin insertar empleados
                foreach ($empleadosPayload as $reqEmp) {
                    $totalRemunerativo += floatval($reqEmp['importe_remunerativo']);
                }
            }

            // Calcular Conceptos
            $conceptosDB = Concepto::where('siempre', 1)->get();
            $totalAPagar = 0;
            $boletaConceptos = [];

            foreach ($conceptosDB as $concepto) {
                $monto = 0;
                if ($concepto->porcentaje > 0) {
                    $monto = $totalRemunerativo * ($concepto->porcentaje / 100);
                } else if ($concepto->importe > 0) {
                    $monto = $concepto->importe;
                }

                if (!$isDraft) {
                    $boletaConceptos[] = new BoletaConcepto([
                        'concepto_id' => $concepto->id,
                        'descripcion_historica' => $concepto->descripcion,
                        'porcentaje_historico' => $concepto->porcentaje,
                        'importe_calculado' => round($monto, 2)
                    ]);
                }
                $totalAPagar += $monto;
            }

            // Crear o Actualizar Cabecera de Boleta
            $boleta = null;

            if (!empty($payload['boleta_id'])) {
                $boleta = Boleta::where('id', $payload['boleta_id'])
                                ->where('farmacia_id', $id_farmacia)
                                ->where('estado', 0)
                                ->first();
                if (!$boleta) {
                    throw new \Exception("Boleta en borrador no encontrada o ya fue generada.");
                }
                
                $updateData = [
                    'imponible_declarado' => $totalRemunerativo,
                    'imponible_calculado' => $totalRemunerativo,
                    'total_deposito' => round($totalAPagar, 2),
                    'estado' => $estado,
                    'draft_payload' => $isDraft ? json_encode($empleadosPayload) : null
                ];
                
                if (!$isDraft) {
                    $updateData['fecha_vto'] = date('Y-m-d', strtotime('+15 days'));
                    $updateData['fecha_boleta'] = date('Y-m-d'); // Corremos 15 días exactos e instanciamos la fecha actual de finalización
                }
                
                $boleta->update($updateData);
                
                if (!$isDraft) {
                    // Clear old relations to replace with new valid ones
                    $boleta->remuneraciones()->delete();
                    $boleta->conceptos()->delete();
                }
            } else {
                $boleta = Boleta::create([
                    'farmacia_id' => $id_farmacia,
                    'periodo_id' => $periodo->id,
                    'fecha_vto' => $isDraft ? date('Y-m-d', strtotime('+30 days')) : date('Y-m-d', strtotime('+15 days')),
                    'fecha_boleta' => date('Y-m-d'),
                    'imponible_declarado' => $totalRemunerativo,
                    'imponible_calculado' => $totalRemunerativo,
                    'total_deposito' => round($totalAPagar, 2),
                    'estado' => $estado,
                    'draft_payload' => $isDraft ? json_encode($empleadosPayload) : null
                ]);
            }

            if (!$isDraft) {
                // Guardar el snapshot solo si cerramos la boleta
                $boleta->remuneraciones()->saveMany($boletaRemuneraciones);
                $boleta->conceptos()->saveMany($boletaConceptos);
            }

            DB::commit();

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Boleta generada exitosamente.',
                'data' => [
                    'boleta_id' => $boleta->id,
                    'total_a_pagar' => $boleta->total_deposito
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (\Exception $e) {
            DB::rollBack();
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
