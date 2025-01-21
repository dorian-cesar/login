<?php
session_start();
header('Content-Type: application/json');

// Configuración de la conexión a la base de datos
$host = 'localhost';
$db = 'login';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al conectar a la base de datos.']);
    exit;
}

// Verificar los datos enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($correo) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos.']);
        exit;
    }

    // Buscar al usuario en la base de datos
    $stmt = $pdo->prepare("SELECT id, password FROM usuarios WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        // Login exitoso
        $_SESSION['user_id'] = $user['id'];
        echo json_encode(['success' => true]);
    } else {
        // Credenciales incorrectas
        echo json_encode(['success' => false, 'message' => 'Correo o contraseña incorrectos.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>
