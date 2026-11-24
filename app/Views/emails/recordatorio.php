<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="color-scheme" content="dark light">
    <title>Recordatorio de tu cita</title>
</head>
<body style="margin:0;padding:0;background:#1a0b2e;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#1a0b2e;">
    <tr>
        <td align="center" style="padding:32px 16px;">
            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:520px;background:#251043;border:1px solid #ff3d9a;border-radius:12px;">
                <tr>
                    <td align="center" style="padding:36px 28px 8px;background:#251043;border-radius:12px 12px 0 0;">
                        <p style="margin:0;font-family:'Brush Script MT','Segoe Script',cursive;font-size:52px;line-height:1;color:#ff8cc4;">
                            Hair <span style="font-family:Arial,Helvetica,sans-serif;font-weight:bold;font-size:40px;color:#2de2e6;">+</span>
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:0 28px;">
                        <div style="height:4px;margin:16px 0 0;background:#ff9e3d;background-image:linear-gradient(90deg,#ffd23f,#ff9e3d,#ff3d9a);border-radius:2px;"></div>
                    </td>
                </tr>
                <tr>
                    <td style="padding:24px 28px 8px;font-family:Arial,Helvetica,sans-serif;color:#fdeff6;">
                        <p style="margin:0 0 8px;font-size:20px;font-weight:bold;">Hola, <?= e($nombre) ?>.</p>
                        <p style="margin:0;font-size:16px;line-height:1.5;color:#fdeff6;">
                            Te recordamos tu cita <?= e($cuando) ?>. Te esperamos.
                        </p>
                    </td>
                </tr>
                <tr>
                    <td style="padding:16px 28px;">
                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#1a0b2e;border-left:3px solid #2de2e6;border-radius:6px;font-family:Arial,Helvetica,sans-serif;">
                            <tr>
                                <td style="padding:16px 18px 4px;color:#b9a2d6;font-size:13px;">Fecha</td>
                            </tr>
                            <tr>
                                <td style="padding:0 18px 10px;color:#fdeff6;font-size:16px;font-weight:bold;"><?= e($fecha) ?></td>
                            </tr>
                            <tr>
                                <td style="padding:4px 18px 4px;color:#b9a2d6;font-size:13px;">Horario</td>
                            </tr>
                            <tr>
                                <td style="padding:0 18px 10px;color:#2de2e6;font-size:22px;font-weight:bold;"><?= e($horario) ?></td>
                            </tr>
                            <tr>
                                <td style="padding:4px 18px 4px;color:#b9a2d6;font-size:13px;">Servicio</td>
                            </tr>
                            <tr>
                                <td style="padding:0 18px 10px;color:#fdeff6;font-size:16px;"><?= e($servicio) ?>, con <?= e($empleado) ?></td>
                            </tr>
                            <tr>
                                <td style="padding:4px 18px 4px;color:#b9a2d6;font-size:13px;">Precio base</td>
                            </tr>
                            <tr>
                                <td style="padding:0 18px 16px;color:#ff9e3d;font-size:18px;font-weight:bold;"><?= e($precio) ?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <?php if ($direccion): ?>
                <tr>
                    <td style="padding:8px 28px;font-family:Arial,Helvetica,sans-serif;color:#fdeff6;font-size:15px;line-height:1.5;">
                        <strong>Dónde:</strong> <?= e($direccion) ?><?php if ($referencia): ?><br><span style="color:#b9a2d6;"><?= e($referencia) ?></span><?php endif; ?>
                        <?php if ($mapa): ?><br><a href="<?= e($mapa) ?>" style="color:#2de2e6;">Cómo llegar</a><?php endif; ?>
                    </td>
                </tr>
                <?php endif; ?>
                <?php if ($whatsapp): ?>
                <tr>
                    <td align="center" style="padding:20px 28px 8px;">
                        <a href="<?= e($whatsapp) ?>" style="display:inline-block;padding:14px 26px;background:#ff3d9a;color:#2a0a1e;font-family:Arial,Helvetica,sans-serif;font-size:16px;font-weight:bold;text-decoration:none;border-radius:6px;">
                            ¿Necesitas cambiarla? Escríbenos
                        </a>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td style="padding:20px 28px 32px;font-family:Arial,Helvetica,sans-serif;color:#b9a2d6;font-size:12px;line-height:1.5;text-align:center;">
                        Recibes este correo porque tienes una cita en <?= e($negocio) ?>.<br>
                        <a href="<?= e($sitioUrl) ?>" style="color:#b9a2d6;"><?= e($sitioUrl) ?></a>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
