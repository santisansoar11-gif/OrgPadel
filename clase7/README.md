# Padel Tournament Organizer — TP7 (PHP + PDO + MySQL)

Proyecto escolar basado en la web desarrollada en los hitos anteriores y en las **7 tablas del TP6**. Implementa un panel para crear, consultar, editar y eliminar torneos, y un sitio público donde jugadores registrados pueden anotarse.

## 1. Instalación desde cero en XAMPP (Windows)

1. Iniciá **Apache** y **MySQL** desde XAMPP.
2. Extraé la carpeta `Padel_Tournament_Organizer_TP7` dentro de `C:\xampp\htdocs\`.
3. Si ya importaste `tp6/schema.sql` y `tp6/consultas.sql` en phpMyAdmin, **NO vuelvas a importar esos archivos**: la base de datos existente sirve tal como está.
4. Confirmá en `http://localhost/phpmyadmin/` que existe **`padel_tournament_organizer`** con las tablas `usuarios`, `roles`, `categorias`, `modalidades`, `torneos`, `inscripciones` y `consultas`.
5. Usá la configuración por defecto de XAMPP (`root` sin contraseña), o **copiá** `.env.example` a `.env` y editá `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER` y `DB_PASS` según tu instalación. El archivo `.env` está excluido de Git.
6. Abrí `http://localhost/Padel_Tournament_Organizer_TP7/`.

**Si aún no cargaste los roles y categorías:** importá **solo** `tp7/maestras_opcional.sql` desde phpMyAdmin. No lo necesitás si completaste todo el TP6.

## 2. Crear la primera cuenta administradora

No hay contraseñas de administrador predefinidas, ni se permite elegir el rol desde el registro web.

1. Abrí **CMD** en Windows.
2. Escribí:

```bat
cd C:\xampp\htdocs\Padel_Tournament_Organizer_TP7
C:\xampp\php\php.exe scripts\crear_admin.php
```

3. Introducí **nombre completo, correo, contraseña y repetición** cuando el programa lo pida. **La contraseña se verá mientras la escribís en CMD**, por lo que utilizá un equipo privado y evitá espectadores.
4. Visitá `http://localhost/Padel_Tournament_Organizer_TP7/login.php` e iniciá sesión como administrador. La web te enviará al panel.

> Si CMD responde que no existe PHP, verificá que XAMPP esté instalado en `C:\xampp` o sustituí la ruta por la de tu instalación. Si muestra que no existe el rol Administrador, revisá el apartado 1.

## 3. Flujo para la demostración del TP7 (delante del profesor)

1. **CREATE:** Ingresá al panel `admin/index.php` → **Crear torneo**; completá nombre, código único, categoría (por ejemplo 4ta), modalidad (por ejemplo Mixto), lugar, fecha futura, cupos y estado `Inscripciones abiertas`. Guardá.
2. **READ:** Al volver al panel se ve el torneo nuevo, su código y los cupos. Abrí `torneos-de-padel.php` en otra pestaña para verlo **en la web pública**, con sus datos leídos de MySQL. También podés confirmarlo desde phpMyAdmin → tabla `torneos` → Examinar o ejecutando `tp7/comprobacion.sql` desde la pestaña SQL.
3. **UPDATE:** En el panel, pulsá **Editar** y cambiá el lugar o cupo. Guardá. Volvé a mirar la ficha pública: el cambio aparece porque se consulta la misma tabla.
4. **DELETE:** En el panel, pulsá **Eliminar** y aceptá la confirmación. El torneo se borra de MySQL, incluidas sus inscripciones asociadas. Para hacer la demo, usá un torneo de prueba sin participantes.
5. **Extra (registro):** Cerrá la sesión, registrá un **Jugador** desde `registro.php` con la misma categoría. Entrá a un torneo programado, elegí **Inscribirme** y volvé a revisar el panel `admin/inscripciones.php` con una cuenta administradora para confirmar su inscripción.

## 4. Cómo están relacionadas las tablas

- **roles** → **usuarios**: administrador o jugador.
- **categorias** → **usuarios** y **torneos**: nivel de pádel 1ra a 8va.
- **modalidades** → **torneos**: masculino, femenino o mixto.
- **torneos**: tabla principal CRUD; su categoría/modalidad referencian tablas maestras.
- **usuarios** ↔ **inscripciones** ↔ **torneos**: relación para que cada jugador pueda inscribirse en varios torneos sin duplicarse.
- **consultas**: mensajes del formulario `contacto.php`.

