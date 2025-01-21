<?php
// Configuración de la conexión a la base de datos
$host = 'localhost';
$db = 'login';
$user = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error al conectar a la base de datos: " . $e->getMessage());
}

// Datos del nuevo usuario
$correo = 'dgonzalez@wit.la'; // Cambia esto por el correo del usuario
$password = '123456'; // Cambia esto por la contraseña del usuario

// Verificar que el correo no exista
$stmt = $pdo->prepare("SELECT id FROM usuarios WHERE correo = :correo");
$stmt->bindParam(':correo', $correo);
$stmt->execute();

if ($stmt->rowCount() > 0) {
    die("Error: El correo ya está registrado.");
}

// Encriptar la contraseña
$passwordHash = password_hash($password, PASSWORD_BCRYPT);

// Insertar el nuevo usuario en la base de datos
$stmt = $pdo->prepare("INSERT INTO usuarios (correo, password) VALUES (:correo, :password)");
$stmt->bindParam(':correo', $correo);
$stmt->bindParam(':password', $passwordHash);

if ($stmt->execute()) {
    echo "Usuario creado con éxito.";
} else {
    echo "Error al crear el usuario.";
}
?>
