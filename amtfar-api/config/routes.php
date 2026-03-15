<?php
// config/routes.php
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    // Definición de CORS para aceptar peticiones del Frontend Angular (preflight)
    $app->options('/{routes:.+}', function ($request, $response, $args) {
        return $response;
    });

    $app->add(function ($request, $handler) {
        $response = $handler->handle($request);
        return $response
                ->withHeader('Access-Control-Allow-Origin', '*')
                ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
                ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS');
    });

    // Rutas Base API
    $app->get('/', function (Request $request, Response $response) {
        $response->getBody()->write(json_encode(['message' => 'AMTFAR API v1.0 funcionando.']));
        return $response->withHeader('Content-Type', 'application/json');
    });

    // ----------------------------------------------------
    // API v1 Rutas (ADR Pattern)
    // ----------------------------------------------------
    $app->group('/api/v1', function (\Slim\Routing\RouteCollectorProxy $group) {
        
        $group->get('/health', function (Request $request, Response $response) {
            $response->getBody()->write(json_encode(['status' => 'OK']));
            return $response->withHeader('Content-Type', 'application/json');
        });

        // ----------------------------------------------------
        // Rutas Públicas
        // ----------------------------------------------------
        $group->group('/auth', function ($authGroup) {
            $authGroup->post('/login', \App\Action\Auth\LoginAction::class);
        });

        // ----------------------------------------------------
        // Rutas Protegidas por JWT
        // ----------------------------------------------------
        $group->group('/empleados', function ($empleadosGroup) {
            $empleadosGroup->get('', \App\Action\Empleado\ListEmpleadosAction::class);
        })->add(\App\Middleware\JwtAuthMiddleware::class);

    });
};
