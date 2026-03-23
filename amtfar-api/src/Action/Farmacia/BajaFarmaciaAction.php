<?php
namespace App\Action\Farmacia;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Farmacia;
use App\Domain\Models\Boleta;

class BajaFarmaciaAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'] ?? null;

        try {
            $farmacia = Farmacia::find($id);

            if (!$farmacia) {
                $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Farmacia no encontrada.']));
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }

            // Baja Logica: estado_baja = 1
            $farmacia->estado_baja = 1;
            $farmacia->save();

            // Deactivate any linked user? We can leave the user active but the farmacia is inactive, 
            // the UI will handle it or they simply can't generate boletas anymore.

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Farmacia dada de baja exitosamente.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
