-- Datos de prueba de Hair +. Las fechas de citas se calculan respecto al
-- dia en que se ejecuta la instalacion, para que la agenda siempre tenga
-- movimiento. Contrasena de todos los usuarios: barberia123
-- Precios de ejemplo: ajustalos desde el modulo de servicios.

INSERT INTO usuarios (id, nombre, email, password_hash, rol) VALUES
    (1, 'Administrador',  'admin@barberia.test',  '$2y$10$p10BN6unMGnHdPNzrXm0POb8K.2.qQfjoyTAB1YZb.tmw3zWgg99q', 'admin'),
    (2, 'Tomás Montiel',  'tomas@barberia.test',  '$2y$10$p10BN6unMGnHdPNzrXm0POb8K.2.qQfjoyTAB1YZb.tmw3zWgg99q', 'barbero'),
    (3, 'Lucía Ferrer',   'lucia@barberia.test',  '$2y$10$p10BN6unMGnHdPNzrXm0POb8K.2.qQfjoyTAB1YZb.tmw3zWgg99q', 'barbero'),
    (4, 'Ramón Galindo',  'ramon@barberia.test',  '$2y$10$p10BN6unMGnHdPNzrXm0POb8K.2.qQfjoyTAB1YZb.tmw3zWgg99q', 'barbero');

INSERT INTO empleados (id, usuario_id, nombre, telefono, email, especialidad, color) VALUES
    (1, 2, 'Tomás Montiel', '5512345678', 'tomas@barberia.test', 'barbero',   '#ff3d9a'),
    (2, 3, 'Lucía Ferrer',  '5523456789', 'lucia@barberia.test', 'colorista', '#2de2e6'),
    (3, 4, 'Ramón Galindo', '5534567890', 'ramon@barberia.test', 'estilista', '#ff9e3d');

-- Martes a sabado 10:00-19:00, domingo 10:00-15:00
INSERT INTO horarios (empleado_id, dia_semana, hora_inicio, hora_fin) VALUES
    (1, 2, '10:00', '19:00'), (1, 3, '10:00', '19:00'), (1, 4, '10:00', '19:00'),
    (1, 5, '10:00', '19:00'), (1, 6, '10:00', '19:00'), (1, 7, '10:00', '15:00'),
    (2, 1, '11:00', '20:00'), (2, 2, '11:00', '20:00'), (2, 3, '11:00', '20:00'),
    (2, 5, '11:00', '20:00'), (2, 6, '10:00', '18:00'),
    (3, 1, '10:00', '19:00'), (3, 3, '10:00', '19:00'), (3, 4, '10:00', '19:00'),
    (3, 5, '10:00', '19:00'), (3, 6, '10:00', '19:00'), (3, 7, '10:00', '15:00');

INSERT INTO servicios (id, nombre, tipo, descripcion, precio, duracion_min) VALUES
    (1,  'Corte de dama',              'corte',       'Incluye shampoo, corte y secado.',                                280,  60),
    (2,  'Corte de caballero',         'corte',       'Incluye shampoo, corte y secado.',                                180,  40),
    (3,  'Tinte completo',             'tinte',       'Color uniforme de raíz a puntas.',                                750, 120),
    (4,  'Retoque de raíz',            'tinte',       'Color solo en el crecimiento.',                                   450,  80),
    (5,  'Balayage',                   'tinte',       'Aclarado pintado a mano para un degradado natural.',             1400, 180),
    (6,  'Mechas',                     'tinte',       'Luces con papel para iluminar el rostro.',                       1100, 150),
    (7,  'Baño de color',              'tinte',       'Matiz para refrescar o neutralizar el tono.',                     380,  45),
    (8,  'Hidratación profunda',       'tratamiento', 'Mascarilla con vapor para cabello reseco.',                       350,  45),
    (9,  'Tratamiento reconstructor',  'tratamiento', 'Repara cabello dañado por decoloración o calor.',                 480,  60),
    (10, 'Botox capilar',              'tratamiento', 'Relleno de fibra para brillo y control de frizz.',                650,  90),
    (11, 'Alaciado japonés',           'alaciado',    'Alaciado permanente: el cabello tratado queda lacio hasta que crece.', 2800, 240),
    (12, 'Alaciado express',           'alaciado',    'Reduce volumen y frizz por varias semanas.',                      950, 120),
    (13, 'Split ender',                'split_ender', 'Elimina las puntas abiertas sin perder largo.',                   320,  45);

