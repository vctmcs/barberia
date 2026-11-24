# Hair +

Aplicación web para una estética de dama y caballero: corte (incluye shampoo, corte y secado), tintes y efectos de color, tratamientos especializados, alaciado japonés y express, y split ender.

Tiene dos partes:

- **Página pública**: presenta el negocio con su lista de servicios y precios (tomados directamente del sistema), galería de trabajos, sección "Acerca de mí", horario, ubicación y contacto por WhatsApp.
- **Panel del personal**: seguimiento de cada cliente con historial de servicios, fórmulas de color, fotografías de referencia, agenda por empleado y recordatorios automáticos.

El proyecto está construido en PHP 8 sin framework, con una arquitectura MVC propia (enrutador, controladores, modelos y vistas), para demostrar los fundamentos del desarrollo web del lado del servidor.

## Stack

| Capa | Tecnología |
| --- | --- |
| Backend | PHP 8 sin framework, PDO |
| Base de datos | SQLite 3 |
| Frontend | HTML, CSS y JavaScript sin librerías de interfaz |
| Calendario | FullCalendar |
| Correo | PHPMailer 6 (incluido en `lib/`) con SMTP externo |
| Hosting de destino | InfinityFree (Apache) |

## Roles

- **Administrador**: gestiona empleados, servicios, clientes, citas y consulta el dashboard completo.
- **Barbero / estilista**: consulta su agenda, atiende citas y registra el historial de cada cliente.

## Módulos

- **Clientes**: datos de contacto, preferencias, notas y fotografía de referencia.
- **Servicios**: cortes, tintes, tratamientos y barba, con precio y duración estimada.
- **Empleados**: barberos y estilistas con su horario semanal.
- **Agenda**: calendario con FullCalendar por mes, semana, día o lista, filtrado por estilista y con su horario sombreado. Al agendar se sugieren los horarios libres donde cabe el servicio completo; el sistema impide citas fuera del turno del estilista o que se empalmen con otra del estilista o del cliente. Las citas programadas se reprograman arrastrándolas y cambian de estado entre programada, atendida, cancelada y no asistió.
- **Historial del cliente**: al marcar una cita como atendida se registra la fórmula de color o los productos usados, observaciones y fotos de antes y después (en el celular abre la cámara). La ficha del cliente muestra la línea de tiempo con un comparador deslizable de antes y después, y destaca la última fórmula de color; al registrar un nuevo color se puede partir de ella. También se pueden registrar servicios que no se agendaron.
- **Recordatorios**: correo automático 24 horas antes de la cita y enlace de WhatsApp con mensaje prellenado y editable, que queda registrado en la cita.
- **Notificaciones internas**: citas que faltan hoy, citas pasadas sin cerrar, servicios atendidos sin registrar en el historial, cumpleaños de los próximos 7 días (con felicitación por WhatsApp) y clientes que no regresan desde hace varias semanas (con invitación por WhatsApp o botón para agendar). El menú muestra un contador con lo que requiere acción.
- **Dashboard**: citas del día, ingresos del mes y servicios más solicitados.
- **Recorrido guiado**: en cada sección donde se capturan o consultan datos, un recorrido paso a paso se abre la primera vez que el usuario entra. Se puede saltar y volver a ver desde el botón "Ver recorrido". Lo que cada usuario ya vio se guarda en la base de datos.

## Página pública

El contenido editable de la página pública vive en `config/sitio.php`: eslogan, WhatsApp, dirección, horario, textos de cada categoría, la sección "Acerca de mí" y la galería. Los servicios y precios no se escriben ahí: se publican automáticamente los servicios activos del módulo de servicios.

Las imágenes de la galería van en `public/img/galeria/` y la foto de "Acerca de mí" en `public/img/`. Las imágenes incluidas son ilustraciones de ejemplo para reemplazar por fotos reales.

## Estructura del proyecto

