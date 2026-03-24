<?php
namespace App\Action\Farmacia;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Farmacia;
use App\Domain\Models\Boleta;
use App\Domain\Models\Empleado;

class GetDashboardFarmaciaAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('jwt_data');
        $id_farmacia = $user->id_farmacia ?? 1; // Fallback for debugging if needed

        $farmacia = Farmacia::find($id_farmacia);
        if (!$farmacia) {
            $response->getBody()->write(json_encode(['status' => 'error', 'message' => 'Farmacia no encontrada']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // --- 1. Calcular Padrón Integrado ---
        $empleadosActivos = Empleado::with('categoria')
            ->where('farmacia_id', $id_farmacia)
            ->where('estado_baja', 0)
            ->get();
        
        $totalEmpleados = $empleadosActivos->count();
        $desgloseCategorias = [];
        
        foreach ($empleadosActivos as $emp) {
            $catName = $emp->categoria->descripcion ?? 'Sin Categoría';
            if (!isset($desgloseCategorias[$catName])) {
                $desgloseCategorias[$catName] = 0;
            }
            $desgloseCategorias[$catName]++;
        }

        $desgloseFormat = [];
        foreach ($desgloseCategorias as $cat => $cant) {
            $desgloseFormat[] = ['categoria' => $cat, 'cantidad' => $cant];
        }

        // --- 2. Calcular Morosidad y Periodos ---
        // Determinar fecha final de exigencia (o hasta el mes actual)
        // Regla: Si hay fecha de baja, esa es la última cuota exigida. 
        $fechaTope = $farmacia->fecha_baja ? new \DateTime($farmacia->fecha_baja) : new \DateTime();
        
        // Determinar fecha_alta 
        $primeraBoleta = Boleta::where('farmacia_id', $id_farmacia)->orderBy('periodo_id', 'asc')->first();
        if ($farmacia->fecha_alta) {
            $fechaAlta = new \DateTime($farmacia->fecha_alta);
        } else if ($primeraBoleta) {
            $fechaAlta = new \DateTime($primeraBoleta->periodo_id . '-01');
        } else {
            // Si nunca hizo nada y no tiene alta, asume desde mes actual
            $fechaAlta = new \DateTime(); 
        }

        // Normalizamos al día 1 para comparar meses
        $fechaAlta->modify('first day of this month');
        $fechaTope->modify('first day of this month');

        // Generar lista de periodos exigibles
        $periodosExigibles = [];
        $current = clone $fechaAlta;
        while ($current <= $fechaTope) {
            $periodosExigibles[] = $current->format('Y-m');
            $current->modify('+1 month');
        }

        // Buscar todas las boletas de esta farmacia
        $boletas = Boleta::where('farmacia_id', $id_farmacia)->get()->keyBy('periodo_id');

        $periodosResponse = [];
        $deudaTotal = 0;
        
        // Bloqueo cronológico
        $bloqueado = false; 

        foreach ($periodosExigibles as $perId) {
            $item = [
                'id' => $perId,
                'mes' => $this->mesEspanol(date('m', strtotime($perId.'-01'))),
                'anio' => (int)date('Y', strtotime($perId.'-01')),
                'bloqueado' => $bloqueado
            ];

            if ($boletas->has($perId)) {
                $boleta = $boletas->get($perId);
                // Si la boleta está pagada o estado superior, no se debe
                $isPagada = $boleta->pagada == 1 || $boleta->estado == 2 || $boleta->estado == 'PAGADA';

                if (!$isPagada) {
                    $item['estado'] = 'IMPAGA';
                    $item['monto'] = floatval($boleta->total_pagar ?? 0);
                    $item['boletaId'] = $boleta->id;
                    $deudaTotal += $item['monto'];
                    
                    // Al tener deuda, los períodos adyacentes a generar quedan bloqueados cronológicamente
                    $bloqueado = true; 
                    $periodosResponse[] = $item;
                }
            } else {
                $item['estado'] = 'SIN_BOLETA';
                $bloqueado = true; // Para no generar meses al azahar perdiendo la cronología
                $periodosResponse[] = $item;
            }
        }

        // Revertir el array para mostrar desde más reciente a más antiguo
        $periodosResponse = array_reverse($periodosResponse);

        $tieneDeuda = $deudaTotal > 0 || count(array_filter($periodosResponse, function($p) { return $p['estado'] === 'SIN_BOLETA'; })) > 0;

        $responseData = [
            'tieneDeuda' => $tieneDeuda,
            'deudaTotal' => $deudaTotal,
            'periodos' => $periodosResponse,
            'totalEmpleados' => $totalEmpleados,
            'desgloseCategorias' => $desgloseFormat
        ];

        $response->getBody()->write(json_encode([
            'status' => 'success',
            'data' => $responseData
        ]));

        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }
    
    private function mesEspanol($numMes) {
        $meses = ['01'=>'Enero', '02'=>'Febrero', '03'=>'Marzo', '04'=>'Abril', '05'=>'Mayo', '06'=>'Junio', 
                  '07'=>'Julio', '08'=>'Agosto', '09'=>'Septiembre', '10'=>'Octubre', '11'=>'Noviembre', '12'=>'Diciembre'];
        return $meses[$numMes] ?? 'Mes';
    }
}
