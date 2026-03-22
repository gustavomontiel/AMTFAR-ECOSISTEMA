<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=amtfar', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
try {
    $pdo->exec('ALTER TABLE boletas ADD COLUMN draft_payload LONGTEXT NULL AFTER observaciones');
    echo "Column draft_payload added.\n";
} catch (Exception $e) {
    if (strpos($e->getMessage(), 'Duplicate column name') !== false) {
        echo "Column already exists.\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
