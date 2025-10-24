<?php
// --- Archivo: login_socio.php ---
// (Este archivo está en /home/htdocs/)

// 1. Iniciar la sesión
// Lo necesitamos para revisar si el usuario YA está logueado
// y para mostrar los mensajes de error.
session_start();

// 2. Seguridad: Si el usuario ya inició sesión, redirigirlo al dashboard
// No tiene sentido mostrarle el login si ya está dentro.
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] == 'socio') {
    header("Location: index.php");
    exit;
}

// 3. Manejo de mensajes de error
// Leemos el error que nos envió 'process_login_socio.php' desde la URL (?error=...)
$error_message = '';
if (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'camposvacios':
            $error_message = 'Por favor, rellena todos los campos.';
            break;
        case 'credencialesinvalidas':
            $error_message = 'Correo o contraseña incorrectos. Inténtalo de nuevo.';
            break;
        case 'noautorizado':
            $error_message = 'Acceso denegado. Esta área es solo para socios.';
            break;
        case 'acceso_denegado':
            $error_message = 'Debes iniciar sesión para ver esa página.';
            break;
        case 'dberror':
            $error_message = 'Error del sistema. Por favor, inténtalo más tarde.';
            break;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Socios</title>
    
    <style>
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
            display: grid; 
            place-items: center; 
            min-height: 90vh; 
            /*background-color: #f8f9fa;*/
            background-image: url("/insertar/27417745_7335958.jpg"); 
            background-size: cover;  
            background-repeat: no-repeat; 
            background-position: center center; 
            background-attachment: fixed;
        }
        .login-container { 
            background: #ffffff; 
            border-radius: 8px; 
            padding: 2rem 3rem; 
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            width: 350px;
        }
        .login-container h2 { text-align: center; color: #333; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { 
            display: block; 
            margin-bottom: 0.5rem; 
            font-weight: 600; 
            color: #555;
        }
        .form-group input { 
            width: 100%; 
            padding: 0.75rem; 
            border: 1px solid #ccc; 
            border-radius: 4px;
            box-sizing: border-box; /* Importante para que el padding no desborde */
        }
        .form-button { 
            width: 100%; 
            padding: 0.75rem; 
            background-color: #007bff; 
            color: white; 
            border: none; 
            border-radius: 4px; 
            cursor: pointer; 
            font-size: 1rem;
            font-weight: 600;
        }
        .form-button:hover { background-color: #0056b3; }
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

    <div class="login-container">
        <h2>Acceso Socios</h2>

        <?php
        // 4. Mostrar el mensaje de error aquí, si existe
        if (!empty($error_message)) {
            // Usamos htmlspecialchars por seguridad, para prevenir ataques XSS
            echo '<div class="error-message">' . htmlspecialchars($error_message) . '</div>';
        }
        ?>

        <form action="/includes/process_login_socio.php" method="POST">
            
            <div class="form-group">
                <label for="correo">Correo Electrónico:</label>
                <input type="email" id="correo" name="correo" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña:</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <div class="form-group">
                <button type="submit" class="form-button">Iniciar Sesión</button>
            </div>
        </form>
        
        <div class="footer-links">
            <p>¿No tienes cuenta? <a href="register.php">Regístrate aquí</a>.</p>
            <p><a href="login_instructor.php">¿Eres instructor?</a></p>
        </div>

    </div>

</body>
</html>