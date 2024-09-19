<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir el autoload de Composer
require '../vendor/autoload.php'; // Ajusta esta ruta según tu estructura de carpetas

function enviarCorreo($correo, $token) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.office365.com'; // Servidor SMTP de Microsoft 365
        $mail->SMTPAuth   = true;
        $mail->Username   = 'gestordoc@gategroup.com'; // Tu dirección de correo electrónico
        $mail->Password   = 'Colombia.2024**'; // Tu contraseña de correo electrónico
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        // Destinatarios
        $mail->setFrom('gestordoc@gategroup.com', 'Gate Gourmet');
        $mail->addAddress($correo);

        // Contenido del correo
        $mail->isHTML(true);
        $mail->Charset = 'UTF-8'; // Asegura que se usan caracteres UTF-8
        $mail->Subject = 'SOLICITUD DE RESTABLECIMIENTO DE CUENTA';

        // Mensaje del correo
        $mail->Body = '
        <html>
        <head>
            <style>
                body {
                    font-family: Arial, sans-serif;
                    color: #333;
                    margin: 0;
                    padding: 0;
                }
                .container {
                    width: 100%;
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                    background-color: #f8f8f8;
                }
                .header {
                    text-align: center;
                    padding: 10px 0;
                }
                .header img {
                    max-width: 150px; /* Ajustado para hacer el logo más pequeño */
                }
                .content {
                    background-color: #fff;
                    padding: 20px;
                    border-radius: 8px;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
                }
                .button {
                    display: inline-block;
                    padding: 10px 20px;
                    margin-top: 20px;
                    background-color: #007bff;
                    color: #fff;
                    text-decoration: none;
                    border-radius: 4px;
                }
                .button:hover {
                    background-color: #0056b3;
                }
                .footer {
                    text-align: center;
                    padding: 20px 0;
                    font-size: 12px;
                    color: #666;
                }
                .footer img {
                    max-width: 10px; /* Ajustado para hacer el logo más pequeño */
                    margin-top: 5px;
                }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="header">
                    <!-- Puedes agregar un logo en el encabezado si lo deseas -->
                </div>
                <div class="content">
                    <h2>RESTABLECIMIENTO DE CONTRASENA</h2>
                    <p>Estimado usuario,</p>
                    <p>Hemos recibido una solicitud para restablecer la contrasena de su cuenta. Si no solicito este cambio, por favor ignore este correo.</p>
                    <p>Para restablecer su contrasena, haga clic en el siguiente enlace:</p>
                    <a href="http://10.24.217.100/Gategourmet/restablecer/cambiar_password.php?token=' . urlencode($token) . '" class="button">Restablecer Contrasena</a>
                    <p>Si el enlace no funciona, copielo y peguelo en la barra de direcciones de su navegador.</p>
                </div>
                <div class="footer">
                    <img src="https://upload.wikimedia.org/wikipedia/en/thumb/b/bd/Gate_gourmet_logo.svg/2560px-Gate_gourmet_logo.svg.png" alt="Gate Gourmet">
                    <p>&copy; 2024 Gate Gourmet. Todos los derechos reservados.</p>
                </div>
            </div>
        </body>
        </html>';

        // Enviar el correo
        $mail->send();
        echo "<script>alert('Se ha enviado un enlace de restablecimiento a tu correo electrónico.');</script>";
    } catch (Exception $e) {
        echo "<script>alert('No se pudo enviar el correo. Error: {$mail->ErrorInfo}');</script>";
    }
}
?>
