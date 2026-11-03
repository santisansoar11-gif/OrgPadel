<?php 
  $titulo_pagina = "Canchas de Padel";
  $descripcion_pagina = "Aquí puedes ver nuestras sucursales o canchas y donde estan ubicadas.";
  require_once 'includes/header.php'; 
?>
    <main class="page-content">
        <h1>Canchas de padel profesionales</h1>
        <h2>Nuestras canchas de padel</h2>
        <p>Contamos con instalaciones de clase mundial para que disfrutes del mejor padel. Nuestras canchas están equipadas con vidrio de seguridad, iluminación LED profesional y mantenimiento diario para garantizar excelentes condiciones de juego, comodidad y seguridad durante cada punto.</p>
        <p>Cada espacio está pensado para facilitar la práctica recreativa y competitiva. La superficie, la calidad de la estructura y la distribución del campo hacen que cada partido sea más preciso, más cómodo y más emocionante. Además, el ambiente del club está diseñado para que jugadores de todos los niveles puedan entrenar, competir y disfrutar del deporte sin complicaciones.</p>
        <p>La infraestructura de nuestras canchas de padel combina seguridad, rendimiento y comodidad. Esto permite que cada partido se sienta más fluido y profesional, ya sea para una práctica casual con amigos o para una competencia más exigente. El mantenimiento constante ayuda a preservar la superficie, la iluminación y la estructura, cuidando así la experiencia de cada jugador y evitando distracciones durante el juego.</p>

        <section class="gallery js-section" aria-label="Galería de canchas">
            <h3>Galería de nuestras canchas</h3>
            <div class="gallery__viewer">
                <img src="Domo cancha.jpg" alt="Cancha de padel profesional" class="gallery__main" width="800" height="420">
            </div>
            <div class="gallery__controls">
                <button type="button" class="btn gallery__prev" aria-label="Imagen anterior">← Anterior</button>
                <span class="gallery__counter">1 / 3</span>
                <button type="button" class="btn gallery__next" aria-label="Siguiente imagen">Siguiente →</button>
            </div>
            <div class="gallery__thumbnails" aria-label="Miniaturas de la galería"></div>
        </section>

        <div class="lightbox" id="lightbox" hidden role="dialog" aria-label="Imagen ampliada">
            <button type="button" class="lightbox__close" aria-label="Cerrar imagen ampliada">&times;</button>
            <img src="" alt="" id="lightbox-img">
        </div>

        <h3>Características de nuestras canchas</h3>
        <ul>
            <li>Piso profesional antideslizante</li>
            <li>Vidrio de seguridad templado</li>
            <li>Iluminación LED de última generación</li>
            <li>Malla metálica de primera calidad</li>
            <li>Acceso a vestuarios y duchas</li>
            <li>Área de descanso con bebidas</li>
            <li>Estacionamiento disponible</li>
        </ul>

        <h3>Reserva tu cancha</h3>
        <p>Puedes reservar nuestras canchas de forma flexible según tus horarios y necesidades. Ya sea para entrenar, jugar con amigos o competir en un torneo, nuestro equipo te ayuda a encontrar la mejor opción para disfrutar del padel con total comodidad y organización. Además, el servicio está pensado para colaborar con jugadores de todos los niveles, ofreciendo un espacio para mejorar la técnica y disfrutar de cada partido en un ambiente profesional.</p>
    </main>

<?php require_once 'includes/footer.php'; ?>
