<?php
// Iniciar sesión primero
session_start();

// Incluir la base de datos con ruta absoluta
require_once __DIR__ . '/php/database.php';

// Verificar si la conexión se estableció
if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos");
}

$page_title = "Editar Cotización de Vuelo";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Obtener ID del vuelo a editar
$vuelo_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$vuelo = null;
$detalles_vuelo = null;

if ($vuelo_id > 0) {
    // Obtener información de la cotización
    if ($stmt = $conn->prepare("
        SELECT c.*, cl.nombre as cliente_nombre, cl.email as cliente_email 
        FROM cotizaciones c 
        LEFT JOIN clientes cl ON c.cliente_id = cl.id 
        WHERE c.id = ?
    ")) {
        $stmt->bind_param("i", $vuelo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $vuelo = $result->fetch_assoc();
        $stmt->close();
    }
    
    // Obtener detalles del vuelo
    if ($stmt = $conn->prepare("SELECT * FROM vuelos WHERE cotizacion_id = ?")) {
        $stmt->bind_param("i", $vuelo_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $detalles_vuelo = $result->fetch_assoc();
        $stmt->close();
    }
}

// Si no se encuentra el vuelo, redirigir
if (!$vuelo) {
    header("Location: vuelos.php");
    exit();
}

// Procesar formulario de actualización
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_id = intval($_POST['cliente_id']);
    $aerolinea = trim($_POST['aerolinea']);
    $tipo_vuelo = trim($_POST['tipo_vuelo']);
    $origen = trim($_POST['origen']);
    $destino = trim($_POST['destino']);
    $fecha_salida = trim($_POST['fecha_salida']);
    $fecha_regreso = ($tipo_vuelo == 'redondo') ? trim($_POST['fecha_regreso']) : null;
    $clase = trim($_POST['clase']);
    $adultos = intval($_POST['adultos']);
    $ninos = intval($_POST['ninos']);
    $bebes = intval($_POST['bebes']);
    $precio = floatval($_POST['precio']);
    $notas = trim($_POST['notas']);
    
    // Validaciones básicas
    if (empty($origen) || empty($destino) || empty($fecha_salida) || $precio <= 0) {
        $error = "Por favor complete todos los campos requeridos correctamente";
    } else {
        // Actualizar en base de datos
        $conn->begin_transaction();
        
        try {
            // Actualizar cotización
            $servicio = "Vuelo {$origen}-{$destino}";
            $stmt = $conn->prepare("UPDATE cotizaciones SET cliente_id = ?, servicio = ?, total = ?, notas = ?, fecha_actualizacion = NOW() WHERE id = ?");
            $stmt->bind_param("issds", $cliente_id, $servicio, $precio, $notas, $vuelo_id);
            $stmt->execute();
            $stmt->close();

            // Actualizar detalles del vuelo
            $stmt = $conn->prepare("UPDATE vuelos SET aerolinea = ?, tipo_vuelo = ?, origen = ?, destino = ?, fecha_salida = ?, fecha_regreso = ?, clase = ?, pasajeros_adultos = ?, pasajeros_ninos = ?, pasajeros_bebes = ?, precio = ?, notas = ? WHERE cotizacion_id = ?");
            $stmt->bind_param("ssssssssiiids", $aerolinea, $tipo_vuelo, $origen, $destino, $fecha_salida, $fecha_regreso, $clase, $adultos, $ninos, $bebes, $precio, $notas, $vuelo_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            
            $success = "Cotización de vuelo actualizada exitosamente!";
            // Recargar los datos actualizados
            header("Location: editar-vuelo.php?id=" . $vuelo_id);
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error al actualizar la cotización: " . $e->getMessage();
        }
    }
}

// Obtener clientes para el select
$clientes = [];
if ($stmt = $conn->prepare("SELECT id, nombre, email FROM clientes ORDER BY nombre")) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
    $stmt->close();
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Cotización de Vuelo</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="vuelos.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST" id="vueloForm">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Información del Vuelo</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="aerolinea" class="form-label">Aerolínea *</label>
                            <select class="form-select" id="aerolinea" name="aerolinea" required>
                                <option value="">Seleccionar aerolínea...</option>
                                <option value="American Airlines" <?php echo ($detalles_vuelo['aerolinea'] ?? '') == 'American Airlines' ? 'selected' : ''; ?>>American Airlines</option>
                                <option value="Delta Air Lines" <?php echo ($detalles_vuelo['aerolinea'] ?? '') == 'Delta Air Lines' ? 'selected' : ''; ?>>Delta Air Lines</option>
                                <option value="United Airlines" <?php echo ($detalles_vuelo['aerolinea'] ?? '') == 'United Airlines' ? 'selected' : ''; ?>>United Airlines</option>
                                <option value="LATAM" <?php echo ($detalles_vuelo['aerolinea'] ?? '') == 'LATAM' ? 'selected' : ''; ?>>LATAM</option>
                                <option value="Copa Airlines" <?php echo ($detalles_vuelo['aerolinea'] ?? '') == 'Copa Airlines' ? 'selected' : ''; ?>>Copa Airlines</option>
                                <option value="Avianca" <?php echo ($detalles_vuelo['aerolinea'] ?? '') == 'Avianca' ? 'selected' : ''; ?>>Avianca</option>
                                <option value="JetBlue" <?php echo ($detalles_vuelo['aerolinea'] ?? '') == 'JetBlue' ? 'selected' : ''; ?>>JetBlue</option>
                                <option value="Spirit Airlines" <?php echo ($detalles_vuelo['aerolinea'] ?? '') == 'Spirit Airlines' ? 'selected' : ''; ?>>Spirit Airlines</option>
                                <option value="Frontier Airlines" <?php echo ($detalles_vuelo['aerolinea'] ?? '') == 'Frontier Airlines' ? 'selected' : ''; ?>>Frontier Airlines</option>
                                <option value="otra" <?php echo (!in_array($detalles_vuelo['aerolinea'] ?? '', ['American Airlines', 'Delta Air Lines', 'United Airlines', 'LATAM', 'Copa Airlines', 'Avianca', 'JetBlue', 'Spirit Airlines', 'Frontier Airlines'])) && !empty($detalles_vuelo['aerolinea']) ? 'selected' : ''; ?>>Otra aerolínea</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tipo_vuelo" class="form-label">Tipo de Vuelo *</label>
                            <select class="form-select" id="tipo_vuelo" name="tipo_vuelo" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="sencillo" <?php echo ($detalles_vuelo['tipo_vuelo'] ?? '') == 'sencillo' ? 'selected' : ''; ?>>Solo ida</option>
                                <option value="redondo" <?php echo ($detalles_vuelo['tipo_vuelo'] ?? '') == 'redondo' ? 'selected' : ''; ?>>Ida y vuelta</option>
                                <option value="multidestino" <?php echo ($detalles_vuelo['tipo_vuelo'] ?? '') == 'multidestino' ? 'selected' : ''; ?>>Multidestino</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="origen" class="form-label">Origen *</label>
                            <input type="text" class="form-control" id="origen" name="origen" required 
                                   placeholder="Ciudad o código de aeropuerto" value="<?php echo $detalles_vuelo['origen'] ?? ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="destino" class="form-label">Destino *</label>
                            <input type="text" class="form-control" id="destino" name="destino" required 
                                   placeholder="Ciudad o código de aeropuerto" value="<?php echo $detalles_vuelo['destino'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha_salida" class="form-label">Fecha de Salida *</label>
                            <input type="date" class="form-control" id="fecha_salida" name="fecha_salida" required 
                                   min="<?php echo date('Y-m-d'); ?>" value="<?php echo $detalles_vuelo['fecha_salida'] ?? ''; ?>">
                        </div>
                        <div class="col-md-6" id="fecha_regreso_container" style="display: <?php echo ($detalles_vuelo['tipo_vuelo'] ?? '') == 'redondo' ? 'block' : 'none'; ?>;">
                            <label for="fecha_regreso" class="form-label">Fecha de Regreso</label>
                            <input type="date" class="form-control" id="fecha_regreso" name="fecha_regreso" 
                                   min="<?php echo date('Y-m-d'); ?>" value="<?php echo $detalles_vuelo['fecha_regreso'] ?? ''; ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="clase" class="form-label">Clase *</label>
                            <select class="form-select" id="clase" name="clase" required>
                                <option value="">Seleccionar clase...</option>
                                <option value="economica" <?php echo ($detalles_vuelo['clase'] ?? '') == 'economica' ? 'selected' : ''; ?>>Económica</option>
                                <option value="premium" <?php echo ($detalles_vuelo['clase'] ?? '') == 'premium' ? 'selected' : ''; ?>>Premium Economy</option>
                                <option value="business" <?php echo ($detalles_vuelo['clase'] ?? '') == 'business' ? 'selected' : ''; ?>>Business</option>
                                <option value="primera" <?php echo ($detalles_vuelo['clase'] ?? '') == 'primera' ? 'selected' : ''; ?>>Primera Clase</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Pasajeros *</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="input-group">
                                        <span class="input-group-text">Adultos</span>
                                        <input type="number" class="form-control" name="adultos" value="<?php echo $detalles_vuelo['pasajeros_adultos'] ?? 1; ?>" min="1" required>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="input-group">
                                        <span class="input-group-text">Niños</span>
                                        <input type="number" class="form-control" name="ninos" value="<?php echo $detalles_vuelo['pasajeros_ninos'] ?? 0; ?>" min="0">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="input-group">
                                        <span class="input-group-text">Bebés</span>
                                        <input type="number" class="form-control" name="bebes" value="<?php echo $detalles_vuelo['pasajeros_bebes'] ?? 0; ?>" min="0">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Información de Precios</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="precio" class="form-label">Precio Total ($) *</label>
                            <input type="number" class="form-control" id="precio" name="precio" 
                                   step="0.01" min="0" required placeholder="0.00" value="<?php echo $vuelo['total'] ?? ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="precio_por_persona" class="form-label">Precio por Persona</label>
                            <input type="text" class="form-control" id="precio_por_persona" readonly 
                                   placeholder="Se calculará automáticamente">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Información del Cliente</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="cliente_id" class="form-label">Seleccionar Cliente *</label>
                        <select class="form-select" id="cliente_id" name="cliente_id" required>
                            <option value="">Seleccionar cliente...</option>
                            <?php foreach ($clientes as $cliente): ?>
                                <option value="<?php echo $cliente['id']; ?>" <?php echo ($vuelo['cliente_id'] ?? 0) == $cliente['id'] ? 'selected' : ''; ?>>
                                    <?php echo $cliente['nombre']; ?> (<?php echo $cliente['email']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="text-center">
                        <a href="nuevo-cliente.php?return_to=editar-vuelo.php?id=<?php echo $vuelo_id; ?>" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-user-plus me-1"></i> Nuevo Cliente
                        </a>
                    </div>
                </div>
            </div>

            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Notas Adicionales</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control" id="notas" name="notas" rows="5" 
                                  placeholder="Notas importantes sobre esta cotización..."><?php echo $vuelo['notas'] ?? ''; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card card-civit">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-civit-primary btn-lg w-100 mb-2">
                        <i class="fas fa-save me-2"></i> Actualizar Cotización
                    </button>
                    <a href="vuelos.php" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mostrar/ocultar fecha de regreso según tipo de vuelo
    const tipoVuelo = document.getElementById('tipo_vuelo');
    const fechaRegresoContainer = document.getElementById('fecha_regreso_container');
    
    tipoVuelo.addEventListener('change', function() {
        fechaRegresoContainer.style.display = this.value === 'redondo' ? 'block' : 'none';
        if (this.value !== 'redondo') {
            document.getElementById('fecha_regreso').value = '';
        }
    });
    
    // Calcular precio por persona
    function calcularPrecioPorPersona() {
        const precioTotal = parseFloat(document.getElementById('precio').value) || 0;
        const adultos = parseInt(document.querySelector('input[name="adultos"]').value) || 0;
        const ninos = parseInt(document.querySelector('input[name="ninos"]').value) || 0;
        const bebes = parseInt(document.querySelector('input[name="bebes"]').value) || 0;
        
        const totalPasajeros = adultos + ninos;
        if (totalPasajeros > 0 && precioTotal > 0) {
            const precioPorPersona = precioTotal / totalPasajeros;
            document.getElementById('precio_por_persona').value = '$' + precioPorPersona.toFixed(2);
        } else {
            document.getElementById('precio_por_persona').value = '';
        }
    }

    // Calcular inicialmente
    calcularPrecioPorPersona();

    // Event listeners para cambios
    document.getElementById('precio').addEventListener('input', calcularPrecioPorPersona);
    document.querySelector('input[name="adultos"]').addEventListener('input', calcularPrecioPorPersona);
    document.querySelector('input[name="ninos"]').addEventListener('input', calcularPrecioPorPersona);
});
</script>