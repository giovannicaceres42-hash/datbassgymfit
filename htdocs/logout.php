<?php
// --- Archivo: logout.php ---
// (Este archivo está en /home/htdocs/)

/**
 * 1. Iniciar el motor de sesiones.
 * Es obligatorio llamar a session_start() para poder
 * acceder a la sesión actual y destruirla.
 */
session_start();

/**
 * 2. Eliminar todas las variables de la sesión.
 * Esto borra todos los datos guardados como $_SESSION['user_id']
 * y $_SESSION['user_role'] del array.
 */
$_SESSION = array();

/**
 * 3. Destruir la sesión.
 * Esta es la función principal que elimina la sesión
 * del almacenamiento del servidor.
 */
session_destroy();

/**
 * 4. Redirigir al usuario.
 * Se envía al usuario de vuelta a la página de inicio de sesión.
 * Le añadimos un parámetro ?status=logout por si queremos
 * que la página de login muestre un mensaje de "Has cerrado sesión".
 */
header("Location: login_socio.php?status=logout");

/**
 * 5. Detener el script.
 * Es una buena práctica llamar a exit() después de una
 * redirección para asegurar que no se ejecute más código.
 */
exit;

?>