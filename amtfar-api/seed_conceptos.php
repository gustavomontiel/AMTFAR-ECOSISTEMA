<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
require __DIR__ . '/config/database.php';
use App\Domain\Models\Concepto;

try {
    echo "Borrando conceptos de prueba y cargando los reales...\n";
    // Since concept table doesn't have foreign key dependencies from Boleta yet (because we haven't successfully created one that uses them), truncation is safe. If error, we'll delete via Eloquent.
    Concepto::query()->delete();

    // Insertando conceptos reales
    Concepto::create(['id' => 1, 'descripcion' => 'Cuota Sindical', 'porcentaje' => 2.00, 'siempre' => 1]);
    Concepto::create(['id' => 7, 'descripcion' => 'ART. 46', 'porcentaje' => 8.00, 'siempre' => 1]);
    Concepto::create(['id' => 6, 'descripcion' => 'Acta Acuerdo', 'importe' => 75.00, 'siempre' => 1]);

    echo "Conceptos corregidos exitosamente.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
