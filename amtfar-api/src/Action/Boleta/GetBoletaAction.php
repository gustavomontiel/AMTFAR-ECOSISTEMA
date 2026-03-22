<?php
namespace App\Action\Boleta;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Boleta;

class GetBoletaAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('jwt_data');
        $id_farmacia = $user->id_farmacia ?? 1;
        
        $id_boleta = $args['id'] ?? null;

        if (!$id_boleta) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Boleta ID is required.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $boleta = Boleta::with(['remuneraciones.empleado.persona', 'remuneraciones.categoria'])
                ->where('id', $id_boleta)
                ->where('farmacia_id', $id_farmacia)
                ->first();

            if (!$boleta) {
                $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Boleta no encontrada o acceso denegado.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            // Mapear remuneraciones al mismo formato de lista interactiva
            $empleadosMapeados = [];

            if ($boleta->estado == 0 && !empty($boleta->draft_payload)) {
                $empleadosMapeados = json_decode($boleta->draft_payload, true) ?? [];
            } else {
                foreach ($boleta->remuneraciones as $rem) {
                     if (!$rem->empleado || !$rem->empleado->persona) continue;
                     
                     $empleadosMapeados[] = [
                        'cuil' => $rem->empleado->persona->cuil,
                        'nombre' => $rem->empleado->persona->nombre,
                        'categoria_id' => $rem->categoria_id,
                        'categoria_descripcion' => $rem->categoria ? $rem->categoria->descripcion : '',
                        'fecha_ingreso' => $rem->fecha_ingreso_historica,
                        'fecha_egreso' => $rem->empleado->fecha_egreso,
                        'importe_remunerativo' => $rem->importe_remunerativo,
                        'importe_no_remunerativo' => $rem->importe_no_remunerativo
                     ];
                }
            }

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => [
                    'boleta' => $boleta,
                    'empleados' => $empleadosMapeados
                ]
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
