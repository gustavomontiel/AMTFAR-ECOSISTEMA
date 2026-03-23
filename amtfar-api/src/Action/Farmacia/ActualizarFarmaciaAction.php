<?php
namespace App\Action\Farmacia;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Farmacia;

class ActualizarFarmaciaAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? null;
        $body = $request->getParsedBody();

        try {
            $farmacia = Farmacia::find($id);

            if (!$farmacia) {
                $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Farmacia no encontrada.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            if (isset($body['razon_social'])) $farmacia->razon_social = $body['razon_social'];
            if (isset($body['nombre_fantasia'])) $farmacia->nombre_fantasia = $body['nombre_fantasia'];
            if (isset($body['cuit'])) $farmacia->cuit = $body['cuit'];
            if (isset($body['direccion'])) $farmacia->direccion = $body['direccion'];
            if (isset($body['telefono'])) $farmacia->telefono = $body['telefono'];
            if (isset($body['localidad_id'])) $farmacia->localidad_id = $body['localidad_id'];
            if (isset($body['estado_baja'])) $farmacia->estado_baja = $body['estado_baja'];

            $farmacia->save();

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Farmacia actualizada exitosamente.',
                'data' => $farmacia
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
