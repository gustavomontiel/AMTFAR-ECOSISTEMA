<?php
require __DIR__ . '/vendor/autoload.php';
use PHPJasper\PHPJasper;

$input = __DIR__ . '/public/reports/boleta.jrxml';
$output = __DIR__ . '/public/reports/boleta_out_test';
$options = [
    'format' => ['pdf'],
    'params' => [
        'IdBoleta' => 8,
        'IdFarmacia' => 1
    ],
    'db_connection' => [
        'driver' => 'mysql',
        'username' => 'root',
        'host' => '127.0.0.1',
        'database' => 'amtfar',
        'port' => '3306'
    ]
];

$jasper = new PHPJasper();
try {
    $jasper->process($input, $output, $options)->execute();
    echo "SUCCESS\n";
} catch (\Exception $e) {
    echo "ERROR OCCURRED:\n" . $e->getMessage() . "\n";
}
