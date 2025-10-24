<?php
// --- Archivo: register.php ---
// (Este archivo está en /home/htdocs/)

// 1. Iniciar la sesión (para mensajes de error)
session_start();

// 2. Incluir la conexión a la BD
// ¡Necesitamos esto para obtener la lista de sucursales!
require_once __DIR__ . '/includes/db.php';

// 3. Manejo de mensajes de error (de process_register.php)
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'camposvacios':
            $error_message = 'Por favor, rellena todos los campos obligatorios.';
            break;
        case 'emailinvalido':
            $error_message = 'El formato del correo electrónico no es válido.';
            break;
        case 'emailduplicado':
            $error_message = 'Ese correo electrónico ya está registrado. Intenta iniciar sesión.';
            break;
        case 'dberror':
            $error_message = 'Error del sistema. Por favor, inténtalo más tarde.';
            break;
    }
}

// 4. Obtener la lista de sucursales para el <select>
$sucursales = [];
try {
    // Preparamos y ejecutamos la consulta para obtener todas las sucursales
    $stmt = $pdo->query("SELECT sucursal_id, nombre_sucursal FROM sucursales ORDER BY nombre_sucursal");
    $sucursales = $stmt->fetchAll();
} catch (PDOException $e) {
    // Si la BD falla, no podemos mostrar el formulario de registro
    $error_message = 'Error al cargar las sucursales. No es posible registrarse en este momento.';
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Nuevo Socio</title>

    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: grid; 
            place-items: center; 
            min-height: 90vh; 
            /*background-color: #f8f9fa;*/
             background-image: url("/insertar/monterrey.jpg");
             background-size: cover;
             background-repeat: no-repeat;
             background-position: center center;
             background-attachment: fixed;
             min-height: 100vh;
        }
        /* Hacemos el contenedor un poco más ancho para el formulario de registro */
        .register-container { 
            background: #ffffff; 
            border-radius: 8px; 
            padding: 2rem 3rem; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            width: 450px;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .register-container h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 1.25rem; }
        .form-group label { 
            display: block; 
            margin-bottom: 0.5rem; 
            font-weight: 600; 
            color: #555;
        }
        .form-group input, .form-group select { 
            width: 100%; 
            padding: 0.75rem; 
            border: 1px solid #ccc; 
            border-radius: 4px;
            box-sizing: border-box; /* Importante */
        }
        .form-button { 
            width: 100%; 
            padding: 0.75rem; 
            background-color: #17a2b8; /* Color cian */
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 1rem;
            font-weight: 600;
        }
        .form-button:hover { background-color: #138496; }
        .error-message { 
            color: #D8000C; 
            background-color: #FFD2D2; 
            border: 1px solid #D8000C;
            padding: 0.75rem; 
            border-radius: 4px; 
            text-align: center; 
            margin-bottom: 1rem;
        }
        .footer-links { text-align: center; margin-top: 1.5rem; font-size: 0.9rem; }
        .footer-links a { color: #007bff; text-decoration: none; }
    </style>
</head>
<body>

    <div class="register-container">
        <h2>Crear Cuenta de Socio</h2>

        <?php
        // 5. Mostrar el mensaje de error aquí, si existe
        if (!empty($error_message)) {
            echo '<div class="error-message">' . htmlspecialchars($error_message) . '</div>';
        }
        ?>

        <form action="/includes/process_register.php" method="POST">
            
            <div class="form-group">
                <label for="nombre">Nombre(s):</label>
                <input type="text" id="nombre" name="nombre" required>
            </div>
            
            <div class="form-group">
                <label for="apellidos">Apellidos:</label>
                <input type="text" id="apellidos" name="apellidos" required>
            </div>

            <div class="form-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required minlength="8">
            </div>

            <div class="form-group">
                <label for="fecha_nacimiento">Fecha de Nacimiento:</label>
                <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
            </div>

            <div class="form-group">
                <label for="telefono">Teléfono: (Opcional)</label>
                <input type="tel" id="telefono" name="telefono">
            </div>
            
            <div class="form-group">
                <label for="direccion">Dirección: (Opcional)</label>
                <input type="text" id="direccion" name="direccion">
            </div>

            <hr style="border: 1px solid #eee; margin: 1.5rem 0;">

            <div class="form-group">
                <label for="sucursal_id">Sucursal de Inscripción:</label>
                <select id="sucursal_id" name="sucursal_id" required>
                    <option value="">-- Selecciona una sucursal --</option>
                    <?php
                    // 7. Llenar el <select> con los datos de la BD
                    foreach ($sucursales as $sucursal) {
                        echo '<option value="' . htmlspecialchars($sucursal['sucursal_id']) . '">'
                           . htmlspecialchars($sucursal['nombre_sucursal'])
                           . '</option>';
                    }
                    ?>
                </select>
            </div>

            <div class="form-group">
                <label for="tipo_membresia">Tipo de Membresía:</label>
                <select id="tipo_membresia" name="tipo_membresia" required>
                    <option value="Basica">Plan Básico (1 año)</option>
                    <option value="Premium">Plan Premium (1 año)</option>
                    <option value="VIP">Plan VIP (1 año)</option>
                </select>
            </div>

            <div class="form-group">
                <button type="submit" class="form-button">Crear Cuenta</button>
            </div>
        </form>
        
        <div class="footer-links">
            <p>¿Ya tienes una cuenta? <a href="login_socio.php">Inicia sesión aquí</a>.</p>
        </div>

    </div>

</body>
</html>