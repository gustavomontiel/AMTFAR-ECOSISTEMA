<?php
namespace App\Action\Auth;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Usuario;
use Firebase\JWT\JWT;

class LoginAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $body = $request->getParsedBody();
        $username = $body['username'] ?? '';
        $password = $body['password'] ?? '';

        if (empty($username) || empty($password)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Credenciales incompletas.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Buscar al usuario
        $usuario = Usuario::with(['farmacia', 'rol'])->where('username', $username)->first();

        // Validación Dummy (en un entorno real será password_verify($password, $usuario->password))
        // Dado que la BBDD está vacía de usuarios, permitiremos login libre si pone username "admin"
        $isValidAuth = false;

        if ($usuario) {
             // Comprobar BD Real
             if ($password === $usuario->password) {
                 $isValidAuth = true;
             }
        } else if ($username === 'admin' && $password === '123') {
             // Mock Admin
             $isValidAuth = true;
             $usuario = (object)[
                 'id' => 1,
                 'username' => 'admin',
                 'farmacia_id' => null,
                 'estado' => 1,
                 'farmacia' => null,
                 'rol' => (object)['descripcion' => 'SuperAdmin']
             ];
        }

        if (!$isValidAuth) {
             $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Usuario o contraseña incorrectos.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        if ($usuario->estado != 1) {
             $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'El usuario se encuentra inactivo.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(403);
        }

        // Emitir JWT
        $secretKey  = $_ENV['JWT_SECRET'] ?? 'AMTFAR_Super_Secret_Key_2026_Secure!';
        $issuedAt   = new \DateTimeImmutable();
        $expire     = $issuedAt->modify('+8 hours')->getTimestamp();      
        $serverName = "amtfar-api";

        $payload = [
            'iat'  => $issuedAt->getTimestamp(),
            'iss'  => $serverName,
            'nbf'  => $issuedAt->getTimestamp(),
            'exp'  => $expire,
            'data' => [
                'id_usuario' => $usuario->id,
                'username'   => $usuario->username,
                'id_farmacia' => $usuario->farmacia_id,
            ]
        ];

        $token = JWT::encode($payload, $secretKey, 'HS256');

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => [
                'token' => $token,
                'expires_at' => $expire,
                'user' => [
                    'username' => $usuario->username,
                    'rol' => $usuario->rol->descripcion ?? 'Farmacia',
                    'farmacia' => $usuario->farmacia->nombre_fantasia ?? 'Sede Central'
                ]
            ]
        ]));
        
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
