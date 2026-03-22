<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=amtfar', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $pdo->query("SHOW COLUMNS FROM categorias");
$cols = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // gets 'Field' column
$nameCol = in_array('descripcion', $cols) ? 'descripcion' : 'nombre';

$sql = "INSERT INTO categorias (id, $nameCol) VALUES
(1, 'Cadete'),
(2, 'Aprendiz Ayudante'),
(3, 'Personal Auxiliar Int. Y Ext.'),
(4, 'Personal con Asig. Especifica'),
(5, 'Ayudante en Gestion de Farm'),
(6, 'Personal en Gestion de Farm'),
(7, 'Farmaceutico')
ON DUPLICATE KEY UPDATE $nameCol = VALUES($nameCol);";

try {
    $pdo->exec($sql);
    echo "Categorias insertadas/actualizadas con exito usando columna: $nameCol\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
