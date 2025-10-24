<?php
// --- Archivo: process_register.php ---
// Este archivo está en /home/includes/

/**
 * 1. Iniciar la sesión
 * Lo usamos para iniciar sesión automáticamente al usuario
 * después de que se registre.
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

    // 4. Obtener todos los datos del formulario de registro
    // (Asumimos que tu formulario en register.php tiene estos campos)
    
    // Datos para la tabla Personas
    $nombre = $_POST['nombre'] ?? '';
    $apellidos = $_POST['apellidos'] ?? '';
    $correo = $_POST['correo'] ?? '';
    $password = $_POST['password'] ?? '';
    $fecha_nacimiento = $_POST['fecha_nacimiento'] ?? '';
    $telefono = $_POST['telefono'] ?? '';
    $direccion = $_POST['direccion'] ?? ''; // Opcional

    // Datos para la tabla Socios
    // En un formulario real, estos vendrían de un <select>
    $sucursal_id = $_POST['sucursal_id'] ?? 1; // Asumimos 1 como default
    $tipo_membresia = $_POST['tipo_membresia'] ?? 'Basica'; // Asumimos 'Basica'
    
    // 5. Validación simple de campos
    if (empty($nombre) || empty($apellidos) || empty($correo) || empty($password) || empty($fecha_nacimiento)) {
        header("Location: ../register.php?error=camposvacios");
        exit;
    }
    
    // Validar formato de correo
    if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../register.php?error=emailinvalido");
        exit;
    }

    /**
     * 6. Hashear la contraseña (¡CRÍTICO PARA LA SEGURIDAD!)
     * Nunca, NUNCA, guardes contraseñas en texto plano.
     */
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    /**
     * 7. Iniciar una Transacción
     * Esto asegura que AMBAS inserciones (en Personas y en Socios)
     * funcionen. Si una falla, la otra se revierte (rollBack).
     */
    try {
        // Iniciar el modo de transacción
        $pdo->beginTransaction();

        // --- ETAPA A: Insertar en la tabla supertipo Personas ---
        
        $sql_persona = "INSERT INTO personas (nombre, apellidos, direccion, correo, telefono, fecha_nacimiento, password) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt_persona = $pdo->prepare($sql_persona);
        $stmt_persona->execute([
            $nombre, 
            $apellidos, 
            $direccion, 
            $correo, 
            $telefono, 
            $fecha_nacimiento, 
            $password_hash // Guardamos el hash seguro
        ]);

        // --- ETAPA B: Insertar en la tabla subtipo Socios ---

        // 8. Obtener el ID de la persona que acabamos de crear
        $nueva_persona_id = $pdo->lastInsertId();

        // 9. Definir fechas de membresía (ej. 1 año desde hoy)
        $inicio_membresia = date('Y-m-d'); // Fecha de hoy
        $fin_membresia = date('Y-m-d', strtotime('+1 year')); // Un año desde hoy

        $sql_socio = "INSERT INTO socios (persona_id, tipo_membresia, inicio_membresia, fin_membresia, sucursal_id) 
                      VALUES (?, ?, ?, ?, ?)";
        
        $stmt_socio = $pdo->prepare($sql_socio);
        $stmt_socio->execute([
            $nueva_persona_id, 
            $tipo_membresia, 
            $inicio_membresia, 
            $fin_membresia, 
            $sucursal_id
        ]);

        // 10. Si ambas inserciones fueron exitosas, confirmar la transacción
        $pdo->commit();

        // 11. (Opcional) Iniciar sesión al nuevo usuario
        session_regenerate_id(true);
        $_SESSION['user_id'] = $nueva_persona_id;
        $_SESSION['user_role'] = 'socio';

        // 12. Redirigir al dashboard con un mensaje de éxito
        header("Location: ../index.php?registro=exitoso");
        exit;

    } catch (PDOException $e) {
        // 13. ¡Algo salió mal! Revertir la transacción
        $pdo->rollBack();

        // 14. Manejar errores comunes (ej. correo ya existe)
        // El código '23000' es un error de violación de integridad (como un UNIQUE)
        if ($e->getCode() == 23000) {
            header("Location: ../register.php?error=emailduplicado");
        } else {
            // Otro error de base de datos
            // En producción, deberías registrar $e->getMessage() en un log
            header("Location: ../register.php?error=dberror");
        }
        exit;
    }

} else {
    // Si alguien intenta acceder a este archivo directamente
    header("Location: ../register.php");
    exit;
}
?>