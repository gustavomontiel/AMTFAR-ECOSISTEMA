<?php
namespace App\Action\Empleado;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Empleado;

class ListEmpleadosAction
{
    public function __invoke(Request $request, Response $response): Response
    {
        // Extraemos los empleados de la Base de Datos con sus relaciones
        $empleados_db = Empleado::with(['persona', 'farmacia', 'categoria'])->get();
        
        // Mapeamos al formato esperado por el frontend
        $empleados = $empleados_db->map(function ($emp) {
            return [
                'id' => $emp->id,
                'nombre' => $emp->persona->nombre ?? 'Sin Nombre',
                'cuil' => $emp->persona->cuil ?? '00-00000000-0',
                'cargo' => $emp->categoria->descripcion ?? 'Sin Cargo',
                'estado' => $emp->estado_baja == 0 ? 'Activo' : 'Inactivo',
                'farmacia' => $emp->farmacia->razon_social ?? 'S/D'
            ];
        });

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => $empleados
        ]));
        
        return $response->withHeader('Content-Type', 'application/json');
    }
}
