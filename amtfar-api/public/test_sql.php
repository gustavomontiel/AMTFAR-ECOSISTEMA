<?php
$c = new PDO('mysql:host=127.0.0.1;dbname=amtfar', 'root', '');
$stmt = $c->query("SELECT id, total_deposito, fecha_vto, CONCAT('01251', LPAD(id, 10, '0'), DATE_FORMAT(IFNULL(fecha_vto, NOW()), '%y%m%d'), LPAD(REPLACE(CAST(total_deposito AS CHAR), '.', ''), 8, '0'), '0000000') AS CodigoBarras, CONCAT('01251', LPAD(CAST(id AS CHAR), 10, '0'), DATE_FORMAT(fecha_vto, '%y%m%d'), LPAD(REPLACE(CAST(total_deposito AS CHAR), '.', ''), 8, '0'), '0000000') AS original_codigo FROM boletas ORDER BY id DESC LIMIT 1");
print_r($stmt->fetch(PDO::FETCH_ASSOC));
