INSERT INTO categorias (descripcion) VALUES ('Farmacéutico'), ('Atención al Cliente'), ('Cajero');
INSERT INTO farmacias (razon_social, cuit, estado_baja, nombre_fantasia) VALUES ('Farmacia San Jose', 30123456789, 0, 'Sede Central San José');
INSERT INTO personas (nombre, cuil) VALUES ('Juan Pérez', 20123456789), ('María Gómez', 27987654321), ('Roberto Sánchez', 20555555555);
INSERT INTO empleados (farmacia_id, persona_id, categoria_id, estado_baja) VALUES (1, 1, 1, 0), (1, 2, 2, 0), (1, 3, 3, 1);

-- Datos de Autenticación Demostrativa
INSERT INTO roles (descripcion) VALUES ('SuperAdmin'), ('Farmacia'), ('Auditoría');
INSERT INTO usuarios (farmacia_id, rol_id, username, password, email, nombre_completo, estado) VALUES 
(NULL, 1, 'admin', '123456', 'admin@amtfar.com.ar', 'Administrador General', 1),
(1,    2, 'farmacia_sj', '123456', 'sj@farmacias.com', 'Juan Administrador', 1);
