<?php
namespace App\Action\Boleta;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Domain\Models\Boleta;
use PHPJasper\PHPJasper;

class DescargarBoletaPdfAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        $user = $request->getAttribute('jwt_data');
        $idFarmacia = $user->id_farmacia ?? 1;
        $idBoleta = $args['id'];

        // Verificar que la boleta existe y es de esa farmacia
        $boleta = Boleta::where('id', $idBoleta)
                        ->where('farmacia_id', $idFarmacia)
                        ->first();

        if (!$boleta) {
            $response->getBody()->write(json_encode(['error' => 'Boleta no encontrada']));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
        }

        // Definimos las rutas de entrada y salida
        $reportsPath = __DIR__ . '/../../../public/reports';
        if (!is_dir($reportsPath)) {
            mkdir($reportsPath, 0777, true);
        }

        $input = $reportsPath . '/boleta.jrxml'; // Archivo master diseñado por el usuario
        $outputFile = $reportsPath . '/boleta_out_' . $idBoleta . '_' . time();

        // Si el archivo .jrxml todavía no existe, devolvemos un error instructivo
        if (!file_exists($input)) {
            $response->getBody()->write(json_encode([
                'error' => 'Falta archivo Jasper.',
                'message' => 'Por favor, crea tu diseño en Jaspersoft Studio y guárdalo en amtfar-api/public/reports/boleta.jrxml'
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        $options = [
            'format' => ['pdf'],
            'params' => [
                'IdBoleta' => $idBoleta,
                'IdFarmacia' => $idFarmacia
            ],
            'db_connection' => [
                'driver' => 'mysql',
                'username' => 'root',        // Ajustar variables de entorno en Prod
                'password' => '',
                'host' => '127.0.0.1',
                'database' => 'amtfar',
                'port' => '3306'
            ]
        ];

        try {
            $jasper = new PHPJasper();
            // Ejecutamos el motor de Java para compilar y procesar
            $jasper->process(
                $input,
                $outputFile,
                $options
            )->execute();

            $pdfPath = $outputFile . '.pdf';

            if (file_exists($pdfPath)) {
                $fileStream = file_get_contents($pdfPath);
                
                // Limpieza de archivos generados por Jasper
                unlink($pdfPath);
                
                $response->getBody()->write($fileStream);
                return $response
                    ->withHeader('Content-Type', 'application/pdf')
                    ->withHeader('Content-Disposition', 'attachment; filename="boleta_'.$idBoleta.'.pdf"')
                    ->withStatus(200);
            } else {
                throw new \Exception('No se generó el archivo PDF.');
            }

        } catch (\Exception $e) {
            $response->getBody()->write(json_encode([
                'error' => 'Error de compilación en Jasper',
                'details' => $e->getMessage()
            ]));
            return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
        }
    }
}
