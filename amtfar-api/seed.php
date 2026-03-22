<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
require __DIR__ . '/config/database.php';
use App\Domain\Models\Farmacia;
use App\Domain\Models\Categoria;
use App\Domain\Models\Concepto;

try {
    echo "Seeding basic catalogs...\n";
    Farmacia::firstOrCreate(['id' => 1], ['razon_social' => 'Farmacia Test Demo', 'cuit' => 30123456789]);
    Categoria::firstOrCreate(['id' => 1], ['descripcion' => 'Farmacéutico Titular']);
    Categoria::firstOrCreate(['id' => 2], ['descripcion' => 'Empleado Mostrador']);
    
    // Creando conceptos para que Calcular devuelva datos
    Concepto::firstOrCreate(['id' => 1], ['descripcion' => 'Aporte Jubilatorio (11%)', 'porcentaje' => 11.00, 'siempre' => 1]);
    Concepto::firstOrCreate(['id' => 2], ['descripcion' => 'Obra Social (3%)', 'porcentaje' => 3.00, 'siempre' => 1]);
    Concepto::firstOrCreate(['id' => 3], ['descripcion' => 'Seguro Vida Fijo', 'importe' => 500.00, 'siempre' => 1]);

    echo "Seed completed successfully.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
