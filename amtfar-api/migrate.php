<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
require __DIR__ . '/config/database.php';
use Illuminate\Database\Capsule\Manager as DB;

try {
    echo "Applying migrations...\n";
    // Using ignore errors for ADD if they already exist, though we can just catch exceptions
    
    // 1. empleados table
    try {
        DB::statement("ALTER TABLE empleados CHANGE remuneracion_actual importe_remunerativo DECIMAL(12,2) DEFAULT NULL;");
        echo "empleados.remuneracion_actual renamed to importe_remunerativo.\n";
    } catch (Exception $e) { echo "empleados rename: " . $e->getMessage() . "\n"; }

    try {
        DB::statement("ALTER TABLE empleados ADD importe_no_remunerativo DECIMAL(12,2) DEFAULT NULL AFTER importe_remunerativo;");
        echo "empleados.importe_no_remunerativo added.\n";
    } catch (Exception $e) { echo "empleados add: " . $e->getMessage() . "\n"; }
    
    // 2. boletas_remuneraciones table
    try {
        DB::statement("ALTER TABLE boletas_remuneraciones CHANGE remuneracion_declarada importe_remunerativo DECIMAL(12,2) NOT NULL;");
        echo "boletas_remuneraciones.remuneracion_declarada renamed to importe_remunerativo.\n";
    } catch (Exception $e) { echo "boletas_remuneraciones rename: " . $e->getMessage() . "\n"; }

    try {
        DB::statement("ALTER TABLE boletas_remuneraciones ADD importe_no_remunerativo DECIMAL(12,2) DEFAULT NULL AFTER importe_remunerativo;");
        echo "boletas_remuneraciones.importe_no_remunerativo added.\n";
    } catch (Exception $e) { echo "boletas_remuneraciones add: " . $e->getMessage() . "\n"; }

    echo "Migration script finished.\n";
} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
