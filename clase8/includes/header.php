<?php
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/funciones.php';
$base_path = $base_path ?? '';
$titulo_final = isset($titulo_pagina) ? $titulo_pagina . ' | ' . $nombre_sitio : $nombre_sitio;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="<?= e($descripcion_pagina ?? 'Torneos, inscripciones y cursos de pádel.'); ?>">
    <title><?= e($titulo_final); ?></title>
    <link rel="stylesheet" href="<?= e($base_path); ?>css/styles.css">
</head>
<body>
<header class="site-header">
    <div class="site-header__inner">
        <?php if (is_file(__DIR__ . '/../padel.jpg')): ?>
            <img src="<?= e($base_path); ?>padel.jpg" alt="Logo de <?= e($nombre_sitio); ?>" class="site-logo" width="90" height="90" loading="lazy">
        <?php else: ?>
            <div class="brand-mark" aria-hidden="true">🏓</div>
        <?php endif; ?>
        <div>
            <h1 class="site-title"><?= e($nombre_sitio); ?></h1>
            <p class="site-tagline">Organizá torneos de pádel, descubrí categorías e inscribite a competir.</p>
        </div>
    </div>
</header>
<?php require __DIR__ . '/nav.php'; ?>
<div class="js-toolbar">
    <div class="js-toolbar__inner">
        <p class="js-datetime" id="js-datetime" aria-live="polite">Cargando fecha y hora…</p>
        <label class="theme-toggle"><span>Modo oscuro</span><input type="checkbox" id="theme-toggle" aria-label="Activar modo oscuro"></label>
    </div>
</div>
<?php if ($aviso = obtener_flash()): ?>
<div class="flash-banner <?= $aviso['tipo'] === 'error' ? 'flash-banner--error' : 'flash-banner--ok'; ?>" role="status">
    <?= e($aviso['mensaje']); ?>
</div>
<?php endif; ?>
