<?php
session_start();
header('Content-Type: application/json');

// Requerir la librería JWT
require 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Configuración de la conexión a la base de datos
$host = 'localhost';
$db = 'login';
$user = 'root';
$password = '';

try {
    // Conexión a la base de datos usando PDO
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al conectar a la base de datos.']);
    exit;
}

// Configuración del JWT
$secretKey = 'TU_SECRETO_COMPARTIDO'; // Cambia esto por una clave secreta segura
$issuer = 'localhost'; // Emisor del token (puede ser tu dominio o sistema)
$audience = 'tu_aplicacion'; // Destinatario del token (por ejemplo, tu API)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($correo) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Por favor, complete todos los campos.']);
        exit;
    }

    // Verificar el usuario en la base de datos
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE correo = :correo");
    $stmt->bindParam(':correo', $correo);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (password_verify($password, $user['password'])) {
            // Generar el JWT
            $issuedAt = time();
            $expirationTime = $issuedAt + 3600; // 1 hora de duración
            $payload = [
                'iat' => $issuedAt,           // Fecha de emisión
                'exp' => $expirationTime,     // Fecha de expiración
                'iss' => $issuer,             // Emisor
                'aud' => $audience,           // Audiencia
                'data' => [
                    'id' => $user['id'],
                   // 'nombre' => $user['nombre'],
                    'correo' => $user['correo']
                ]
            ];

            $jwt = JWT::encode($payload, $secretKey, 'HS256'); // Generar el token

            // Guardar el ID del usuario en la sesión (opcional)
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['jwt']= $jwt;

            echo json_encode([
                'success' => true,
                'message' => 'Inicio de sesión exitoso.',
                'token' => $jwt
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Contraseña incorrecta.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
