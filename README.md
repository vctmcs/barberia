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
| Correo | PHPMailer con SMTP externo |
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
- **Notificaciones internas**: citas del día, clientes que no regresan desde hace varias semanas y cumpleaños.
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
├── database/              Esquema SQL, datos de prueba e instalador
├── public/                CSS, JavaScript, fuentes e imágenes
└── storage/               Fotografias subidas y logs (fuera del repositorio)
```

Las vistas solo contienen HTML con impresión de variables; toda la lógica vive en controladores, modelos y servicios.

## Instalación local

Requisitos: PHP 8.1 o superior con las extensiones `pdo_sqlite` y `fileinfo`, y Composer. No se necesita servidor de base de datos.

1. Clonar el repositorio:

   ```bash
   git clone https://github.com/vctrestrada/barberia.git
   cd barberia
   ```

2. Instalar dependencias:

   ```bash
   composer install
   ```

3. Copiar la configuración y ajustar la URL base y el SMTP:

   ```bash
   cp config/config.example.php config/config.php
   ```

4. Crear la base de datos con datos de prueba:

   ```bash
   php database/instalar.php
   ```

   Opciones: `--vacia` crea solo el esquema y un administrador; `--forzar` borra la base existente y la vuelve a crear.

5. Levantar el servidor integrado de PHP:

   ```bash
   php -S localhost:8000 index.php
   ```

6. Abrir `http://localhost:8000` para ver la página pública. El panel está en `http://localhost:8000/login`; entrar con alguno de los usuarios de prueba (contraseña `barberia123`):

   | Correo | Rol |
   | --- | --- |
   | admin@barberia.test | Administrador |
   | tomas@barberia.test | Barbero |
   | lucia@barberia.test | Colorista |
   | ramon@barberia.test | Estilista |

## Despliegue en InfinityFree

1. Ejecutar `composer install --no-dev` en local para generar la carpeta `vendor/`.
2. Crear la base en local con `php database/instalar.php --vacia` (o con datos de prueba si es una demo).
3. Subir todo el contenido del proyecto a `htdocs/` por FTP, incluyendo `vendor/`, `config/config.php` y `database/barberia.sqlite`.
4. Ajustar `app.url` y `app.debug = false` en `config/config.php`.
5. Dar permisos de escritura a `database/`, `storage/uploads/` y `storage/logs/`. SQLite necesita escribir en la carpeta que contiene el archivo, no solo en el archivo.

El archivo `.htaccess` bloquea el acceso web a `app/`, `config/`, `database/`, `storage/`, `vendor/` y `cron/`, de modo que la base de datos y la configuración no se pueden descargar desde el navegador.

### Recordatorios en hosting gratuito

El plan gratuito de InfinityFree no ofrece tareas programadas ni servidor de correo propio, y bloquea las peticiones de servicios externos de cron. Por eso el sistema usa dos mecanismos:

- **Disparo por actividad**: cada vez que un usuario navega por el sistema se revisan las citas pendientes de recordatorio, como máximo una vez cada cierto número de minutos (configurable). Las visitas normales del personal mantienen los recordatorios al día.
- **Script de cron**: `cron/recordatorios.php` puede ejecutarse por línea de comandos en cualquier servidor con cron real.

El correo se envía mediante SMTP externo (por ejemplo Gmail con contraseña de aplicación).

## Créditos

FullCalendar 6 (licencia MIT), incluido en `public/vendor/fullcalendar/`.

Tipografías Mr Dafoe y Barlow, bajo licencia SIL Open Font License 1.1, incluidas en `public/fonts/`.

## Licencia

MIT
