<?php
namespace App\Action\Boleta;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Boleta;

class ListarBoletasAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('jwt_data');
        $id_farmacia = $user->id_farmacia ?? 1;

        try {
            // Get all boletas for the pharmacy ordered by most recent period
            $boletas = Boleta::where('farmacia_id', $id_farmacia)
                ->orderBy('periodo_id', 'desc')
                ->orderBy('id', 'desc')
                ->get();

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $boletas
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
