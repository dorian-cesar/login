<?php
session_start();
require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$mail = new PHPMailer(true);

require_once 'db_config.php'; // Conexión a la base de datos

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    if (empty($email)) {
        $message = "Por favor, ingresa tu correo electrónico.";
    } else {
        $stmt = $conn->prepare("SELECT id FROM usuarios WHERE correo = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Generar un token único
            $token = bin2hex(random_bytes(16));
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Guardar el token en la base de datos
            $stmt = $conn->prepare("UPDATE usuarios SET reset_token = ?, reset_token_expiry = ? WHERE correo = ?");
            $stmt->bind_param("sss", $token, $expiry, $email);
            $stmt->execute();

            // Enviar un correo electrónico con el enlace de recuperación
            // Enlace para restablecer contraseña
            $resetLink = "http://localhost/login/reset_password.php?token=$token";

            try {
                // Crear instancia de PHPMailer
                $mail = new PHPMailer(true);
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com'; // Cambia según tu servidor SMTP
                $mail->SMTPAuth = true;
                $mail->Username = 'mailer.wit@gmail.com'; // Tu correo electrónico
                $mail->Password = 'rqcsywcmsuvhzyqt';       // Tu contraseña o contraseña de aplicación
                $mail->SMTPSecure ='tls';
                $mail->Port = 587;

                // Configurar remitente y destinatario
                $mail->setFrom('soporte@gmail.com', 'Soporte');
                $mail->addAddress($email); // Correo del destinatario

                // Contenido del correo
                $mail->isHTML(true);
                $mail->Subject = 'Restablecimiento de contraseña';
                $mail->Body = "<p>Hola,</p>
                   <p>Hemos recibido una solicitud para restablecer tu contraseña. Haz clic en el enlace de abajo para continuar:</p>
                   <p><a href='$resetLink'>$resetLink</a></p>
                   <p>Este enlace es válido por 1 hora.</p>
                   <p>Si no solicitaste este cambio, ignora este correo.</p>";

                $mail->send();
                 $message = 'Correo de restablecimiento enviado correctamente.';
            } catch (Exception $e) {
                $message ="Error al enviar el correo: {$mail->ErrorInfo}";
            }
        } else {
            $message = "No se encontró ninguna cuenta con ese correo.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Olvidé mi contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
    <div class="container mt-5" style="width: 400px;">
        <h2 class="text-center">Recuperar contraseña</h2>
        <form method="POST" class="mt-4">
            <div class="form-group mb-3">
                <label for="email">Correo electrónico</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Ingresa tu correo" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Enviar enlace de recuperación</button>
            <p class="mt-3 text-center">
                <a href="index.html" class="text-decoration-none">Volver al inicio de sesión</a>
            </p>
            <?php if (!empty($message)): ?>
                <div class="alert alert-info mt-3">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>
        </form>
    </div>
</body>

</html>