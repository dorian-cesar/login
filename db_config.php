<?php
// Configuración de la base de datos
$host = 'localhost';         // Dirección del servidor
$dbname = 'login'; // Nombre de la base de datos
$username = 'root';        // Usuario de la base de datos
$password = '';     // Contraseña del usuario

// Crear conexión a la base de datos
$conn = new mysqli($host, $username, $password, $dbname);

// Verificar si hay errores en la conexión
if ($conn->connect_error) {
    die("Error en la conexión con la base de datos: " . $conn->connect_error);
}

// Configurar codificación de caracteres
$conn->set_charset("utf8");

?>
