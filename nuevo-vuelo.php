<?php
// Iniciar sesión primero
session_start();

// Incluir la base de datos con ruta absoluta
require_once __DIR__ . '/php/database.php';

// Verificar si la conexión se estableció
if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos");
}

$page_title = "Nueva Cotización de Vuelo";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Procesar formulario
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
        // Generar código único
        $codigo = 'VUE-' . date('Ymd-His');
        $servicio = "Vuelo {$origen}-{$destino}";
        
        // Insertar en base de datos
        $conn->begin_transaction();
        
        try {
            // Insertar cotización
            $stmt = $conn->prepare("INSERT INTO cotizaciones (cliente_id, usuario_id, codigo, servicio, fecha_emision, fecha_vigencia, total, estado, notas) 
                                   VALUES (?, ?, ?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), ?, 'pendiente', ?)");
            $stmt->bind_param("iissds", $cliente_id, $_SESSION['usuario_id'], $codigo, $servicio, $precio, $notas);
            $stmt->execute();
            $cotizacion_id = $stmt->insert_id;
            $stmt->close();

            // Insertar servicio
            $stmt = $conn->prepare("INSERT INTO cotizacion_servicios (cotizacion_id, tipo_servicio, detalles, precio) 
                                   VALUES (?, 'vuelo', ?, ?)");
            $detalles_servicio = "Vuelo {$aerolinea} - {$origen} a {$destino}";
            $stmt->bind_param("iss", $cotizacion_id, $detalles_servicio, $precio);
            $stmt->execute();
            $stmt->close();

            // Insertar detalles del vuelo
            $stmt = $conn->prepare("INSERT INTO vuelos (cotizacion_id, aerolinea, tipo_vuelo, origen, destino, fecha_salida, fecha_regreso, clase, pasajeros_adultos, pasajeros_ninos, pasajeros_bebes, precio, notas) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isssssssiiids", $cotizacion_id, $aerolinea, $tipo_vuelo, $origen, $destino, $fecha_salida, $fecha_regreso, $clase, $adultos, $ninos, $bebes, $precio, $notas);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            
            $success = "Cotización de vuelo creada exitosamente!";
            // Redirigir después de 2 segundos
            echo "<script>setTimeout(function() { window.location.href = 'vuelos.php'; }, 2000);</script>";
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error al crear la cotización: " . $e->getMessage();
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
    <h1 class="h2">Nueva Cotización de Vuelo</h1>
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
                                <option value="American Airlines">American Airlines</option>
                                <option value="Delta Air Lines">Delta Air Lines</option>
                                <option value="United Airlines">United Airlines</option>
                                <option value="LATAM">LATAM</option>
                                <option value="Copa Airlines">Copa Airlines</option>
                                <option value="Avianca">Avianca</option>
                                <option value="JetBlue">JetBlue</option>
                                <option value="Spirit Airlines">Spirit Airlines</option>
                                <option value="Frontier Airlines">Frontier Airlines</option>
                                <option value="otra">Otra aerolínea</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="tipo_vuelo" class="form-label">Tipo de Vuelo *</label>
                            <select class="form-select" id="tipo_vuelo" name="tipo_vuelo" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="sencillo">Solo ida</option>
                                <option value="redondo">Ida y vuelta</option>
                                <option value="multidestino">Multidestino</option>
                            </select>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="origen" class="form-label">Origen *</label>
                            <input type="text" class="form-control" id="origen" name="origen" required 
                                   placeholder="Ciudad o código de aeropuerto">
                        </div>
                        <div class="col-md-6">
                            <label for="destino" class="form-label">Destino *</label>
                            <input type="text" class="form-control" id="destino" name="destino" required 
                                   placeholder="Ciudad o código de aeropuerto">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha_salida" class="form-label">Fecha de Salida *</label>
                            <input type="date" class="form-control" id="fecha_salida" name="fecha_salida" required 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="col-md-6" id="fecha_regreso_container" style="display: none;">
                            <label for="fecha_regreso" class="form-label">Fecha de Regreso</label>
                            <input type="date" class="form-control" id="fecha_regreso" name="fecha_regreso" 
                                   min="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="clase" class="form-label">Clase *</label>
                            <select class="form-select" id="clase" name="clase" required>
                                <option value="">Seleccionar clase...</option>
                                <option value="economica">Económica</option>
                                <option value="premium">Premium Economy</option>
                                <option value="business">Business</option>
                                <option value="primera">Primera Clase</option>
                            </select>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Pasajeros *</label>
                            <div class="row g-2">
                                <div class="col-4">
                                    <div class="input-group">
                                        <span class="input-group-text">Adultos</span>
                                        <input type="number" class="form-control" name="adultos" value="1" min="1" required>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="input-group">
                                        <span class="input-group-text">Niños</span>
                                        <input type="number" class="form-control" name="ninos" value="0" min="0">
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="input-group">
                                        <span class="input-group-text">Bebés</span>
                                        <input type="number" class="form-control" name="bebes" value="0" min="0">
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
                                   step="0.01" min="0" required placeholder="0.00">
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
                                <option value="<?php echo $cliente['id']; ?>">
                                    <?php echo $cliente['nombre']; ?> (<?php echo $cliente['email']; ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="text-center">
                        <a href="nuevo-cliente.php?return_to=nuevo-vuelo.php" class="btn btn-outline-primary btn-sm">
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
                                  placeholder="Notas importantes sobre esta cotización..."></textarea>
                    </div>
                </div>
            </div>

            <div class="card card-civit">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-civit-primary btn-lg w-100 mb-2">
                        <i class="fas fa-save me-2"></i> Guardar Cotización
                    </button>
                    <button type="reset" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-undo me-1"></i> Limpiar Formulario
                    </button>
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

    document.getElementById('precio').addEventListener('input', calcularPrecioPorPersona);
    document.querySelector('input[name="adultos"]').addEventListener('input', calcularPrecioPorPersona);
    document.querySelector('input[name="ninos"]').addEventListener('input', calcularPrecioPorPersona);
});
</script>