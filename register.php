<?php
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

    // Verificar si el correo ya existe
    $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => false, 'message' => 'El correo ya está registrado.']);
        exit;
    }

    // Encriptar la contraseña
    $passwordHash = password_hash($password, PASSWORD_BCRYPT);

    // Insertar el nuevo usuario
    $stmt = $pdo->prepare("INSERT INTO usuarios (correo, password) VALUES (:correo, :password)");
    $stmt->bindParam(':correo', $correo);
    $stmt->bindParam(':password', $passwordHash);

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al registrar el usuario.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>
