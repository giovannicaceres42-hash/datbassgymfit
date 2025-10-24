<?php
// --- Archivo: process_login_socio.php ---
// Este archivo está en /home/includes/

/**
 * 1. Iniciar la sesión
 * Esto es crucial. Debe ir ANTES de cualquier salida de HTML o PHP.
 * Nos permite usar las variables $_SESSION.
 */
session_start();

/**
 * 2. Incluir la conexión a la base de datos
 * Traemos el archivo 'db.php' (que está en la misma carpeta)
 * para tener disponible la variable $pdo.
 */
require_once 'db.php';

/**
 * 3. Verificar que los datos lleguen por POST
 * Por seguridad, este script solo debe procesar peticiones POST.
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Obtener los datos del formulario
    // Usamos el operador de fusión de null (??) por seguridad, 
    // para evitar errores si las variables no están definidas.
    $correo_usuario = $_POST['correo'] ?? '';
    $password_usuario = $_POST['password'] ?? '';

    // 5. Validar que los campos no estén vacíos
    if (empty($correo_usuario) || empty($password_usuario)) {
        // Si están vacíos, redirigir de vuelta al login con un error
        header("Location: ../login_socio.php?error=camposvacios");
        exit;
    }

    /**
     * 6. Lógica de autenticación y autorización
     * Usamos un bloque try/catch porque PDO está configurado
     * para lanzar excepciones en caso de error.
     */
    try {
        
        // --- ETAPA A: AUTENTICACIÓN (Verificar Identidad) ---

        // 6.1. Buscar a la PERSONA por su correo (usando una sentencia preparada)
        $sql_persona = "SELECT persona_id, password FROM personas WHERE correo = ?";
        $stmt_persona = $pdo->prepare($sql_persona);
        $stmt_persona->execute([$correo_usuario]);
        
        $persona = $stmt_persona->fetch();

        // 6.2. Verificar si la persona existe Y si la contraseña es correcta
        // password_verify() compara la contraseña del usuario con el HASH
        // que guardamos en la base de datos.
        if ($persona && password_verify($password_usuario, $persona['password'])) {
            
            // ¡Identidad verificada! La contraseña es correcta.
            
            // --- ETAPA B: AUTORIZACIÓN (Verificar Rol) ---
            
            // 6.3. Verificar si esta persona tiene un registro en la tabla SOCIOS
            $sql_socio = "SELECT persona_id FROM socios WHERE persona_id = ?";
            $stmt_socio = $pdo->prepare($sql_socio);
            $stmt_socio->execute([$persona['persona_id']]);
            
            $socio = $stmt_socio->fetch();

            if ($socio) {
                // ¡ÉXITO! ✅
                // Es una persona válida y TAMBIÉN es un socio.
                
                // 6.4. Iniciar la sesión del usuario
                session_regenerate_id(true); // Regenera el ID de sesión por seguridad
                $_SESSION['user_id'] = $socio['persona_id'];
                $_SESSION['user_role'] = 'socio'; // Guardamos su rol
                
                // 6.5. Redirigir al dashboard del socio
                header("Location: ../index.php");
                exit; // Detener la ejecución del script
                
            } else {
                // Error: Contraseña correcta, pero no tiene el rol de socio
                header("Location: ../login_socio.php?error=noautorizado");
                exit;
            }

        } else {
            // Error: Correo no encontrado o contraseña incorrecta
            header("Location: ../login_socio.php?error=credencialesinvalidas");
            exit;
        }

    } catch (PDOException $e) {
        // Error de la base de datos
        // En un sitio en producción, deberías registrar este error en un log
        // file_put_contents('db_errors.log', $e->getMessage(), FILE_APPEND);
        header("Location: ../login_socio.php?error=dberror");
        exit;
    }

} else {
    // Si alguien intenta acceder a este archivo directamente por el navegador
    // (sin usar el formulario), simplemente lo echamos.
    header("Location: ../login_socio.php");
    exit;
}
?>