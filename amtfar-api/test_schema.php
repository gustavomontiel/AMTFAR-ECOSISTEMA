<?php
$c = new PDO('mysql:host=127.0.0.1;dbname=amtfar', 'root', '');
$res = $c->query("SHOW COLUMNS FROM farmacias")->fetchAll(PDO::FETCH_ASSOC);
file_put_contents('schema.txt', implode(', ', array_column($res, 'Field')));
