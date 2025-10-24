<?php
// --- Archivo: dashboard_instructor.php ---
// (Este archivo está en /home/htdocs/)

// 1. EL CADENERO (Verificador de Sesión)
session_start();

// Si el usuario no ha iniciado sesión O no es un instructor, lo echamos.
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] != 'instructor') {
    header("Location: login_instructor.php?error=acceso_denegado");
    exit;
}

// 2. CONECTAR A BD Y OBTENER DATOS
require_once __DIR__ . '/includes/db.php';

$id_instructor = $_SESSION['user_id'];
$persona = null;
$instructor_info = null;
$clases_programadas = []; // Inicializamos como array vacío

try {
    // A. Buscar datos de la Persona (para el nombre)
    $stmt_persona = $pdo->prepare("SELECT nombre, apellidos FROM personas WHERE persona_id = ?");
    $stmt_persona->execute([$id_instructor]);
    $persona = $stmt_persona->fetch();

    // B. Buscar datos del Instructor (jornada y sucursal base)
    // Hacemos un JOIN con Sucursales para obtener el nombre
    $stmt_instructor = $pdo->prepare("
        SELECT i.inicio_jornada, i.fin_jornada, s.nombre_sucursal
        FROM instructores AS i
        JOIN sucursales AS s ON i.sucursal_base_id = s.sucursal_id
        WHERE i.persona_id = ?
    ");
    $stmt_instructor->execute([$id_instructor]);
    $instructor_info = $stmt_instructor->fetch();
    
    // C. Buscar las clases que imparte (¡pueden ser cero!)
    // Hacemos JOIN con Clases y Sucursales para tener la info completa
    $stmt_clases = $pdo->prepare("
        SELECT h.dia_semana, h.hora_inicio, h.hora_fin, c.nombre_clase, s.nombre_sucursal
        FROM horarios AS h
        JOIN clases AS c ON h.clase_id = c.clase_id
        JOIN sucursales AS s ON h.sucursal_id = s.sucursal_id
        WHERE h.instructor_id = ?
        ORDER BY h.dia_semana, h.hora_inicio 
    ");
    $stmt_clases->execute([$id_instructor]);
    $clases_programadas = $stmt_clases->fetchAll();
    
} catch (PDOException $e) {
    // Manejar error si la consulta falla
    echo "Error al cargar los datos del dashboard. " . $e->getMessage();
    exit;
}

// Si $persona o $instructor_info están vacíos, algo está muy mal.
if (!$persona || !$instructor_info) {
    echo "Error: No se pudieron encontrar los datos completos del instructor.";
    exit;
}

// 3. LA INTERFAZ VISIBLE (HTML)
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Instructor</title>
    <style>
        body { font-family: sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; 
         background-image: url("/insertar/bigsur.jpg");
         background-size: cover;
         background-repeat: no-repeat;
         background-position: center center;
         background-attachment: fixed;
         min-height: 100vh;
        }
        header { background: #333; color: white; padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; }
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
    </style>
</head>
<body>

    <header>
        <h1>Bienvenido, Instructor <?php echo htmlspecialchars($persona['nombre']); ?>!</h1>
        <nav>
            <a href="logout.php">Cerrar Sesión</a>
        </nav>
    </header>

    <div class="container">

        <div class="card">
            <div class="card-header">
                <h2>Tu Información</h2>
            </div>
            <div class="card-body">
                <ul class="info-list">
                    <li>
                        <span>Sucursal Base:</span>
                        <?php echo htmlspecialchars($instructor_info['nombre_sucursal']); ?>
                    </li>
                    <li>
                        <span>Jornada (Entrada):</span>
                        <?php echo date('g:i A', strtotime($instructor_info['inicio_jornada'])); ?>
                    </li>
                    <li>
                        <span>Jornada (Salida):</span>
                        <?php echo date('g:i A', strtotime($instructor_info['fin_jornada'])); ?>
                    </li>
                    <li>
                        <span>Perfil:</span>
                        <a href="perfil_instructor.php">Ver/Editar mi Perfil</a>
                    </li>
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2>Tus Clases Programadas</h2>
            </div>
            <div class="card-body">
                <?php
                // 4. Verificamos si el array $clases_programadas está vacío
                if (empty($clases_programadas)) {
                    // Mensaje si no tiene clases
                    echo '<p class="no-clases">No tienes clases asignadas en el horario actualmente.</p>';
                } else {
                    // 5. Si tiene clases, las recorremos y mostramos
                    foreach ($clases_programadas as $clase) {
                        echo '<div class="clase-item">';
                        echo '<h4>' . htmlspecialchars($clase['nombre_clase']) . '</h4>';
                        echo '<p>';
                        echo '<strong>Cuándo:</strong> ' . htmlspecialchars($clase['dia_semana']) . ' de ';
                        echo date('g:i A', strtotime($clase['hora_inicio'])) . ' a ';
                        echo date('g:i A', strtotime($clase['hora_fin']));
                        echo '<br>';
                        echo '<strong>Dónde:</strong> Sucursal ' . htmlspecialchars($clase['nombre_sucursal']);
                        echo '</p>';
                        echo '</div>';
                    }
                }
                ?>
            </div>
        </div>
        
    </div>

</body>
</html>