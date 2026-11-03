<?php
function cargarVariablesEnv($ruta) {
    if (!file_exists($ruta)) return;
    $lineas = file($ruta, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        $linea = trim($linea);
        if ($linea === '' || strpos($linea, '#') === 0) continue;
        list($nombre, $valor) = explode('=', $linea, 2);
        $nombre = trim($nombre);
        $valor = trim($valor, " \t\n\r\0\x0B\"'");
        if (!array_key_exists($nombre, $_SERVER) && !array_key_exists($nombre, $_ENV)) {
            putenv("$nombre=$valor");
            $_ENV[$nombre] = $valor;
            $_SERVER[$nombre] = $valor;
        }
    }
}

cargarVariablesEnv(__DIR__ . '/../.env');

$nombre_sitio  = $_ENV['APP_NAME']  ?? 'Padel Tournament Organizer';
$email_soporte = $_ENV['APP_EMAIL'] ?? 'santisansoar11@gmail.com';
$app_env       = $_ENV['APP_ENV']   ?? 'local';
?>