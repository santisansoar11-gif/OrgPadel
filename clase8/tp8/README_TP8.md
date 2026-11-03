# TP8 — Registro, login, logout, sesión y autorización

## Proyecto
Padel Tournament Organizer, integración real con PHP, PDO, MySQL y las mismas tablas del TP6. Este TP8 **conserva todas las funciones CRUD del TP7**: crear/editar/eliminar torneos, inscripciones y consultas de contacto.

## Base MySQL: sin cambios ni reinstalación

La estructura del **TP6 está normalizada hasta 3FN**, por lo tanto la tabla `usuarios` no tiene literalmente columnas llamadas `id`, `nombre`, `password` y `rol`. Tiene estos equivalentes:

| Requisito del profesor | Campo real del proyecto |
|---|---|
| `id` | `usuarios.id_usuario` (PK AUTO_INCREMENT) |
| `nombre` | `usuarios.nombre_completo` |
| `email` | `usuarios.email` (UNIQUE) |
| `password` | `usuarios.password_hash` VARCHAR(255) |
| `rol` | `usuarios.id_rol` (FK a `roles.id_rol`); se obtiene con INNER JOIN |
| `created_at` | `usuarios.created_at` |

En la base `roles.nombre` contiene **Administrador** y **Jugador**. En `$_SESSION['rol']` se traducen, respectivamente, a `admin` y `usuario`. No dupliques el rol con una columna adicional; eso rompería la normalización anterior. Podés ejecutar `verificacion.sql` en phpMyAdmin si necesitás demostrar cómo están relacionadas las tablas (solo SELECT/SHOW).

## Instalación sobre tu TP7 existente

1. **Respaldá** la carpeta de tu sitio actual en `C:\xampp\htdocs` y exportá tu base desde phpMyAdmin si querés una copia de seguridad.
2. **No borres ni recrees tu base de datos** `padel_tournament_organizer` y **no vuelvas a importar** `tp6/schema.sql` ni `tp6/consultas.sql`. Eso podría causar duplicados.
3. Extraé el ZIP TP8 completo en `C:\xampp\htdocs\Padel_Tournament_Organizer_TP8`. Alternativamente copiá los archivos del ZIP de **cambios** encima de la carpeta TP7, conservando directorios.
4. Si utilizabas un archivo `.env`, **conservá el tuyo**. No subas `.env` a GitHub. Si no tenés `.env`, el proyecto usa por defecto MySQL en `127.0.0.1`, puerto 3306, base `padel_tournament_organizer`, usuario root sin contraseña (XAMPP típico).
5. Copiá tus imágenes (`padel.jpg` y otras fotos originales) a la raíz del proyecto si querés que se vean.
6. En XAMPP iniciá **Apache y MySQL**. Abrí `http://localhost/Padel_Tournament_Organizer_TP8/` (o la URL de la carpeta existente si actualizaste en lugar de crear una nueva).

## Pasos para la demostración ante el docente

1. **Registro:** Abrí `registro.php`; creá una cuenta **nueva** con nombre, correo único, categoría y contraseña (8 a 72 bytes). La contraseña pasa por `password_hash(..., PASSWORD_BCRYPT)` y se guarda solo el hash en MySQL. El formulario **nunca permite elegir admin**.
2. **Login:** En `login.php` ingresá con esa cuenta. PHP usa `password_verify()`, `session_regenerate_id(true)`, y guarda `$_SESSION['usuario_id']`, `$_SESSION['usuario_nombre']` y `$_SESSION['rol']`. El jugador se redirige a `mis-inscripciones.php`.
3. **Estado conservado:** Pasá a `mi-cuenta.php`, luego a `mis-inscripciones.php`; seguís autenticado en ambas. El identificador de sesión va en una cookie `HttpOnly` con `SameSite=Lax`.
4. **Guardián de rutas:** Cerrá sesión desde **Salir** (POST + CSRF). Intentá acceder manualmente a `mi-cuenta.php`, `mis-inscripciones.php` o `admin/index.php`. Debe redirigirte a `login.php?error=acceso_denegado`.
5. **Roles:** Iniciá sesión con una cuenta normal e intentá `admin/index.php`; debe responder HTTP **403 (Acceso denegado)**. Entrá con tu usuario administrador y abrí `admin/index.php`, `admin/crear.php` y `admin/usuarios.php`.
6. **Persistencia:** Mientras el administrador crea/edita un torneo, comprobá que el registro cambia en phpMyAdmin. El TP8 no borra las funcionalidades del TP7.
7. **Logout:** Volvé a pulsar Salir; `logout.php` borra la sesión del servidor **y la cookie**.

## Primer administrador

