<?php
session_start();

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit;
}

// Obtener el JWT si está configurado
$token = isset($_SESSION['jwt']) ? $_SESSION['jwt'] : null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
</head>
<body>
    <div class="container mt-5">
        <h1>Bienvenido al Dashboard</h1>
        <p>Estás autenticado. Aquí puedes acceder a tus recursos protegidos.</p>
        <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
    </div>

    <script>
        // Mostrar el token JWT en la consola
        const jwtToken = <?php echo json_encode($token); ?>;
        if (jwtToken) {
            console.log('JWT Token:', jwtToken);
        } else {
            console.warn('No se encontró un JWT Token.');
        }
    </script>
</body>
</html>
