<?php
header('Content-Type: application/json');
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Configuración de la base de datos
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
$secretKey = 'TU_SECRETO_COMPARTIDO'; // Clave secreta usada para generar los tokens 

// Verificar el encabezado de autorización
//echo $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
$authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? '';

if (!preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
   echo json_encode(['success' => false, 'message' => 'Token no proporcionado o inválido.']);
   // echo json_encode(['success' => false, 'message' => $_SERVER]);
    
    exit;
}

$jwt = $matches[1]; // Extraer el token del encabezado

try {
    // Verificar y decodificar el JWT
    $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

    // Obtener la lista de usuarios
    $stmt = $pdo->query("SELECT id, correo FROM usuarios");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'message' => 'Lista de usuarios obtenida exitosamente.',
        'data' => $users
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Token inválido o expirado.',
        'error' => $e->getMessage()
    ]);
}
