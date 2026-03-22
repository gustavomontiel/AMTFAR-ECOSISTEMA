<?php
namespace App\Action\Maestro;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Categoria;

class ListarCategoriasAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $categorias = Categoria::all();
        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => $categorias
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
