<?php
// Powered by Site.pro
    // --- Archivo: index.php (en /htdocs/) ---

// 1. EL CADENERO (Verificador de Sesión)
session_start();

// Si el usuario no ha iniciado sesión O no es un socio, lo echamos.
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'socio') {
    header("Location: login_socio.php?error=acceso_denegado");
    exit;
}
require_once __DIR__ . '/includes/db.php';
$id_socio = $_SESSION['user_id'];
$usuario = null;
$membresia = null;

try {
    // Buscar datos de la Persona (para el nombre)
    $stmt_persona = $pdo->prepare("SELECT nombre, apellidos FROM personas WHERE persona_id = ?");
    $stmt_persona->execute([$id_socio]);
    $usuario = $stmt_persona->fetch();

    // Buscar datos del Socio (para la membresía)
    $stmt_socio = $pdo->prepare("SELECT tipo_membresia, fin_membresia FROM socios WHERE persona_id = ?");
    $stmt_socio->execute([$id_socio]);
    $membresia = $stmt_socio->fetch();
    
    // (Aquí iría la consulta más compleja para 'próximas clases')

} catch (PDOException $e) {
    // Manejar error si la consulta falla
    echo "Error al cargar los datos. " . $e->getMessage();
    exit;
}

// Si $usuario está vacío (raro, pero posible), algo salió mal.
if (!$usuario || !$membresia) {
    echo "Error: No se pudieron encontrar los datos del socio.";
    exit;
}

// 3. LA INTERFAZ VISIBLE (HTML)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mi Dashboard - Gimnasio</title>
    
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; /*background-color: #f4f4f4;*/background-image: url("/insertar/patron2.jpg"); background-size: cover;  background-repeat: no-repeat; background-position: center center; background-attachment: fixed;   min-height: 100vh; margin: 0; padding: 0; }
        header {background: #007bff;  /*Color azul para socio */ color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
        header h1 { margin: 0; font-size: 1.5rem; }
        header a { color: #fff; text-decoration: none; font-weight: bold; }
        .container { max-width: 1200px; margin: 2rem auto; padding: 1rem; }
        .card { background: #fff; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); margin-bottom: 2rem; }
        .card-header { padding: 1.5rem; border-bottom: 1px solid #eee; }
        .card-header h2 { margin: 0; }
        .card-body { padding: 1.5rem; }
        .info-list { list-style: none; padding: 0; }
        .info-list li { display: flex; justify-content: space-between; padding: 0.75rem 0; border-bottom: 1px solid #f0f0f0; }
        .info-list li:last-child { border-bottom: none; }
        .info-list span { font-weight: bold; color: #555; }
        .clase-item { padding: 1rem; border: 1px solid #ddd; border-radius: 4px; margin-bottom: 1rem; }
        .clase-item h4 { margin: 0 0 0.5rem 0; color: #007bff; }
        .no-clases { color: #777; font-style: italic; }
        /* Estilos para los enlaces de acciones */
        .action-links { list-style: none; padding: 0; }
        .action-links li { margin-bottom: 1rem; }
        .action-links a { 
            display: block; 
            padding: 1rem; 
            background: #e9ecef; 
            color: #333; 
            text-decoration: none; 
            font-weight: bold; 
            border-radius: 5px;
        }
        .action-links a:hover { background: #dee2e6; }
    </style>
</head>
<body>

    <header>
        <h1>¡Hola, Socio <?php echo htmlspecialchars($usuario['nombre']); ?>!</h1>
        <nav>
            <a href="logout.php">Cerrar Sesión</a>
        </nav>
    </header>

    <div class="container">

        <div class="card">
            <div class="card-header">
                <h2>Tu Membresía</h2>
            </div>
            <div class="card-body">
                <ul class="info-list">
                    <li>
                        <span>Plan:</span>
                        <strong><?php echo htmlspecialchars($membresia['tipo_membresia']); ?></strong>
                    </li>
                    <li>
                        <span>Vence el:</span>
                        <strong>
<?php
    // 1. Array con los nombres de los meses en español
    $meses_es = [
        "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
        "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
    ];

    // 2. Convertimos la fecha de la BD a un timestamp
    $timestamp = strtotime($membresia['fin_membresia']);

    // 3. Obtenemos las partes de la fecha que necesitamos
    $dia = date('d', $timestamp);
    $mes_numero = date('n', $timestamp); // 'n' da el número del mes (1-12)
    $ano = date('Y', $timestamp);

    // 4. Buscamos el nombre del mes en nuestro array
    // (restamos 1 porque los arrays empiezan en 0)
    $nombre_mes = $meses_es[$mes_numero - 1];

    // 5. Imprimimos el formato final
    echo "$dia de $nombre_mes, $ano";
?>
</strong>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Tus Próximas Clases Inscritas</h2>
            </div>
            <div class="card-body">
                <?php
                // 4. Verificamos si el array $proximas_clases está vacío
                if (empty($proximas_clases)) {
                    // Mensaje si no está inscrito a clases
                    echo '<p class="no-clases">Actualmente no estás inscrito en ninguna clase.</p>';
                } else {
                    // 5. Si tiene clases, las recorremos y mostramos
                    foreach ($proximas_clases as $clase) {
                        echo '<div class="clase-item">';
                        echo '<h4>' . htmlspecialchars($clase['nombre_clase']) . '</h4>';
                        echo '<p>';
                        echo '<strong>Cuándo:</strong> ' . htmlspecialchars($clase['dia_semana']) . ' a las ';
                        echo date('g:i A', strtotime($clase['hora_inicio']));
                        echo '<br>';
                        echo '<strong>Dónde:</strong> Sucursal ' . htmlspecialchars($clase['nombre_sucursal']);
                        echo '</p>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
        
        <div class="card">
            <div class="card-header">
                <h2>Acciones</h2>
            </div>
            <div class="card-body">
                <ul class="action-links">
                    <li><a href="clases_disponibles.php">Inscribirse a una Nueva Clase</a></li>
                    <li><a href="perfil.php">Ver/Editar mi Perfil</a></li>
                    <li><a href="historial_asistencias.php">Ver Historial de Asistencias</a></li>
                </ul>
            </div>
        </div>
        
    </div>

</body>
</html>