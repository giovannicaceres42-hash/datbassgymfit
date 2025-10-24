<?php
// --- Archivo: process_login_instructor.php ---
// Este archivo está en /home/includes/

/**
 * 1. Iniciar la sesión
 */
session_start();

/**
 * 2. Incluir la conexión a la base de datos
 */
require_once 'db.php';

/**
 * 3. Verificar que los datos lleguen por POST
 */
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 4. Obtener los datos del formulario
    $correo_usuario = $_POST['correo'] ?? '';
    $password_usuario = $_POST['password'] ?? '';

    // 5. Validar que los campos no estén vacíos
    if (empty($correo_usuario) || empty($password_usuario)) {
        // Redirigir de vuelta al login de INSTRUCTOR
        header("Location: ../login_instructor.php?error=camposvacios");
        exit;
    }

    /**
     * 6. Lógica de autenticación y autorización
     */
    try {
        
        // --- ETAPA A: AUTENTICACIÓN (Idéntica a la de socios) ---

        // 6.1. Buscar a la PERSONA por su correo
        $sql_persona = "SELECT persona_id, password FROM personas WHERE correo = ?";
        $stmt_persona = $pdo->prepare($sql_persona);
        $stmt_persona->execute([$correo_usuario]);
        
        $persona = $stmt_persona->fetch();

        // 6.2. Verificar si la persona existe Y si la contraseña es correcta
        if ($persona && password_verify($password_usuario, $persona['password'])) {
            
            // ¡Identidad verificada!
            
            // --- ETAPA B: AUTORIZACIÓN (Aquí está la única diferencia) --- 🔑
            
            // 6.3. Verificar si esta persona tiene un registro en la tabla INSTRUCTORES
            $sql_instructor = "SELECT persona_id FROM instructores WHERE persona_id = ?";
            $stmt_instructor = $pdo->prepare($sql_instructor);
            $stmt_instructor->execute([$persona['persona_id']]);
            
            $instructor = $stmt_instructor->fetch();

            if ($instructor) {
                // ¡ÉXITO! ✅
                // Es una persona válida y TAMBIÉN es un instructor.
                
                // 6.4. Iniciar la sesión del usuario
                session_regenerate_id(true); 
                $_SESSION['user_id'] = $instructor['persona_id'];
                $_SESSION['user_role'] = 'instructor'; // Guardamos su rol
                
                // 6.5. Redirigir al dashboard del INSTRUCTOR
                header("Location: ../dashboard_instructor.php");
                exit; 
                
            } else {
                // Error: Contraseña correcta, pero no tiene el rol de instructor
                header("Location: ../login_instructor.php?error=noautorizado");
                exit;
            }

        } else {
            // Error: Correo no encontrado o contraseña incorrecta
            header("Location: ../login_instructor.php?error=credencialesinvalidas");
            exit;
        }

    } catch (PDOException $e) {
        // Error de la base de datos
        header("Location: ../login_instructor.php?error=dberror");
        exit;
    }

} else {
    // Si alguien intenta acceder a este archivo directamente
    header("Location: ../login_instructor.php");
    exit;
}
?>