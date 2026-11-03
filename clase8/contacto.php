<?php
require_once __DIR__ . '/includes/funciones.php';
require_once __DIR__ . '/config/conexion.php';
$titulo_pagina = "Contacto";
$descripcion_pagina = "Si tienes dudas o alguna crítica aquí puedes escribirnos.";

$errores = [];
$exito = false;
$datos = [
    'nombre' => '',
    'email' => '',
    'mensaje' => ''
];

function limpiar_texto_post(string $clave): string
{
    if (!isset($_POST[$clave]) || is_array($_POST[$clave])) {
        return '';
    }

    $valor = trim((string) $_POST[$clave]);

    // Sanitización de entrada: elimina etiquetas HTML antes de procesar el dato.
    return trim(strip_tags($valor));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validar_csrf();
    // 1) Captura y sanitización de entradas.
    $datos['nombre'] = limpiar_texto_post('nombre');

    $email_raw = limpiar_texto_post('email');
    $email_sanitizado = filter_var($email_raw, FILTER_SANITIZE_EMAIL);
    $datos['email'] = is_string($email_sanitizado) ? $email_sanitizado : '';

    $datos['mensaje'] = limpiar_texto_post('mensaje');

    // 2) Validación del lado del servidor.
    if (empty($datos['nombre'])) {
        $errores[] = 'El nombre es obligatorio.';
    } elseif (mb_strlen($datos['nombre']) < 2) {
        $errores[] = 'El nombre debe tener al menos 2 caracteres.';
    }

    if (empty($datos['email'])) {
        $errores[] = 'El correo electrónico es obligatorio.';
    } elseif (!filter_var($datos['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'El correo electrónico no tiene un formato válido.';
    }

    if (empty($datos['mensaje'])) {
        $errores[] = 'El mensaje es obligatorio.';
    } elseif (mb_strlen($datos['mensaje']) < 10) {
        $errores[] = 'El mensaje debe tener al menos 10 caracteres.';
    } elseif (mb_strlen($datos['mensaje']) > 1000) {
        $errores[] = 'El mensaje no puede superar los 1000 caracteres.';
    }

    // 3) Procesamiento final si no hay errores.
    if (empty($errores)) {
        try {
            $stmt = $pdo->prepare('INSERT INTO consultas (nombre, email, mensaje, estado) VALUES (:nombre, :email, :mensaje, :estado)');
            $stmt->execute(['nombre' => $datos['nombre'], 'email' => $datos['email'], 'mensaje' => $datos['mensaje'], 'estado' => 'pendiente']);
            $exito = true;
        } catch (PDOException $error) {
            error_log('Error guardando consulta: ' . $error->getMessage());
            $errores[] = 'No se pudo guardar tu mensaje. Probá nuevamente.';
        }
    }
}

require_once 'includes/header.php';
?>

<main class="page-content">
    <h1>Contacto para torneos de padel</h1>
    <h2>Contacto con nuestra organización</h2>
    <p>¿Tienes alguna pregunta o necesitas información? Estamos aquí para ayudarte con cualquier duda sobre torneos, cursos o servicios. Si buscas reservar una cancha, inscribirte a un evento o consultar productos, nuestro equipo te responderá con la mejor orientación posible.</p>
    <p>La comunicación es una parte esencial de la experiencia deportiva. Por eso, queremos que cada consulta sea clara, rápida y útil. Ya sea que estés buscando una categoría para tu próximo torneo, una cancha para entrenar o información sobre cursos y equipamiento, te brindaremos una respuesta precisa y cordial para tomar la mejor decisión.</p>
    <p>Nuestro equipo trabaja para que cada jugador reciba información útil y oportuna. En Padel Tournament Organizer valoramos la atención personalizada, porque entendemos que cada persona tiene objetivos distintos: algunos buscan mejorar su técnica, otros competir en un torneo, y también hay quienes desean coordinar un espacio para jugar con amigos. Por eso, te invitamos a escribirnos con tranquilidad y contarnos lo que necesitas.</p>

    <h3>Información de contacto</h3>
    <p><strong>Email:</strong> santisansoar11@gmail.com</p>

    <h3>Envíanos un mensaje</h3>

    <?php if ($exito): ?>
        <div class="card form-feedback form-feedback--success" role="status">
            <p>
                <strong>Consulta registrada correctamente.</strong>
                Gracias, <?= e($datos['nombre']); ?>. Guardamos tu consulta y te podremos responder a
                <?= e($datos['email']); ?>.
            </p>
        </div>
    <?php endif; ?>

    <?php if (!empty($errores)): ?>
        <div class="card form-feedback form-feedback--error" role="alert">
            <p><strong>No se pudo enviar el formulario.</strong> Revisa los siguientes datos:</p>
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?= e($error); ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="contacto.php" method="post" data-validate novalidate>
        <?= csrf_field(); ?>
        <label for="nombre">Nombre:</label>
        <input
            type="text"
            id="nombre"
            name="nombre"
            value="<?= $exito ? '' : e($datos['nombre']); ?>"
            maxlength="80"
            required
        >
        <span class="form-error" id="error-nombre" role="alert"></span>

        <label for="email">Correo electrónico:</label>
        <input
            type="email"
            id="email"
            name="email"
            value="<?= $exito ? '' : e($datos['email']); ?>"
            maxlength="120"
            required
        >
        <span class="form-error" id="error-email" role="alert"></span>

        <label for="mensaje">Mensaje:</label>
        <textarea
            id="mensaje"
            name="mensaje"
            rows="5"
            maxlength="1000"
            required
        ><?= $exito ? '' : e($datos['mensaje']); ?></textarea>
        <span class="form-error" id="error-mensaje" role="alert"></span>

        <input type="submit" value="Enviar">
    </form>
</main>

<?php require_once 'includes/footer.php'; ?>
