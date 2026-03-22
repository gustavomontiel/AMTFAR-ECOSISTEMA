<?php
namespace App\Action\Persona;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Persona;
use App\Domain\Models\Empleado;

class GetPersonaByCuilAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('jwt_data');
        $id_farmacia = $user->id_farmacia ?? 1;
        $cuil = $args['cuil'] ?? '';

        if (empty($cuil)) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'CUIL is required.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            $persona = Persona::where('cuil', $cuil)->first();

            if (!$persona) {
                $response->getBody()->write(json_encode([
                    'status' => 'not_found',
                    'data' => null
                ]));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
            }

            // Verificar si la persona ya es un empleado activo de esta farmacia
            $empleado = Empleado::where('persona_id', $persona->id)
                                ->where('farmacia_id', $id_farmacia)
                                ->where('estado_baja', 0)
                                ->first();

            $data = [
                'nombre' => $persona->nombre,
                'es_empleado' => $empleado ? true : false,
                'fecha_ingreso' => $empleado ? $empleado->fecha_ingreso : null,
                'categoria_id' => $empleado ? $empleado->categoria_id : null
            ];

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $data
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