`tp7/comprobacion.sql` contiene `SELECT` de control y no modifica registros.

No se necesita cambiar `schema.sql` del TP6 ni crear una base nueva. Todas estas tablas **ya existen** en el esquema que entregamos.

## 5. Archivos principales y seguridad

| Archivo | Función |
| --- | --- |
| `config/conexion.php` | Conexión `PDO` con `try/catch (PDOException)`, conexión UTF-8 y prepared statements reales. |
| `includes/funciones.php` | Sesión, roles, CSRF, validaciones y `htmlspecialchars()` mediante `e()`. |
| `admin/index.php` | READ, listado y conteo de inscripciones. |
| `admin/crear.php` | CREATE: formulario e `INSERT ... VALUES (:parametros)`. |
| `admin/editar.php` | UPDATE: modifica solo el torneo seleccionado con `WHERE id_torneo = :id`. |
| `admin/eliminar.php` | DELETE: únicamente por `POST` + CSRF, usando transacción y `WHERE`. |
| `admin/inscripciones.php` | Listado de jugadores; confirma/rechaza sus inscripciones. |
| `torneos-de-padel.php` y `torneo.php` | Listado filtrable y página pública de detalles. |
| `registro.php`, `login.php` y `logout.php` | Cuentas y sesiones (contraseñas `password_hash` y `password_verify`). |
| `inscribirse.php`, `mis-inscripciones.php` | Inscripciones con cupo y control de duplicados. |
| `contacto.php` | Guarda mensajes en `consultas` con consulta preparada. |

Todos los valores mostrados desde BD/GET/POST pasan por `e()` (`htmlspecialchars`). Las consultas que utilizan datos variables se ejecutan con `prepare()` y `execute()`. El panel requiere usuario con **rol Administrador comprobado en MySQL**; no se puede entrar desde el formulario de registro con ese rol. Los formularios de modificación incluyen **CSRF**.

## 6. Qué se tomó como referencia de Padel Organizer

En Padel Organizer, un **evento** puede agrupar torneos por categorías; los organizadores configuran competencias y gestionan sus inscripciones. Como este TP usa el esquema original del TP6, implementa **un torneo por categoría y modalidad**, administrable con nombre, código, sede, fecha, cupos, descripción y estado. Es una implementación educativa **inspirada en el funcionamiento**, no una copia del código fuente ni de la marca.

**No incluye aún** creación de eventos con múltiples torneos, armado automático de parejas, zonas, fixture, ranking, cobros ni resultados en vivo; estas funciones necesitarían ampliar el modelo de base de datos.

## 7. Problemas habituales

- **"No se pudo conectar a la base de datos"**: verificá MySQL en XAMPP, nombre correcto de BD y usuario/contraseña en `.env`.
- **"could not find driver"**: activá la extensión `pdo_mysql` de PHP en XAMPP (`php.ini`) y reiniciá Apache.
- **"No existe el rol Administrador"**: importá `tp7/maestras_opcional.sql`, no todo el TP6 de nuevo.
- **"Ese código ya existe"**: elegí un código distinto en el formulario de crear.
- **"No hay torneos"**: los torneos cancelados no aparecen en el catálogo público; creá uno con estado programado.
- **Imágenes que no aparecen**: los PHP originales utilizan `padel.jpg`, `Domo cancha.jpg`, `Signo cancha.jpg`, `Productos.jpg`, `Productos. 2.jpg` y `Signo Logo.jpg`. Como esos archivos gráficos no fueron adjuntados, copiá los tuyos originales en la raíz del proyecto. Sin `padel.jpg` el encabezado muestra un icono alternativo.
- **"Acceso restringido"**: el panel está disponible solo para el rol Administrador.

## 8. GitHub

Subí los archivos de esta carpeta al repositorio del proyecto, manteniendo la estructura `config/`, `includes/`, `admin/`, `css/` y `js/`. **No subas archivos `.env` reales**, claves ni bases de datos con datos de personas reales. Podés conservar `tp6/` como referencia y agregar `tp7/` como continuidad. La base MySQL de XAMPP no se sube automáticamente a GitHub.

## 9. Alcance de la validación

Todos los archivos PHP fueron revisados con el comprobador de sintaxis `php -l`. El entorno de preparación no dispone del controlador MySQL para conectarse a tu XAMPP real. **Para completar la validación**, ejecutá la demostración CREATE → READ → UPDATE → DELETE y comprobá los registros en phpMyAdmin desde tu PC.
