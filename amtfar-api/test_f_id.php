<?php
$c = new PDO('mysql:host=127.0.0.1;dbname=amtfar', 'root', '');
$q = "SELECT b.id, b.periodo_id, b.fecha_boleta, b.fecha_vto, b.total_deposito, f.razon_social, f.cuit, f.direccion AS domicilio, IFNULL(l.nombre, 'Misiones') AS localidad_nombre, 'Consumidor Final / Exento' AS tipo_contribuyente FROM boletas b LEFT JOIN farmacias f ON b.farmacia_id = f.id LEFT JOIN localidades l ON f.localidad_id = l.id WHERE b.id = 8 AND b.farmacia_id = 1";
var_dump($c->query($q)->fetch(PDO::FETCH_ASSOC));
