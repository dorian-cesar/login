<?php
require 'vendor/autoload.php'; // Asegúrate de que Composer instaló PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluir la configuración de la base de datos
require_once 'db_config.php';

header('Content-Type: application/json');

// Obtener el correo electrónico desde la solicitud
$email = isset($_POST['email']) ? trim($_POST['email']) : '';

if (empty($email)) {
    echo json_encode(['success' => false, 'message' => 'El correo electrónico es obligatorio.']);
    exit;
}

// Verificar si el correo electrónico existe en la base de datos
$sql = "SELECT id, correo FROM users WHERE correo = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    echo json_encode(['success' => false, 'message' => 'No se encontró un usuario con ese correo electrónico.']);
    exit;
}

$user = $result->fetch_assoc();
$userId = $user['id'];

// Generar un token único
$token = bin2hex(random_bytes(16));

// Guardar el token en la base de datos con una fecha de vencimiento
$expiry = date('Y-m-d H:i:s', strtotime('+1 hour')); // Válido por 1 hora
$sql = "INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE token = ?, expires_at = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("issss", $userId, $token, $expiry, $token, $expiry);
$stmt->execute();

// Enlace para restablecer contraseña
$resetLink = "http://localhost/login/reset_password.php?token=$token";

try {
    // Crear instancia de PHPMailer
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com'; // Cambia según tu servidor SMTP
    $mail->SMTPAuth = true;
    $mail->Username = 'tu-correo@gmail.com'; // Tu correo electrónico
    $mail->Password = 'tu-contraseña';       // Tu contraseña o contraseña de aplicación
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port = 587;

    // Configurar remitente y destinatario
    $mail->setFrom('tu-correo@gmail.com', 'Tu Nombre o Empresa');
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
    echo json_encode(['success' => true, 'message' => 'Correo de restablecimiento enviado correctamente.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Error al enviar el correo: {$mail->ErrorInfo}"]);
}
