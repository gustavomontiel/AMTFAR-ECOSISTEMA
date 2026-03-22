<?php
$pdo = new PDO('mysql:host=127.0.0.1;dbname=amtfar', 'root', '');
// Using longtext if JSON is not perfectly supported, but JSON is standard in MySQL 5.7.8+
$pdo->exec("ALTER TABLE boletas ADD COLUMN draft_payload LONGTEXT DEFAULT NULL");
echo "Schema Updated";
