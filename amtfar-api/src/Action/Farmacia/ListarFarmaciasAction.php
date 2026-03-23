<?php
namespace App\Action\Farmacia;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Farmacia;

class ListarFarmaciasAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try {
            // Retrieve all farmacias. 
            $farmacias = Farmacia::orderBy('id', 'desc')->get();

            $response->getBody()->write(json_encode([
                'status' => 'success',
                'data' => $farmacias
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(200);

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => $e->getMessage()]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
