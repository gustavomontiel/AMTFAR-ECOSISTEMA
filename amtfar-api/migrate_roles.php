<?php
require __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();
require __DIR__ . '/config/database.php';
use Illuminate\Database\Capsule\Manager as DB;

try {
    echo "Creating permissions tables...\n";
    // permisos
    DB::statement("
        CREATE TABLE IF NOT EXISTS permisos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(50) UNIQUE NOT NULL,
            descripcion VARCHAR(255) NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    // rol_permisos
    DB::statement("
        CREATE TABLE IF NOT EXISTS rol_permisos (
            rol_id INT NOT NULL,
            permiso_id INT NOT NULL,
            PRIMARY KEY (rol_id, permiso_id),
            FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
            FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ");

    // Seeding some basic permissions
    DB::statement("INSERT IGNORE INTO permisos (id, nombre, descripcion) VALUES 
        (1, 'ver_dashboard', 'Acceder al dashboard principal'),
        (2, 'gestionar_usuarios', 'Crear, editar o eliminar usuarios'),
        (3, 'gestionar_farmacias', 'Crear, editar o eliminar farmacias'),
        (4, 'ver_reportes', 'Visualizar y descargar reportes')
    ");

    // Assigning to roles. Assuming rol_id 1 is Admin and 2 is Farmacia or Operador.
    // For now, give Admin (1) all permissions.
    DB::statement("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) VALUES 
        (1, 1), (1, 2), (1, 3), (1, 4)
    ");
    
    // Si existe el rol Backoffice Standard (ej. id 2), le damos Dashboard y Reportes.
    DB::statement("INSERT IGNORE INTO rol_permisos (rol_id, permiso_id) VALUES 
        (2, 1), (2, 4)
    ");

    echo "Roles and Permissions migration finished.\n";
} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
