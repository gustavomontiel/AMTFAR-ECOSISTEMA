<?php
namespace App\Action\Boleta;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
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
                foreach ($empleadosPayload as $reqEmp) {
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
