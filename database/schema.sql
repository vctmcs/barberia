-- Esquema de la base de datos (SQLite 3)
-- Las fechas se guardan como texto ISO: 'YYYY-MM-DD' y 'YYYY-MM-DD HH:MM:SS'.
-- Los montos se guardan en pesos con dos decimales.

PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS usuarios (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre         TEXT    NOT NULL,
    email          TEXT    NOT NULL UNIQUE COLLATE NOCASE,
    password_hash  TEXT    NOT NULL,
    rol            TEXT    NOT NULL CHECK (rol IN ('admin', 'barbero')),
    activo         INTEGER NOT NULL DEFAULT 1,
    ultimo_acceso  TEXT,
    creado_en      TEXT    NOT NULL DEFAULT (datetime('now', 'localtime'))
);

CREATE TABLE IF NOT EXISTS empleados (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario_id    INTEGER UNIQUE REFERENCES usuarios (id) ON DELETE SET NULL,
    nombre        TEXT    NOT NULL,
    telefono      TEXT,
    email         TEXT,
    especialidad  TEXT    NOT NULL DEFAULT 'barbero' CHECK (especialidad IN ('barbero', 'estilista', 'colorista')),
    color         TEXT    NOT NULL DEFAULT '#ff3d9a',
    activo        INTEGER NOT NULL DEFAULT 1,
    creado_en     TEXT    NOT NULL DEFAULT (datetime('now', 'localtime'))
);

-- dia_semana sigue ISO-8601: 1 = lunes ... 7 = domingo
CREATE TABLE IF NOT EXISTS horarios (
    id           INTEGER PRIMARY KEY AUTOINCREMENT,
    empleado_id  INTEGER NOT NULL REFERENCES empleados (id) ON DELETE CASCADE,
    dia_semana   INTEGER NOT NULL CHECK (dia_semana BETWEEN 1 AND 7),
    hora_inicio  TEXT    NOT NULL,
    hora_fin     TEXT    NOT NULL,
    UNIQUE (empleado_id, dia_semana),
    CHECK (hora_fin > hora_inicio)
);

CREATE TABLE IF NOT EXISTS clientes (
    id                INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre            TEXT    NOT NULL,
    telefono          TEXT,
    email             TEXT,
    fecha_nacimiento  TEXT,
    preferencias      TEXT,
    notas             TEXT,
    foto              TEXT,
    activo            INTEGER NOT NULL DEFAULT 1,
    creado_en         TEXT    NOT NULL DEFAULT (datetime('now', 'localtime'))
);

CREATE TABLE IF NOT EXISTS servicios (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre        TEXT    NOT NULL,
    tipo          TEXT    NOT NULL CHECK (tipo IN ('corte', 'tinte', 'tratamiento', 'alaciado', 'split_ender', 'otro')),
    descripcion   TEXT,
    precio        REAL    NOT NULL CHECK (precio >= 0),
    duracion_min  INTEGER NOT NULL CHECK (duracion_min > 0),
    activo        INTEGER NOT NULL DEFAULT 1,
    creado_en     TEXT    NOT NULL DEFAULT (datetime('now', 'localtime'))
);

CREATE TABLE IF NOT EXISTS citas (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id      INTEGER NOT NULL REFERENCES clientes (id) ON DELETE CASCADE,
    empleado_id     INTEGER NOT NULL REFERENCES empleados (id) ON DELETE RESTRICT,
    servicio_id     INTEGER NOT NULL REFERENCES servicios (id) ON DELETE RESTRICT,
    fecha_hora      TEXT    NOT NULL,
    fecha_fin       TEXT    NOT NULL,
    estado          TEXT    NOT NULL DEFAULT 'programada'
                            CHECK (estado IN ('programada', 'atendida', 'cancelada', 'no_asistio')),
    precio          REAL    NOT NULL DEFAULT 0,
    notas           TEXT,
    creado_en       TEXT    NOT NULL DEFAULT (datetime('now', 'localtime')),
    actualizado_en  TEXT
);

CREATE TABLE IF NOT EXISTS historial (
    id             INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id     INTEGER NOT NULL REFERENCES clientes (id) ON DELETE CASCADE,
    cita_id        INTEGER UNIQUE REFERENCES citas (id) ON DELETE SET NULL,
    servicio_id    INTEGER NOT NULL REFERENCES servicios (id) ON DELETE RESTRICT,
    empleado_id    INTEGER REFERENCES empleados (id) ON DELETE SET NULL,
    formula_tinte  TEXT,
    observaciones  TEXT,
    foto_antes     TEXT,
    foto_despues   TEXT,
    fecha          TEXT    NOT NULL,
    creado_en      TEXT    NOT NULL DEFAULT (datetime('now', 'localtime'))
);

CREATE TABLE IF NOT EXISTS recordatorios (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    cita_id     INTEGER NOT NULL REFERENCES citas (id) ON DELETE CASCADE,
    canal       TEXT    NOT NULL CHECK (canal IN ('correo', 'whatsapp')),
    enviado_en  TEXT,
    estado      TEXT    NOT NULL DEFAULT 'pendiente' CHECK (estado IN ('pendiente', 'enviado', 'fallido')),
    detalle     TEXT,
    UNIQUE (cita_id, canal)
);

-- Recorridos guiados que cada usuario ya vio o salto
CREATE TABLE IF NOT EXISTS recorridos_vistos (
    usuario_id  INTEGER NOT NULL REFERENCES usuarios (id) ON DELETE CASCADE,
    clave       TEXT    NOT NULL,
    visto_en    TEXT    NOT NULL,
    PRIMARY KEY (usuario_id, clave)
);

-- Intentos fallidos de inicio de sesion (limite por IP y correo)
CREATE TABLE IF NOT EXISTS login_intentos (
    id         INTEGER PRIMARY KEY AUTOINCREMENT,
    ip         TEXT NOT NULL,
    email      TEXT NOT NULL,
    creado_en  TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS idx_login_ip ON login_intentos (ip, creado_en);

CREATE INDEX IF NOT EXISTS idx_citas_fecha       ON citas (fecha_hora);
CREATE INDEX IF NOT EXISTS idx_citas_empleado    ON citas (empleado_id, fecha_hora);
CREATE INDEX IF NOT EXISTS idx_citas_cliente     ON citas (cliente_id);
CREATE INDEX IF NOT EXISTS idx_citas_estado      ON citas (estado);
CREATE INDEX IF NOT EXISTS idx_historial_cliente ON historial (cliente_id, fecha);
CREATE INDEX IF NOT EXISTS idx_clientes_nombre   ON clientes (nombre);

-- Version del esquema. Database.php vuelve a aplicar este archivo
-- (todo es IF NOT EXISTS) cuando la base tiene una version menor.
PRAGMA user_version = 3;