```
barberia/
├── index.php              Front controller (punto de entrada único)
├── .htaccess              Reescritura de URLs y bloqueo de carpetas internas
├── app/
│   ├── bootstrap.php      Configuración, autoload y sesión
│   ├── helpers.php        Funciones de apoyo para las vistas
│   ├── Core/              Router, Controller, View, Database, Session, Config
│   ├── Controllers/       Controladores por modulo
│   ├── Models/            Acceso a datos con PDO
│   ├── Services/          Correo, recordatorios, carga de archivos
│   └── Views/             Plantillas HTML separadas de la lógica
├── config/
│   ├── config.example.php Plantilla de configuración
│   ├── sitio.php          Contenido de la página pública
│   └── routes.php         Tabla de rutas
├── cron/                  Scripts de tareas programadas
├── lib/                   PHPMailer incluido (sin Composer)
├── database/              Esquema SQL, datos de prueba e instalador
├── public/                CSS, JavaScript, fuentes e imágenes
└── storage/               Fotografias subidas y logs (fuera del repositorio)
```

Las vistas solo contienen HTML con impresión de variables; toda la lógica vive en controladores, modelos y servicios.

## Instalación local

Requisitos: PHP 8.1 o superior con las extensiones `pdo_sqlite`, `fileinfo`, `openssl` y, de preferencia, `gd`. No se necesita servidor de base de datos ni Composer: PHPMailer viene incluido.

1. Clonar el repositorio:

   ```bash
   git clone https://github.com/vctrestrada/barberia.git
   cd barberia
   ```

2. Copiar la configuración y ajustar la URL base y el SMTP:

   ```bash
   cp config/config.example.php config/config.php
   ```

3. Crear la base de datos con datos de prueba:

   ```bash
   php database/instalar.php
   ```

   Opciones: `--vacia` crea solo el esquema y un administrador; `--forzar` borra la base existente y la vuelve a crear.

4. Levantar el servidor integrado de PHP:

   ```bash
   php -S localhost:8000 index.php
   ```

5. Abrir `http://localhost:8000` para ver la página pública. El panel está en `http://localhost:8000/login`; entrar con alguno de los usuarios de prueba (contraseña `barberia123`):

   | Correo | Rol |
   | --- | --- |
   | admin@barberia.test | Administrador |
   | tomas@barberia.test | Barbero |
   | lucia@barberia.test | Colorista |
   | ramon@barberia.test | Estilista |

## Despliegue en InfinityFree

1. Crear la base en local con `php database/instalar.php --vacia` (o con datos de prueba si es una demo).
2. Subir todo el contenido del proyecto a `htdocs/` por FTP, incluyendo `config/config.php` y `database/barberia.sqlite`.
3. Ajustar `app.url` y `app.debug = false` en `config/config.php`.
4. Dar permisos de escritura a `database/`, `storage/uploads/` y `storage/logs/`. SQLite necesita escribir en la carpeta que contiene el archivo, no solo en el archivo.

El archivo `.htaccess` bloquea el acceso web a `app/`, `config/`, `database/`, `storage/`, `lib/`, `vendor/` y `cron/`, de modo que la base de datos y la configuración no se pueden descargar desde el navegador.

### Recordatorios por correo

El recordatorio se envía automáticamente 24 horas antes de cada cita (configurable en `reminders.hours_before`) a los clientes que tienen correo. Si un envío falla, se reintenta una hora después.

**Configurar Gmail**

1. Activar la verificación en dos pasos en la cuenta de Google.
2. Crear una contraseña de aplicación en `https://myaccount.google.com/apppasswords`.
3. Llenar la sección `mail` de `config/config.php`: `host` smtp.gmail.com, `port` 587, `encryption` tls, el correo completo en `username` y `from_email`, y la contraseña de aplicación de 16 letras (sin espacios) en `password`.
4. En el panel, en Notificaciones, usar "Enviar prueba" para confirmar que funciona.

**Cómo se dispara el envío**

El plan gratuito de InfinityFree no ofrece tareas programadas y bloquea los servicios externos de cron. Por eso hay tres mecanismos:

