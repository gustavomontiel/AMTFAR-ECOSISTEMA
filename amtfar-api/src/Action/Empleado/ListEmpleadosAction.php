<?php
namespace App\Action\Empleado;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Empleado;

class ListEmpleadosAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        $user = $request->getAttribute('jwt_data');
        $id_farmacia = $user->id_farmacia ?? 1;

        // Extraemos solo los empleados de la Farmacia actual
        $empleados_db = Empleado::with(['persona', 'farmacia', 'categoria'])
                        ->where('farmacia_id', $id_farmacia)
                        ->get();
        
        // Mapeamos al formato esperado por el frontend
        $empleados = $empleados_db->map(function ($emp) {
            return [
                'id' => $emp->id,
                'nombre' => $emp->persona->nombre ?? 'Sin Nombre',
                'cuil' => $emp->persona->cuil ?? '',
                'cargo' => $emp->categoria->descripcion ?? 'Sin Cargo',
                'estado' => $emp->estado_baja == 0 ? 'Activo' : 'Inactivo',
                'farmacia' => $emp->farmacia->razon_social ?? 'S/D',
                // Raw fields for Form auto-population
                'categoria_id' => $emp->categoria_id,
                'fecha_ingreso' => $emp->fecha_ingreso,
                'fecha_egreso' => $emp->fecha_egreso,
                'importe_remunerativo' => $emp->importe_remunerativo,
                'importe_no_remunerativo' => $emp->importe_no_remunerativo,
                'estado_baja' => $emp->estado_baja
            ];
        });

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => $empleados
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
