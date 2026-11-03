<?php
require_once __DIR__ . '/includes/funciones.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit('Método no permitido.'); }
validar_csrf();
$_SESSION = [];
session_regenerate_id(true);
flash('ok', 'Sesión cerrada correctamente.');
redirigir('index.php');
