<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=amtfar', 'root', '');
$pdo->exec("UPDATE empleados SET fecha_ingreso = '2023-01-01' WHERE id IN (1, 2)");
echo "OK";
