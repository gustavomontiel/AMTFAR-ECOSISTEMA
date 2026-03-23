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
        $identifier = $body['username'] ?? ''; // El frontend enviará username o cuit aquí
        $password = $body['password'] ?? '';
        $type = $body['type'] ?? ''; // 'farmacia' o 'backoffice'

        if (empty($identifier) || empty($password)) {
            $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Credenciales incompletas.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Validación
        $isValidAuth = false;
        $usuario = null;
        $permisos = [];

        // Mock Admin overrides DB check
        if ($identifier === 'admin' && $password === '123') {
             $isValidAuth = true;
             $usuario = (object)[
                 'id' => 1,
                 'username' => 'admin',
                 'farmacia_id' => null,
                 'estado' => 1,
                 'farmacia' => null,
                 'rol' => (object)['descripcion' => 'SuperAdmin']
             ];
             $permisos = ['ver_dashboard', 'gestionar_usuarios', 'gestionar_farmacias', 'ver_reportes'];
        } else {
             // Try to find as Farmacia if type is farmacia or identifier is numeric length 11
             if ($type === 'farmacia' || (is_numeric($identifier) && strlen((string)$identifier) === 11)) {
                 $farmacia = \App\Domain\Models\Farmacia::where('cuit', $identifier)->first();
                 if ($farmacia) {
                     $usuario = Usuario::with(['farmacia', 'rol'])->where('farmacia_id', $farmacia->id)->first();
                 }
             } else {
                 // Try to find as Backoffice
                 $usuario = Usuario::with(['rol.permisos'])->where('username', $identifier)->whereNull('farmacia_id')->first();
                 if ($usuario && $usuario->rol) {
                     $permisos = $usuario->rol->permisos->pluck('nombre')->toArray();
                 }
             }

             if ($usuario && $password === $usuario->password) {
                 $isValidAuth = true;
             }
        }

        if (!$isValidAuth) {
             $response->getBody()->write(json_encode([
                'status' => 'error',
                'message' => 'Usuario o contraseña incorrectos.'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
        }

        if ($usuario->estado != 1) { // == 0 inactivos etc
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
                'id_farmacia' => $usuario->farmacia_id ?? null,
                'permisos'   => $permisos
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
                    'farmacia' => $usuario->farmacia->nombre_fantasia ?? 'Sede Central',
                    'permisos' => $permisos
                ]
            ]
        ]));
        
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