INSERT INTO clientes (id, nombre, telefono, email, fecha_nacimiento, preferencias, notas, creado_en) VALUES
    (1,  'Manuel Ortega',     '5541112233', 'manuel.ortega@correo.test',  '1992-03-14', 'Corte corto con textura arriba.',             'Prefiere los sábados temprano.',              date('now', 'localtime', '-300 days')),
    (2,  'Elena Herrera',     '5542223344', 'elena.h@correo.test',        '1995-' || strftime('%m-%d', 'now', 'localtime', '+3 days'), 'Rubio platinado, raíz cada 6 semanas.', 'Piel sensible: prueba de alergia antes del tinte.', date('now', 'localtime', '-280 days')),
    (3,  'Jorge Salas',       '5543334455', 'jorge.salas@correo.test',    '1988-07-02', 'Corte clásico con raya al lado.',             NULL,                                          date('now', 'localtime', '-250 days')),
    (4,  'Mariana Quiroz',    '5544445566', 'mariana.q@correo.test',      '1999-11-21', 'Puntas rectas, sin capas.',                   'Trae foto de referencia.',                    date('now', 'localtime', '-200 days')),
    (5,  'Diego Ramírez',     '5545556677', 'diego.r@correo.test',        '1990-' || strftime('%m-%d', 'now', 'localtime'), 'Degradado bajo a los lados.', NULL,                    date('now', 'localtime', '-190 days')),
    (6,  'Sofía Benítez',     '5546667788', 'sofia.b@correo.test',        '1997-05-30', 'Balayage cobrizo.',                           NULL,                                          date('now', 'localtime', '-170 days')),
    (7,  'Raúl Castañeda',    '5547778899', NULL,                         '1985-09-12', 'Corte a máquina número 2.',                   'Solo contacto por WhatsApp.',                 date('now', 'localtime', '-160 days')),
    (8,  'Andrea Lugo',       '5548889900', 'andrea.lugo@correo.test',    '2001-01-08', 'Fleco cortina.',                              NULL,                                          date('now', 'localtime', '-120 days')),
    (9,  'Valeria Ochoa',     '5549990011', 'valeria.o@correo.test',      '1987-12-25', 'Alaciado japonés, retoque cada 6 meses.',     'Cabello grueso: calcular tiempo extra.',      date('now', 'localtime', '-110 days')),
    (10, 'Paola Medina',      '5540001122', 'paola.m@correo.test',        '1993-06-17', 'Ondas al secar.',                             NULL,                                          date('now', 'localtime', '-60 days'));

-- Citas pasadas (atendidas, canceladas y no asistio)
INSERT INTO citas (id, cliente_id, empleado_id, servicio_id, fecha_hora, fecha_fin, estado, precio) VALUES
    (1,  1, 1, 2,  datetime(date('now','localtime','-56 days'), '+11 hours'), datetime(date('now','localtime','-56 days'), '+11 hours', '+40 minutes'),  'atendida',   180),
    (2,  2, 2, 5,  datetime(date('now','localtime','-90 days'), '+12 hours'), datetime(date('now','localtime','-90 days'), '+12 hours', '+180 minutes'), 'atendida',  1400),
    (3,  2, 2, 4,  datetime(date('now','localtime','-45 days'), '+12 hours'), datetime(date('now','localtime','-45 days'), '+12 hours', '+80 minutes'),  'atendida',   450),
    (4,  3, 3, 2,  datetime(date('now','localtime','-70 days'), '+16 hours'), datetime(date('now','localtime','-70 days'), '+16 hours', '+40 minutes'),  'atendida',   180),
    (5,  4, 3, 1,  datetime(date('now','localtime','-20 days'), '+13 hours'), datetime(date('now','localtime','-20 days'), '+13 hours', '+60 minutes'),  'atendida',   280),
    (6,  5, 1, 2,  datetime(date('now','localtime','-15 days'), '+17 hours'), datetime(date('now','localtime','-15 days'), '+17 hours', '+40 minutes'),  'atendida',   180),
    (7,  6, 2, 3,  datetime(date('now','localtime','-12 days'), '+11 hours'), datetime(date('now','localtime','-12 days'), '+11 hours', '+120 minutes'), 'atendida',   750),
    (8,  7, 1, 2,  datetime(date('now','localtime','-100 days'),'+18 hours'), datetime(date('now','localtime','-100 days'),'+18 hours', '+40 minutes'),  'atendida',   180),
    (9,  9, 3, 11, datetime(date('now','localtime','-8 days'),  '+10 hours'), datetime(date('now','localtime','-8 days'),  '+10 hours', '+240 minutes'), 'atendida',  2800),
    (10, 8, 3, 1,  datetime(date('now','localtime','-5 days'),  '+15 hours'), datetime(date('now','localtime','-5 days'),  '+15 hours', '+60 minutes'),  'cancelada',  280),
    (11,10, 3, 8,  datetime(date('now','localtime','-3 days'),  '+12 hours'), datetime(date('now','localtime','-3 days'),  '+12 hours', '+45 minutes'),  'no_asistio', 350),
    (12, 1, 1, 2,  datetime(date('now','localtime','-2 days'),  '+11 hours'), datetime(date('now','localtime','-2 days'),  '+11 hours', '+40 minutes'),  'atendida',   180),
    (13, 4, 3, 13, datetime(date('now','localtime','-30 days'), '+14 hours'), datetime(date('now','localtime','-30 days'), '+14 hours', '+45 minutes'),  'atendida',   320),
    (14, 6, 2, 9,  datetime(date('now','localtime','-40 days'), '+16 hours'), datetime(date('now','localtime','-40 days'), '+16 hours', '+60 minutes'),  'atendida',   480);

