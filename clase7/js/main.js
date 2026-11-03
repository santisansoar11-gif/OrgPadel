/**
 * Padel Tournament Organizer — Funcionalidades JavaScript
 */

(function () {
  "use strict";

  /* ── 1. Saludo al cargar la página ── */
  function initWelcome() {
    if (document.body.dataset.welcome !== "true") return;
    alert(
      "¡Bienvenido a Padel Tournament Organizer!\n\n" +
        "Organizamos torneos de padel para todos los niveles. " +
        "Explorá nuestras canchas, cursos y eventos."
    );
  }

  /* ── 3. Fecha y hora en tiempo real ── */
  function initDateTime() {
    var el = document.getElementById("js-datetime");
    if (!el) return;

    function actualizar() {
      var ahora = new Date();
      var fecha = ahora.toLocaleDateString("es-UY", {
        weekday: "long",
        year: "numeric",
        month: "long",
        day: "numeric",
      });
      var hora = ahora.toLocaleTimeString("es-UY", {
        hour: "2-digit",
        minute: "2-digit",
        second: "2-digit",
      });
      el.textContent = fecha.charAt(0).toUpperCase() + fecha.slice(1) + " — " + hora;
    }

    actualizar();
    setInterval(actualizar, 1000);
  }

  /* ── 6. Modo oscuro ── */
  function initThemeToggle() {
    var toggle = document.getElementById("theme-toggle");
    if (!toggle) return;

    var guardado = localStorage.getItem("pto-dark-mode");
    if (guardado === "true") {
      document.body.classList.add("dark-mode");
      toggle.checked = true;
    }

    toggle.addEventListener("change", function () {
      document.body.classList.toggle("dark-mode", toggle.checked);
      localStorage.setItem("pto-dark-mode", toggle.checked ? "true" : "false");
    });
  }

  /* ── 4. Acordeón interactivo ── */
  function initAccordion() {
    var headers = document.querySelectorAll(".accordion__header");
    if (!headers.length) return;

    headers.forEach(function (header) {
      header.addEventListener("click", function () {
        var panel = header.nextElementSibling;
        var abierto = panel.classList.contains("accordion__panel--open");

        document.querySelectorAll(".accordion__panel").forEach(function (p) {
          p.classList.remove("accordion__panel--open");
        });
        document.querySelectorAll(".accordion__header").forEach(function (h) {
          h.setAttribute("aria-expanded", "false");
        });

        if (!abierto) {
          panel.classList.add("accordion__panel--open");
          header.setAttribute("aria-expanded", "true");
        }
      });
    });
  }

  /* ── 5. Galería de imágenes ── */
  function initGallery() {
    var gallery = document.querySelector(".gallery");
    if (!gallery) return;

    var imagenes = [
      { src: "Domo cancha.jpg", alt: "Cancha de padel profesional con domo e iluminación" },
      { src: "Signo cancha.jpg", alt: "Señalización e instalaciones del club de padel" },
      { src: "padel.jpg", alt: "Vista general del club Padel Tournament Organizer" },
    ];

    var indice = 0;
    var mainImg = gallery.querySelector(".gallery__main");
    var counter = gallery.querySelector(".gallery__counter");
    var thumbsContainer = gallery.querySelector(".gallery__thumbnails");
    var btnPrev = gallery.querySelector(".gallery__prev");
    var btnNext = gallery.querySelector(".gallery__next");
    var lightbox = document.getElementById("lightbox");
    var lightboxImg = document.getElementById("lightbox-img");
    var lightboxClose = document.querySelector(".lightbox__close");

    function mostrar(i) {
      indice = (i + imagenes.length) % imagenes.length;
      var img = imagenes[indice];
      mainImg.src = img.src;
      mainImg.alt = img.alt;
      counter.textContent = indice + 1 + " / " + imagenes.length;

      thumbsContainer.querySelectorAll(".gallery__thumb").forEach(function (thumb, idx) {
        thumb.classList.toggle("gallery__thumb--active", idx === indice);
      });
    }

    imagenes.forEach(function (img, i) {
      var btn = document.createElement("button");
      btn.type = "button";
      btn.className = "gallery__thumb" + (i === 0 ? " gallery__thumb--active" : "");
      btn.setAttribute("aria-label", "Ver imagen " + (i + 1));
      btn.innerHTML = '<img src="' + img.src + '" alt="" loading="lazy">';
      btn.addEventListener("click", function () {
        mostrar(i);
      });
      thumbsContainer.appendChild(btn);
    });

    btnPrev.addEventListener("click", function () {
      mostrar(indice - 1);
    });

    btnNext.addEventListener("click", function () {
      mostrar(indice + 1);
    });

    mainImg.addEventListener("click", function () {
      lightboxImg.src = imagenes[indice].src;
      lightboxImg.alt = imagenes[indice].alt;
      lightbox.hidden = false;
      document.body.classList.add("lightbox-open");
    });

    function cerrarLightbox() {
      lightbox.hidden = true;
      document.body.classList.remove("lightbox-open");
    }

    lightboxClose.addEventListener("click", cerrarLightbox);
    lightbox.addEventListener("click", function (e) {
      if (e.target === lightbox) cerrarLightbox();
    });

    document.addEventListener("keydown", function (e) {
      if (e.key === "Escape" && !lightbox.hidden) cerrarLightbox();
    });

    mostrar(0);
  }

  /* ── 2. Validación de formularios ── */
  var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

  function mostrarError(campoId, mensaje) {
    var errorEl = document.getElementById("error-" + campoId);
    var input = document.getElementById(campoId);
    if (errorEl) errorEl.textContent = mensaje;
    if (input) input.setAttribute("aria-invalid", mensaje ? "true" : "false");
    return !mensaje;
  }

  function validarCampo(input) {
    var id = input.id;
    var valor = input.value.trim();
    var tipo = input.type;
    var tag = input.tagName.toLowerCase();

    if (input.required || input.dataset.required === "true") {
      if (tag === "select" && !valor) {
        return mostrarError(id, "Debes seleccionar una opción.");
      }
      if (!valor) {
        return mostrarError(id, "Este campo no puede estar vacío.");
      }
    }

    if ((tipo === "email" || input.name === "email") && valor && !emailRegex.test(valor)) {
      return mostrarError(id, "Ingresa un correo electrónico válido (ej: nombre@dominio.com).");
    }

    if (tipo === "password" && valor && valor.length < 8) {
      return mostrarError(id, "La contraseña debe tener al menos 8 caracteres.");
    }

    return mostrarError(id, "");
  }

  function validarFormulario(form) {
    var valido = true;
    var campos = form.querySelectorAll("input, textarea, select");

    campos.forEach(function (campo) {
      if (campo.type === "radio") return;
      if (campo.type === "submit" || campo.type === "button" || campo.type === "hidden") return;
      if (!validarCampo(campo)) valido = false;
    });

    return valido;
  }

  function initFormValidation() {
    document.querySelectorAll("form[data-validate]").forEach(function (form) {
      form.querySelectorAll("input, textarea, select").forEach(function (campo) {
        if (campo.type === "submit" || campo.type === "button") return;
        campo.addEventListener("blur", function () {
          validarCampo(campo);
        });
        campo.addEventListener("input", function () {
          if (campo.getAttribute("aria-invalid") === "true") {
            validarCampo(campo);
          }
        });
      });
    });
  }

  /* ── 7. Formulario con resumen antes de enviar ── */
  function crearModalResumen() {
    if (document.getElementById("resumen-modal")) return;

    var modal = document.createElement("div");
    modal.id = "resumen-modal";
    modal.className = "resumen-modal";
    modal.hidden = true;
    modal.setAttribute("role", "dialog");
    modal.setAttribute("aria-labelledby", "resumen-titulo");
    modal.innerHTML =
      '<div class="resumen-modal__content">' +
      '<h4 id="resumen-titulo">Confirmar datos</h4>' +
      '<p>Revisa tu información antes de enviar:</p>' +
      '<ul class="resumen-lista" id="resumen-lista"></ul>' +
      '<div class="resumen-modal__actions">' +
      '<button type="button" class="btn" id="resumen-confirmar">Confirmar y enviar</button>' +
      '<button type="button" class="btn btn--secondary" id="resumen-cancelar">Editar</button>' +
      "</div></div>";

    document.body.appendChild(modal);
  }

  function obtenerEtiqueta(form, campo) {
    var label = form.querySelector('label[for="' + campo.id + '"]');
    return label ? label.textContent.replace(":", "").trim() : campo.name;
  }

  function initFormSummary() {
    crearModalResumen();

    var modal = document.getElementById("resumen-modal");
    var lista = document.getElementById("resumen-lista");
    var btnConfirmar = document.getElementById("resumen-confirmar");
    var btnCancelar = document.getElementById("resumen-cancelar");
    var formPendiente = null;

    document.querySelectorAll("form[data-resumen]").forEach(function (form) {
      form.addEventListener("submit", function (e) {
        e.preventDefault();

        if (!validarFormulario(form)) return;

        formPendiente = form;
        lista.innerHTML = "";

        form.querySelectorAll("input, textarea, select").forEach(function (campo) {
          if (campo.type === "submit" || campo.type === "button" || campo.type === "password") return;
          if (campo.type === "radio" && !campo.checked) return;

          var valor = campo.type === "radio" ? campo.value : campo.value.trim();
          if (!valor) return;

          var li = document.createElement("li");
          var strong = document.createElement("strong");
          strong.textContent = obtenerEtiqueta(form, campo) + ": ";
          li.appendChild(strong);
          li.appendChild(document.createTextNode(valor));
          lista.appendChild(li);
        });

        modal.hidden = false;
      });
    });

    btnCancelar.addEventListener("click", function () {
      modal.hidden = true;
      formPendiente = null;
    });

    btnConfirmar.addEventListener("click", function () {
      if (formPendiente) {
        modal.hidden = true;
        formPendiente.removeAttribute("data-resumen");
        formPendiente.submit();
      }
    });

    modal.addEventListener("click", function (e) {
      if (e.target === modal) {
        modal.hidden = true;
        formPendiente = null;
      }
    });
  }

  /* ── Formularios sin resumen (solo validación) ── */
  function initSimpleForms() {
    document.querySelectorAll("form[data-validate]:not([data-resumen])").forEach(function (form) {
      form.addEventListener("submit", function (e) {
        if (!validarFormulario(form)) {
          e.preventDefault();
        }
      });
    });
  }

  /* Confirmación antes de cambios destructivos */
  function initConfirmaciones() {
    document.querySelectorAll("form[data-confirm]").forEach(function (form) {
      form.addEventListener("submit", function (evento) {
        if (!window.confirm(form.dataset.confirm)) evento.preventDefault();
      });
    });
  }

  /* ── Inicialización ── */
  function init() {
    initWelcome();
    initDateTime();
    initThemeToggle();
    initAccordion();
    initGallery();
    initFormValidation();
    initFormSummary();
    initSimpleForms();
    initConfirmaciones();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
