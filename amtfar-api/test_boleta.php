<?php
require __DIR__ . '/vendor/autoload.php';
$settings = require __DIR__ . '/config/settings.php';
$capsule = new \Illuminate\Database\Capsule\Manager;
$capsule->addConnection($settings['db']);
$capsule->setAsGlobal();
$capsule->bootEloquent();

use App\Domain\Models\Boleta;

try {
    $boletas = Boleta::where('farmacia_id', 1)
        ->orderBy('periodo_id', 'desc')
        ->orderBy('created_at', 'desc')
        ->get();
    echo "Success! Boletas found: " . count($boletas);
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
