# Barberia - Seguimiento de clientes

Aplicacion web de gestion para barberias y peluquerias. Permite dar seguimiento a cada cliente: historial de cortes y tintes, formulas de color, fotografias de antes y despues, agenda de citas por empleado y recordatorios automaticos por correo y WhatsApp.

El proyecto esta construido en PHP 8 sin framework, con una arquitectura MVC propia (enrutador, controladores, modelos y vistas), para demostrar los fundamentos del desarrollo web del lado del servidor.

## Stack

| Capa | Tecnologia |
| --- | --- |
| Backend | PHP 8 sin framework, PDO |
| Base de datos | MySQL / MariaDB |
| Frontend | HTML, CSS, JavaScript |
| Calendario | FullCalendar |
| Correo | PHPMailer con SMTP externo |
| Hosting de destino | InfinityFree (Apache) |

## Roles

- **Administrador**: gestiona empleados, servicios, clientes, citas y consulta el dashboard completo.
- **Barbero / estilista**: consulta su agenda, atiende citas y registra el historial de cada cliente.

## Modulos

- **Clientes**: datos de contacto, preferencias, notas y fotografia de referencia.
- **Servicios**: cortes, tintes, tratamientos y barba, con precio y duracion estimada.
- **Empleados**: barberos y estilistas con su horario semanal.
- **Citas**: agenda por dia y por empleado en calendario visual, con estados programada, atendida, cancelada y no asistio.
- **Historial del cliente**: servicios recibidos, formula de tinte y fotografias de antes y despues.
- **Recordatorios**: correo automatico 24 horas antes de la cita y enlace de WhatsApp con mensaje prellenado.
- **Notificaciones internas**: citas del dia, clientes que no regresan desde hace varias semanas y cumpleanos.
- **Dashboard**: citas del dia, ingresos del mes y servicios mas solicitados.

## Estructura del proyecto

```
barberia/
├── index.php              Front controller (punto de entrada unico)
├── .htaccess              Reescritura de URLs y bloqueo de carpetas internas
├── app/
│   ├── bootstrap.php      Configuracion, autoload y sesion
│   ├── helpers.php        Funciones de apoyo para las vistas
│   ├── Core/              Router, Controller, View, Database, Session, Config
│   ├── Controllers/       Controladores por modulo
│   ├── Models/            Acceso a datos con PDO
│   ├── Services/          Correo, recordatorios, carga de archivos
│   └── Views/             Plantillas HTML separadas de la logica
├── config/
│   ├── config.example.php Plantilla de configuracion
│   └── routes.php         Tabla de rutas
├── cron/                  Scripts de tareas programadas
├── database/              Esquema SQL y datos de prueba
├── public/                CSS, JavaScript e imagenes
└── storage/               Fotografias subidas y logs (fuera del repositorio)
```

Las vistas solo contienen HTML con impresion de variables; toda la logica vive en controladores, modelos y servicios.

## Instalacion local

Requisitos: PHP 8.1 o superior con la extension `pdo_mysql`, MySQL o MariaDB, Apache con `mod_rewrite` y Composer.

1. Clonar el repositorio dentro de la carpeta publica del servidor local:

   ```bash
   git clone https://github.com/<usuario>/barberia.git
   ```

2. Instalar dependencias:

   ```bash
   composer install
   ```

3. Copiar la configuracion y ajustar los datos de conexion, la URL base y el SMTP:

   ```bash
   cp config/config.example.php config/config.php
   ```

4. Crear la base de datos e importar `database/schema.sql` y, opcionalmente, `database/seed.sql`.

5. Abrir la URL configurada en `app.url`.

## Despliegue en InfinityFree

1. Ejecutar `composer install --no-dev` en local para generar la carpeta `vendor/`.
2. Subir todo el contenido del proyecto a `htdocs/` por FTP, incluyendo `vendor/` y `config/config.php` con los datos de la base de InfinityFree.
3. Importar el esquema desde phpMyAdmin.
4. Dar permisos de escritura a `storage/uploads/` y `storage/logs/`.

### Recordatorios en hosting gratuito

El plan gratuito de InfinityFree no ofrece tareas programadas ni servidor de correo propio, y bloquea las peticiones de servicios externos de cron. Por eso el sistema usa dos mecanismos:

- **Disparo por actividad**: cada vez que un usuario navega por el sistema se revisan las citas pendientes de recordatorio, como maximo una vez cada cierto numero de minutos (configurable). Las visitas normales del personal mantienen los recordatorios al dia.
- **Script de cron**: `cron/recordatorios.php` puede ejecutarse por linea de comandos en cualquier servidor con cron real.

El correo se envia mediante SMTP externo (por ejemplo Gmail con contrasena de aplicacion).

## Licencia

MIT
