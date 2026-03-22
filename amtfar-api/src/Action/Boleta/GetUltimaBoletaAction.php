<?php
namespace App\Action\Boleta;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Boleta;
use App\Domain\Models\Empleado;

class GetUltimaBoletaAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('jwt_data');
        $id_farmacia = $user->id_farmacia ?? 1;

        // Buscar última boleta
        $ultimaBoleta = Boleta::with(['remuneraciones.empleado.persona', 'remuneraciones.categoria'])
            ->where('farmacia_id', $id_farmacia)
            ->orderBy('id', 'desc')
            ->first();

        if ($ultimaBoleta) {
            // Mapear remuneraciones a formato de "empleados editables"
            $empleados = $ultimaBoleta->remuneraciones->map(function ($rem) {
                return [
                    'boleta_remuneracion_id' => $rem->id,
                    'empleado_id' => $rem->empleado_id,
                    'persona_id' => $rem->empleado->persona->id ?? null,
                    'cuil' => $rem->empleado->persona->cuil ?? '',
                    'nombre' => $rem->empleado->persona->nombre ?? '',
                    'categoria_id' => $rem->categoria_id,
                    'categoria_desc' => $rem->categoria->descripcion ?? '',
                    'fecha_ingreso' => $rem->fecha_ingreso_historica ?: $rem->empleado->fecha_ingreso,
                    'fecha_egreso' => $rem->empleado->fecha_egreso,
                    'importe_remunerativo' => $rem->importe_remunerativo,
                    'importe_no_remunerativo' => $rem->importe_no_remunerativo
                ];
            });
        } else {
            // Si no hay boleta, traer empleados activos actuales
            $empleadosActivos = Empleado::with(['persona', 'categoria'])
                ->where('farmacia_id', $id_farmacia)
                ->where('estado_baja', 0)
                ->get();
            
            $empleados = $empleadosActivos->map(function ($emp) {
                return [
                    'boleta_remuneracion_id' => null,
                    'empleado_id' => $emp->id,
                    'persona_id' => $emp->persona->id ?? null,
                    'cuil' => $emp->persona->cuil ?? '',
                    'nombre' => $emp->persona->nombre ?? '',
                    'categoria_id' => $emp->categoria_id,
                    'categoria_desc' => $emp->categoria->descripcion ?? '',
                    'fecha_ingreso' => $emp->fecha_ingreso,
                    'fecha_egreso' => $emp->fecha_egreso,
                    'importe_remunerativo' => $emp->importe_remunerativo,
                    'importe_no_remunerativo' => $emp->importe_no_remunerativo
                ];
            });
        }

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => [
                'ultima_boleta_id' => $ultimaBoleta->id ?? null,
                'empleados' => $empleados
            ]
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
}
