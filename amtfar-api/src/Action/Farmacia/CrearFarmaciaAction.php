<?php
namespace App\Action\Farmacia;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Farmacia;
use App\Domain\Models\Usuario;
use Illuminate\Database\Capsule\Manager as DB;

class CrearFarmaciaAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();
        
        // Basic validation
        if (empty($body['cuit']) || empty($body['razon_social'])) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'CUIT y Razón Social son requeridos.']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        try {
            DB::beginTransaction();

            // Create Farmacia
            $farmacia = new Farmacia();
            $farmacia->razon_social = $body['razon_social'];
            $farmacia->nombre_fantasia = $body['nombre_fantasia'] ?? null;
            $farmacia->cuit = $body['cuit'];
            $farmacia->direccion = $body['direccion'] ?? null;
            $farmacia->telefono = $body['telefono'] ?? null;
            $farmacia->localidad_id = $body['localidad_id'] ?? null;
            $farmacia->estado_baja = 0; // 0 = activo, 1 = baja
            $farmacia->save();

            // Create associated User for the farmacia
            $usuario = new Usuario();
            $usuario->username = 'farmacia_' . $farmacia->id;
            $usuario->password = '123456'; // Default password, they should ideally change it
            $usuario->farmacia_id = $farmacia->id;
            $usuario->rol_id = 2; // Assuming 2 is 'Farmacia' role.
            $usuario->estado = 1; // 1 = activo
            $usuario->save();

            DB::commit();

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'message' => 'Farmacia creada exitosamente.',
                'data' => $farmacia
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

        } catch (\Exception $e) {
            DB::rollBack();
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
