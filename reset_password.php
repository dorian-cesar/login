<?php
require_once 'db_config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = $_POST['token'];
    $newPassword = $_POST['password'];

    // Validar el token
    $stmt = $conn->prepare("SELECT id FROM usuarios WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

        // Actualizar la contraseña y eliminar el token
        $stmt = $conn->prepare("UPDATE usuarios SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE reset_token = ?");
        $stmt->bind_param("ss", $hashedPassword, $token);
        $stmt->execute();

        $message = "Tu contraseña ha sido actualizada exitosamente.";
    } else {
        $message = "El enlace de recuperación es inválido o ha expirado.";
    }
} elseif (isset($_GET['token'])) {
    $token = $_GET['token'];
} else {
    die("Token no proporcionado.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2 class="text-center">Restablecer contraseña</h2>
    <?php if (!empty($message)): ?>
        <div class="alert alert-info text-center"><?= htmlspecialchars($message) ?></div>
    <?php elseif (!empty($token)): ?>
        <form method="POST" class="mt-4">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
            <div class="form-group mb-3">
                <label for="password">Nueva contraseña</label>
                <input type="password" name="password" id="password" class="form-control" placeholder="Nueva contraseña" required>
            </div>
            <button type="submit" class="btn btn-primary w-100">Actualizar contraseña</button>
        </form>
    <?php endif; ?>
</div>
</body>
</html>