Si ya tenías uno funcionando en el TP7, **usá esa misma cuenta**; el hash existente se puede verificar con `password_verify()`.

Si no existe ningún administrador, en tu misma PC podés ir a `http://localhost/Padel_Tournament_Organizer_TP8/crear-admin-local.php` (solo localhost), o ejecutar por CMD:

```bat
cd C:\xampp\htdocs\Padel_Tournament_Organizer_TP8
C:\xampp\php\php.exe scripts\crear_admin.php
```

El asistente de navegador bloquea la creación cuando detecta que ya existe algún administrador. Es una herramienta de **instalación local**: después de usarla, **eliminá** `crear-admin-local.php` de cualquier despliegue público. No inventes contraseñas para las cuentas de ejemplo del TP6: usá una cuenta propia que hayas registrado.

## Protección implementada

- **PDO:** MySQL con excepciones y consultas preparadas. No hay SQL construido con datos introducidos por los usuarios.
- **Contraseñas:** `password_hash(..., PASSWORD_BCRYPT)` al registrar y `password_verify()` al ingresar; campo VARCHAR(255). bcrypt procesa como máximo **72 bytes** de contraseña, por eso se limita la longitud.
- **Sesiones:** cookie `HttpOnly`, `SameSite=Lax`, `use_only_cookies=1`, `use_strict_mode=1`, regeneración del ID al autenticar. `Secure` se activa con HTTPS; para `http://localhost` de XAMPP no se puede activar sin HTTPS. En un alojamiento público **configurá HTTPS**.
- **Rutas:** `includes/auth_guard.php` comprueba `isset($_SESSION['usuario_id'])`; `admin/_comun.php` además verifica el rol en MySQL antes de cualquier operación.
- **XSS:** toda salida variable va por `e()` (= `htmlspecialchars`); `js/main.js` muestra datos ingresados con `textContent` en vez de `innerHTML`.
- **CSRF:** formularios POST sensibles tienen token de sesión y `hash_equals`, incluido el logout.
- **Logout:** borra variables de `$_SESSION`, vence cookie y llama `session_destroy()`.
- **Vencimiento de sesión:** 30 minutos de inactividad o 12 horas desde el login.

> Este es un módulo didáctico para XAMPP. Para exponerlo en Internet agregá HTTPS obligatorio, limitación de intentos por IP/cuenta, control de recuperación de claves, registro de auditoría y configuración segura del servidor.

## Archivo por archivo

| Archivo | Uso |
|---|---|
| `includes/funciones.php` | Sesiones, cookies, hash helper, CSRF y roles de sesión. |
| `includes/auth_guard.php` | Guardián reutilizable para páginas privadas y admin. |
| `registro.php` | Alta de jugadores con correo único y contraseña hasheada. |
| `login.php` | Lectura por PDO, verificación, regeneración y sesión. |
| `logout.php` | Cierre seguro por POST. |
| `mi-cuenta.php` | Página privada nueva que muestra la persistencia de sesión. |
| `mis-inscripciones.php` | Segunda página privada (jugadores). |
| `admin/_comun.php` y `admin/*.php` | Panel administrativo sujeto a rol, validado en MySQL. |
| `includes/nav.php` | Menú que cambia según login/rol y botón Salir. |
| `js/main.js` | Corrige el resumen del formulario frente a XSS. |
| `tp8/verificacion.sql` | Verificación no destructiva de la tabla usuarios, rol y fechas. |

## Commits sugeridos para tu repositorio GitHub

```bash
git add includes/funciones.php includes/auth_guard.php && git commit -m "TP8: configurar sesiones seguras y guardian de rutas"
git add registro.php login.php logout.php && git commit -m "TP8: registro bcrypt y autenticacion con PDO"
git add mi-cuenta.php mis-inscripciones.php inscribirse.php cancelar-inscripcion.php admin includes/nav.php && git commit -m "TP8: proteger rutas de jugadores y administradores"
git add js/main.js tp8 && git commit -m "TP8: prevenir XSS y agregar pruebas y documentacion"
git push
```

**Importante:** si algún archivo estaba previamente modificado en tu proyecto, revisá los cambios con `git diff` antes de reemplazarlo. Nunca publiques tu `.env` ni una exportación de la base con datos reales.

## Límites de verificación

Se probó la sintaxis de PHP y JS localmente. **No se pudo probar contra tu servidor MySQL/XAMPP**, que solo está disponible desde tu equipo. La demostración en XAMPP completa esta comprobación.

**Acceso a archivos sensibles:** `.htaccess` impide navegar a `.env`, archivos SQL, las carpetas `includes/`, `config/`, `scripts/` y documentación bajo Apache/XAMPP. Esta protección no afecta a `require_once` de PHP.
