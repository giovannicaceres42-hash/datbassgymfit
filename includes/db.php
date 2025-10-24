<?php
// 1. Configuración de la conexión
$host = 'sql300.infinityfree.com'; // O la IP/host que te dé tu hosting
$db_name = 'if0_40115703_gymfit';
$username = 'if0_40115703';
$password = 'OEzpz5oHraSU';
$charset = 'utf8mb4';

// 2. Crear la conexión (DSN)
$dsn = "mysql:host=$host;dbname=$db_name;charset=$charset";

// 3. Opciones de PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Manejar errores como excepciones
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Devolver resultados como arrays asociativos
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// 4. Intentar la conexión
try {
     $pdo = new PDO($dsn, $username, $password, $options);
} catch (\PDOException $e) {
    http_response_code(500); // Internal Server Error
     echo 'Error de conexión a la base de datos.';
     // Descomenta la siguiente línea solo si estás depurando (¡No en producción!)
     // echo 'Error: ' . $e->getMessage();
     exit;
}
?>