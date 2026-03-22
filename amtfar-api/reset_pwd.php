<?php

$pdo = new PDO('mysql:host=127.0.0.1;dbname=amtfar', 'root', '');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$hash = password_hash('amtfar123', PASSWORD_DEFAULT);
$pdo->exec("UPDATE usuarios SET password = '$hash' WHERE username IN ('admin', 'farmacia_demo', 'farmacia1')");

echo "Passwords reset successfully.\n";
