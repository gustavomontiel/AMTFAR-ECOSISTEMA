<?php
namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtAuthMiddleware
{
    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $header = $request->getHeaderLine('Authorization');
        
        if (empty($header) || !preg_match('/Bearer\s(\S+)/', $header, $matches)) {
            return $this->unauthorizedResponse('Token no provisto o con formato inválido.');
        }

        $token = $matches[1];
        
        try {
            $secretKey = $_ENV['JWT_SECRET'] ?? 'AMTFAR_Super_Secret_Key_2026_Secure!';
            $decoded = JWT::decode($token, new Key($secretKey, 'HS256'));
            
            // Inyectar el payload en la petición para que los Actions puedan acceder a sus datos (ej: id_usuario).
            $request = $request->withAttribute('jwt_payload', $decoded);
            
            // Continuar con la acción
            return $handler->handle($request);
            
        } catch (\Firebase\JWT\ExpiredException $e) {
            return $this->unauthorizedResponse('El token ha expirado. Por favor, inicie sesión nuevamente.');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse('Token inválido o corrompido. Detalle: ' . $e->getMessage());
        }
    }

    private function unauthorizedResponse(string $message): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode([
            'status' => 'error',
            'message' => $message
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
}
