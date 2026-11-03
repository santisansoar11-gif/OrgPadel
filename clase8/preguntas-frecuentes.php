<?php 
  $titulo_pagina = "Preguntas Frecuentes";
  $descripcion_pagina = "Aqui leerás las preguntas más frecuentes que nos hacen los clientes.";
  require_once 'includes/header.php'; 
?>
    <main class="page-content">
        <h1>Preguntas frecuentes sobre padel</h1>
        <h2>Respuestas a las dudas más comunes</h2>
        <p>Aquí encontrarás respuestas a las dudas más comunes sobre torneos, cursos, canchas y servicios. Queremos que la inscripción, la organización y la experiencia completa sean fáciles de entender para jugadores de todos los niveles.</p>
        <p>Muchas personas se interesan por el padel por la emoción del juego, pero también dudan sobre cómo empezar, qué esperar de una competición o qué tipo de equipo necesitan. Por eso esta guía responde de forma sencilla preguntas frecuentes para que el proceso sea claro, útil y sin frustraciones. Conocer estos detalles te ayuda a prepararte mejor para competir, entrenar y disfrutar del deporte con más seguridad.</p>

        <div class="accordion js-section">
            <div class="accordion__item">
                <button type="button" class="accordion__header" aria-expanded="false">¿Cuál es la edad mínima para participar?</button>
                <div class="accordion__panel">
                    <p>No hay una edad máxima para practicar padel, aunque en competencias especiales se recomienda consultar la categoría según la edad del participante. Para menores, se requiere la autorización correspondiente y la validación de los tutores responsables.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button type="button" class="accordion__header" aria-expanded="false">¿Necesito tener pareja antes de registrarme?</button>
                <div class="accordion__panel">
                    <p>No es obligatorio. Si no tienes pareja, podemos ayudarte a encontrar una durante el proceso de inscripción para que puedas participar sin complicaciones. La mayoría de los torneos se organizan en parejas, y la organización tiene en cuenta la disponibilidad y el nivel para emparejar a los jugadores de manera justa.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button type="button" class="accordion__header" aria-expanded="false">¿Cuál es la cuota de inscripción?</button>
                <div class="accordion__panel">
                    <p>Los precios varían según el torneo y la categoría. En general, la inscripción incluye el uso de la cancha, el control del evento y la organización de la competencia. Para más información detallada, puedes contactarnos directamente y te ayudaremos con el costo exacto.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button type="button" class="accordion__header" aria-expanded="false">¿Ofrecen cursos para principiantes?</button>
                <div class="accordion__panel">
                    <p>Sí, ofrecemos cursos completos de iniciación. Visita la sección de <a href="cursos-de-padel.php">Cursos de Padel</a> para más información. Estos talleres incluyen conceptos básicos de técnica, posicionamiento, empuñaduras y atención al juego en equipo.</p>
                </div>
            </div>
            <div class="accordion__item">
                <button type="button" class="accordion__header" aria-expanded="false">¿Puedo alquilar una cancha sin participar en torneos?</button>
                <div class="accordion__panel">
                    <p>Por supuesto. Contáctanos para consultar disponibilidad, horarios y tarifa de alquiler. Este servicio es ideal para entrenamientos, partidos amistosos o reuniones con amigos. También puedes reservar según el nivel de juego y la intensidad de la práctica.</p>
                </div>
            </div>
        </div>

        <p>Además, es importante aclarar que estos servicios están pensados para adaptar la experiencia a tus necesidades. Algunos jugadores buscan un espacio para mejorar su técnica, mientras que otros quieren competir o simplemente pasar un buen momento con amigos. En cualquier caso, nuestro enfoque es ofrecer una experiencia ordenada, accesible y enfocada en la práctica del padel de la mejor manera posible.</p>

        <p><a href="contacto.php" class="btn">¿Tienes más preguntas? Contáctanos</a></p>
    </main>
<?php require_once 'includes/footer.php'; ?>
