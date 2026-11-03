# Validación realizada fuera de XAMPP

**Fecha de preparación:** 27/10/2026.

- Todos los archivos `*.php` del paquete: `php -l` sin errores de sintaxis.
- `js/main.js`: `node --check` sin errores de sintaxis.
- `password_hash(..., PASSWORD_BCRYPT)` más `password_verify()`: comprobación positiva con contraseña de prueba.
- `iniciar_sesion_usuario()` en PHP CLI: almacena correctamente `usuario_id`, `usuario_nombre` y `rol` (`admin` o `usuario`).
- Sesión: verificación de vencimiento de 30 minutos de inactividad.
- `e('<script>')`: devuelve texto escapado (`&lt;script&gt;`).
- Servidor de desarrollo PHP y cookies locales (sin base): `GET /mi-cuenta.php` sin sesión redirige a `login.php?error=acceso_denegado` (HTTP 303).
- `GET /admin/index.php` sin sesión redirige a `../login.php?error=acceso_denegado` (HTTP 303), incluso sin conexión MySQL.
- Sesión de prueba con rol `usuario`: `GET /admin/index.php` responde 403 antes de consultar MySQL.
- `GET /logout.php` devuelve 405: el cierre requiere POST y token CSRF.
- `POST /logout.php` con sesión y CSRF correctos devuelve 303 a `login.php?salida=1`, expira la cookie y destruye la sesión; reutilizar el viejo ID ya no permite entrar a `/mi-cuenta.php`.

**Pendiente exclusivamente en tu computadora:** probar el guardián de roles frente a cambios en MySQL, el registro de cuentas reales de prueba, login conectado al PDO de XAMPP y la conservación de la funcionalidad CRUD del TP7. En el entorno de preparación no está instalado el controlador PDO MySQL ni existe tu servidor XAMPP.
