<?php
// config/routes.php
use Slim\App;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

return function (App $app) {
    // CORS middleware is globally registered in index.php

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

        $group->group('/boletas', function ($boletaGroup) {
            $boletaGroup->get('', \App\Action\Boleta\ListarBoletasAction::class);
            $boletaGroup->get('/ultima', \App\Action\Boleta\GetUltimaBoletaAction::class);
            $boletaGroup->get('/{id:[0-9]+}', \App\Action\Boleta\GetBoletaAction::class);
            $boletaGroup->get('/{id:[0-9]+}/pdf', \App\Action\Boleta\DescargarBoletaPdfAction::class);
            $boletaGroup->post('', \App\Action\Boleta\CrearBoletaAction::class);
            $boletaGroup->post('/calcular', \App\Action\Boleta\CalcularBoletaAction::class);
        })->add(\App\Middleware\JwtAuthMiddleware::class);

        $group->group('/farmacias', function ($farmaciasGroup) {
            $farmaciasGroup->get('/dashboard', \App\Action\Farmacia\GetDashboardFarmaciaAction::class);
            $farmaciasGroup->get('', \App\Action\Farmacia\ListarFarmaciasAction::class);
            $farmaciasGroup->post('', \App\Action\Farmacia\CrearFarmaciaAction::class);
            $farmaciasGroup->put('/{id:[0-9]+}', \App\Action\Farmacia\ActualizarFarmaciaAction::class);
            $farmaciasGroup->put('/{id:[0-9]+}/baja', \App\Action\Farmacia\BajaFarmaciaAction::class);
        })->add(\App\Middleware\JwtAuthMiddleware::class);

        $group->group('/maestros', function ($maestrosGroup) {
            $maestrosGroup->get('/categorias', \App\Action\Maestro\ListarCategoriasAction::class);
        })->add(\App\Middleware\JwtAuthMiddleware::class);

        $group->group('/personas', function ($personaGroup) {
            $personaGroup->get('/{cuil}', \App\Action\Persona\GetPersonaByCuilAction::class);
        })->add(\App\Middleware\JwtAuthMiddleware::class);

        $group->group('/admin', function ($adminGroup) {
            $adminGroup->get('/dashboard', \App\Action\Admin\GetAdminDashboardAction::class);
        })->add(\App\Middleware\JwtAuthMiddleware::class);

    });
};
