<?php
/**
 * Ejecutar SOLO por consola:
 * C:\xampp\php\php.exe scripts\crear_admin.php
 * No hay usuario/contraseña de administrador por defecto.
 */
if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('Solo disponible desde consola.');
}
require_once __DIR__ . '/../config/conexion.php';

function pedir(string $texto): string
{
    echo $texto;
    $linea = fgets(STDIN);
    return is_string($linea) ? trim($linea) : '';
}

echo "=== Crear administrador de Padel Tournament Organizer ===\n";
$nombre = pedir('Nombre completo: ');
$email = strtolower(pedir('Correo electrónico: '));
$contrasena = pedir('Contraseña nueva (mínimo 8 caracteres; visible en consola): ');
$repetir = pedir('Repetir contraseña: ');
if (mb_strlen($nombre) < 3 || mb_strlen($nombre) > 100 || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($contrasena) < 8 || strlen($contrasena) > 72 || $contrasena !== $repetir) {
    exit("Datos inválidos o contraseñas distintas. No se creó ninguna cuenta.\n");
}
try {
    $stmt = $pdo->prepare('SELECT id_usuario FROM usuarios WHERE email = :email');
    $stmt->execute(['email' => $email]);
    if ($stmt->fetchColumn()) exit("Ese correo ya está registrado. Usá otro correo para el administrador.\n");

    $stmt = $pdo->prepare('SELECT id_rol FROM roles WHERE nombre = :nombre');
    $stmt->execute(['nombre' => 'Administrador']);
    $rol = $stmt->fetchColumn();
    if (!$rol) exit("No existe el rol Administrador. Importá primero las tablas maestras del TP6.\n");

    $stmt = $pdo->prepare('INSERT INTO usuarios (id_rol, id_categoria, nombre_completo, email, password_hash)
        VALUES (:rol, NULL, :nombre, :email, :clave)');
    $stmt->execute(['rol' => (int) $rol, 'nombre' => $nombre, 'email' => $email, 'clave' => password_hash($contrasena, PASSWORD_DEFAULT)]);
    echo "Administrador creado. Ya podés entrar desde /login.php.\n";
} catch (PDOException $e) {
    error_log('Error al crear administrador: ' . $e->getMessage());
    exit("No se pudo crear el administrador. Revisá MySQL y el TP6.\n");
}