- **Por actividad (InfinityFree)**: al terminar cualquier visita al sitio, público o panel, se revisan las citas pendientes como máximo una vez cada `reminders.check_interval` minutos. Un candado de archivo evita envíos duplicados si llegan dos visitas a la vez.
- **Línea de comandos**: `php cron/recordatorios.php`, para un servidor con cron real.
- **URL con token**: `/tareas/recordatorios?token=...` con el valor de `reminders.cron_token`, para servicios externos de cron en hostings que los permitan.

El panel de Notificaciones muestra la cola, los últimos envíos con su error si fallaron y un botón para enviar los pendientes en ese momento.

## Seguridad

- **Contraseñas** con `password_hash` (bcrypt) y rehash automático si cambia el algoritmo por defecto.
- **Inicio de sesión** con límite de intentos guardado en la base: 5 fallos por IP y correo, o 20 por IP, bloquean 15 minutos aunque se borren las cookies. El tiempo de respuesta es igual exista o no el correo, para no revelar qué cuentas existen.
- **Sesión** con cookie `HttpOnly` y `SameSite=Lax` (y `Secure` con HTTPS), identificador regenerado al entrar y al cambiar la contraseña, y cierre por inactividad configurable (`app.session_idle`).
- **CSRF**: todos los formularios y peticiones `POST` llevan token.
- **Autorización** en cada controlador: el estilista solo ve y modifica su propia agenda e historial; servicios, empleados y eliminaciones son del administrador.
- **SQL** solo con consultas preparadas de PDO; los modelos limitan las columnas que se pueden escribir.
- **XSS**: toda salida de datos en las vistas pasa por `htmlspecialchars`.
- **Fotos**: se valida el tipo real con `finfo`, se reescalan con GD (lo que elimina metadatos como la ubicación), se guardan con nombre aleatorio fuera de la carpeta pública y solo se entregan a usuarios con sesión.
- **Encabezados**: Content-Security-Policy (solo scripts propios), `X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy` y `Permissions-Policy`.
- **Servidor**: `.htaccess` bloquea carpetas internas, archivos ocultos y extensiones sensibles (`.sqlite`, `.sql`, `.log`). Los errores se registran en `storage/logs` y el visitante ve una página 500 sin detalles.
- **Contraseña de prueba**: si alguien entra con la contraseña de los datos de prueba, el panel muestra un aviso hasta que la cambie en "Mi cuenta".

## Antes de publicar

1. **Base de datos**: crear la de producción con `php database/instalar.php --vacia` (solo el administrador) o con datos de prueba si es una demo para el portafolio.
2. **Contraseña**: entrar como administrador y cambiarla en "Mi cuenta" (nombre en la barra superior).
3. **`config/config.php`**:
   - `app.url` con la dirección real y `https://`.
   - `app.debug` en `false`.
   - `reminders.cron_token`: un valor largo y aleatorio, por ejemplo el resultado de `php -r "echo bin2hex(random_bytes(24));"`. Mientras tenga el valor de ejemplo, la URL de tareas queda desactivada.
   - `mail`: opcional; sin datos reales el sistema simplemente no envía correos.
4. **`config/sitio.php`**: datos reales del negocio y fotos reales en `public/img/galeria/`.
5. **Subir a `htdocs/` por FTP**: `index.php`, `.htaccess` y las carpetas `app/`, `config/`, `cron/`, `database/` (con el archivo `.sqlite`), `lib/`, `public/` y `storage/`. No hace falta subir `.git/`, `README.md` ni `composer.json`.
6. **Permisos** de escritura en `database/` y `storage/`.
7. **HTTPS**: activar el certificado SSL gratuito desde el panel de InfinityFree.
8. **Comprobar** que estas direcciones responden 403: `/config/config.php`, `/database/barberia.sqlite`, `/storage/logs/` y `/lib/PHPMailer/PHPMailer.php`.

## Créditos

FullCalendar 6 (licencia MIT), incluido en `public/vendor/fullcalendar/`.

PHPMailer 6 (licencia LGPL 2.1), incluido en `lib/PHPMailer/`.

Tipografías Mr Dafoe y Barlow, bajo licencia SIL Open Font License 1.1, incluidas en `public/fonts/`.

## Licencia

MIT