-- Citas de hoy y proximas
INSERT INTO citas (id, cliente_id, empleado_id, servicio_id, fecha_hora, fecha_fin, estado, precio) VALUES
    (15, 3, 1, 2,  datetime(date('now','localtime'), '+12 hours'), datetime(date('now','localtime'), '+12 hours', '+40 minutes'),  'programada', 180),
    (16, 6, 2, 4,  datetime(date('now','localtime'), '+16 hours'), datetime(date('now','localtime'), '+16 hours', '+80 minutes'),  'programada', 450),
    (17, 8, 3, 13, datetime(date('now','localtime'), '+18 hours'), datetime(date('now','localtime'), '+18 hours', '+45 minutes'),  'programada', 320),
    (18, 2, 2, 4,  datetime(date('now','localtime','+1 day'), '+12 hours'), datetime(date('now','localtime','+1 day'), '+12 hours', '+80 minutes'),  'programada', 450),
    (19, 4, 3, 10, datetime(date('now','localtime','+1 day'), '+17 hours'), datetime(date('now','localtime','+1 day'), '+17 hours', '+90 minutes'),  'programada', 650),
    (20, 5, 1, 2,  datetime(date('now','localtime','+3 days'), '+11 hours'), datetime(date('now','localtime','+3 days'), '+11 hours', '+40 minutes'), 'programada', 180),
    (21,10, 3, 12, datetime(date('now','localtime','+6 days'), '+13 hours'), datetime(date('now','localtime','+6 days'), '+13 hours', '+120 minutes'), 'programada', 950);

INSERT INTO historial (cliente_id, cita_id, servicio_id, empleado_id, formula_tinte, observaciones, fecha) VALUES
    (1, 1,  2, 1, NULL, 'Quiso más corto arriba la próxima vez.', date('now','localtime','-56 days')),
    (2, 2,  5, 2, 'Decolorante 30 vol. 1:2, 45 min. Matiz 10.1 + 0.11 con 10 vol.', 'Llegó con base 6. Quedó en 10 cenizo.', date('now','localtime','-90 days')),
    (2, 3,  4, 2, 'Raíz con decolorante 20 vol. 1:2, 35 min. Matiz 10.1 con 10 vol.', 'Crecimiento de 2 cm.', date('now','localtime','-45 days')),
    (3, 4,  2, 3, NULL, NULL, date('now','localtime','-70 days')),
    (4, 5,  1, 3, NULL, 'Recto a la altura del hombro.', date('now','localtime','-20 days')),
    (5, 6,  2, 1, NULL, NULL, date('now','localtime','-15 days')),
    (6, 7,  3, 2, '7.44 + 7.4 partes iguales, 20 vol. 1:1.5, 35 min.', 'Cobrizo intenso, recomendar champú sin sulfatos.', date('now','localtime','-12 days')),
    (7, 8,  2, 1, NULL, NULL, date('now','localtime','-100 days')),
    (9, 9, 11, 3, NULL, 'Cabello grueso, 4 horas. Sin lavar en 72 horas.', date('now','localtime','-8 days')),
    (1, 12, 2, 1, NULL, 'Como lo pidió.', date('now','localtime','-2 days')),
    (4, 13,13, 3, NULL, 'Puntas abiertas en medios. Se conservó el largo.', date('now','localtime','-30 days')),
    (6, 14, 9, 2, NULL, 'Después del cobrizo, para sellar cutícula.', date('now','localtime','-40 days'));
