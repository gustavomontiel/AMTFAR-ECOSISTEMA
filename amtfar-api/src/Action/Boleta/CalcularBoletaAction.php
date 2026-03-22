<?php
namespace App\Action\Boleta;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Concepto;

class CalcularBoletaAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $payload = $request->getParsedBody();
        $empleados = $payload['empleados'] ?? [];

        $totalRemunerativo = 0;
        $totalNoRemunerativo = 0;

        foreach ($empleados as $emp) {
            $totalRemunerativo += floatval($emp['importe_remunerativo'] ?? 0);
            $totalNoRemunerativo += floatval($emp['importe_no_remunerativo'] ?? 0);
        }

        $conceptosDB = Concepto::where('siempre', 1)->get();
        $calculos = [];
        $totalAPagar = 0;

        foreach ($conceptosDB as $concepto) {
            $monto = 0;
            if ($concepto->porcentaje > 0) {
                // Cálculo sobre remunerativo por convención
                $monto = $totalRemunerativo * ($concepto->porcentaje / 100);
            } else if ($concepto->importe > 0) {
                $monto = $concepto->importe;
            }

            $calculos[] = [
                'concepto_id' => $concepto->id,
                'descripcion' => $concepto->descripcion,
                'porcentaje' => $concepto->porcentaje,
                'importe_calculado' => round($monto, 2)
            ];
            $totalAPagar += $monto;
        }

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => [
                'resumen_empleados' => count($empleados),
                'total_remunerativo' => $totalRemunerativo,
                'total_no_remunerativo' => $totalNoRemunerativo,
                'conceptos_detallados' => $calculos,
                'total_a_pagar' => round($totalAPagar, 2)
            ]
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
