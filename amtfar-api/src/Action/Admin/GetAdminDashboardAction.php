<?php
namespace App\Action\Admin;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Farmacia;
use App\Domain\Models\Boleta;
use App\Domain\Models\Empleado;
use Illuminate\Database\Capsule\Manager as DB;

class GetAdminDashboardAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        // 1. KPIs Globales
        $activas = Farmacia::where('estado_baja', 0)->count();
        $empleadosTotales = Empleado::where('estado_baja', 0)->count();
        
        $mesActual = date('Ym');
        $recaudacionMes = Boleta::where('periodo_id', $mesActual)
                                ->where('estado', 3)
                                ->sum('total_deposito');
                                
        $deudaMes = Boleta::where('periodo_id', $mesActual)
                          ->whereIn('estado', [1, 2])
                          ->sum('total_deposito');

        $recaudacionHistorica = Boleta::where('estado', 3)
                                ->sum('total_deposito');

        // 2. Ranking de Morosidad (Boletas Impagas)
        $rankingDeudores = Boleta::select('farmacias.id', 'farmacias.razon_social', 'farmacias.nombre_fantasia', 'farmacias.cuit', DB::raw('SUM(boletas.total_deposito) as deuda_total'), DB::raw('COUNT(boletas.id) as boletas_impagas'))
            ->join('farmacias', 'boletas.farmacia_id', '=', 'farmacias.id')
            ->whereIn('boletas.estado', [1, 2])
            ->where('boletas.total_deposito', '>', 0)
            ->groupBy('farmacias.id', 'farmacias.razon_social', 'farmacias.nombre_fantasia', 'farmacias.cuit')
            ->orderBy('deuda_total', 'desc')
            ->limit(10)
            ->get();
            
        // Mappear denominacion para el Frontend
        $rankingDeudores->map(function($item) {
            $item->denominacion = $item->nombre_fantasia ?? $item->razon_social;
            return $item;
        });

        $responseData = [
            'kpis' => [
                'farmacias_activas' => $activas,
                'empleados_activos' => $empleadosTotales,
                'recaudacion_mes' => $recaudacionMes ?? 0,
                'deuda_mes' => $deudaMes ?? 0,
                'recaudacion_historica' => $recaudacionHistorica ?? 0
            ],
            'ranking_morosidad' => $rankingDeudores
        ];

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => $responseData
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
