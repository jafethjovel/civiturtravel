<?php
session_start();
require_once __DIR__ . '/php/database.php';
require_once __DIR__ . '/php/auth.php';

// Verificar permisos
verificarAutenticacion();
verificarPermisos(ROLES_VENDEDOR);

$hotel_id = isset($_GET['hotel_id']) ? (int)$_GET['hotel_id'] : 0;

// Obtener información del hotel
$hotel = null;
if ($hotel_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM hoteles WHERE id = ?");
    $stmt->bind_param("i", $hotel_id);
    $stmt->execute();
    $hotel = $stmt->get_result()->fetch_assoc();
    $stmt->close();
}

if (!$hotel) {
    header("Location: hoteles.php");
    exit;
}

// Procesar formulario de nueva habitación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipo_habitacion = trim($_POST['tipo_habitacion']);
    $precio_noche = (float)$_POST['precio_noche'];
    $moneda = trim($_POST['moneda']);
    $capacidad = (int)$_POST['capacidad'];
    $descripcion = trim($_POST['descripcion']);
    $caracteristicas = trim($_POST['caracteristicas']);
    
    // INSERT corregido con tipo_habitacion
    $stmt = $conn->prepare("INSERT INTO habitaciones (hotel_id, tipo_habitacion, precio_noche, moneda, capacidad, descripcion, amenities) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isdsiss", $hotel_id, $tipo_habitacion, $precio_noche, $moneda, $capacidad, $descripcion, $caracteristicas);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Habitación agregada correctamente";
        header("Location: habitaciones.php?hotel_id=" . $hotel_id);
        exit;
    }
    $stmt->close();
}

// Obtener habitaciones del hotel - CORREGIDO
$habitaciones = [];
$stmt = $conn->prepare("SELECT * FROM habitaciones WHERE hotel_id = ? ORDER BY tipo_habitacion, precio_noche");
$stmt->bind_param("i", $hotel_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $habitaciones[] = $row;
}
$stmt->close();

$page_title = "Habitaciones - " . $hotel['nombre'];
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-bed me-2"></i>
        Habitaciones - <?php echo htmlspecialchars($hotel['nombre']); ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="hoteles.php" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Volver a Hoteles
        </a>
        <button type="button" class="btn btn-sm btn-civit-primary" data-bs-toggle="modal" data-bs-target="#nuevaHabitacionModal">
            <i class="fas fa-plus me-1"></i> Nueva Habitación
        </button>
    </div>
</div>

<!-- Modal Nueva Habitación -->
<div class="modal fade" id="nuevaHabitacionModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Nueva Habitación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Tipo de Habitación *</label>
                                <select class="form-select" name="tipo_habitacion" required>
                                    <option value="">Seleccionar tipo</option>
                                    <option value="Sencilla">Sencilla</option>
                                    <option value="Doble">Doble</option>
                                    <option value="Triple">Triple</option>
                                    <option value="Suite">Suite</option>
                                    <option value="Suite Ejecutiva">Suite Ejecutiva</option>
                                    <option value="Suite Presidencial">Suite Presidencial</option>
                                    <option value="Familiar">Familiar</option>
                                    <option value="Junior Suite">Junior Suite</option>
                                    <option value="Estándar">Estándar</option>
                                    <option value="Superior">Superior</option>
                                    <option value="Deluxe">Deluxe</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Capacidad (personas) *</label>
                                <select class="form-select" name="capacidad" required>
                                    <option value="1">1 persona</option>
                                    <option value="2" selected>2 personas</option>
                                    <option value="3">3 personas</option>
                                    <option value="4">4 personas</option>
                                    <option value="5">5 personas</option>
                                    <option value="6">6 personas</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Precio por Noche *</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control" name="precio_noche" 
                                           step="0.01" min="1" required placeholder="0.00">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Moneda *</label>
                                <select class="form-select" name="moneda" required>
                                    <option value="USD" selected>USD - Dólar Americano</option>
                                    <option value="EUR">EUR - Euro</option>
                                    <option value="GBP">GBP - Libra Esterlina</option>
                                    <option value="MXN">MXN - Peso Mexicano</option>
                                    <option value="BRL">BRL - Real Brasileño</option>
                                    <option value="COP">COP - Peso Colombiano</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Número de Habitación (opcional)</label>
                        <input type="text" class="form-control" name="numero_habitacion" 
                               placeholder="Ej: 101, 202-A, etc.">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Descripción</label>
                        <textarea class="form-control" name="descripcion" rows="2" 
                                  placeholder="Descripción general de la habitación..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amenities y Características</label>
                        <textarea class="form-control" name="caracteristicas" rows="3" 
                                  placeholder="WiFi, TV, Aire acondicionado, Baño privado, Mini bar, etc."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-civit-primary">Guardar Habitación</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Lista de Habitaciones -->
<div class="card card-civit">
    <div class="card-header card-header-civit d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Tipos de Habitación Disponibles</h5>
        <span class="badge bg-primary"><?php echo count($habitaciones); ?> habitaciones</span>
    </div>
    <div class="card-body">
        <?php if (count($habitaciones) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tipo</th>
                            <th>Número</th>
                            <th>Precio/Noche</th>
                            <th>Capacidad</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($habitaciones as $habitacion): ?>
                            <tr>
                                <td class="fw-bold"><?php echo htmlspecialchars($habitacion['tipo_habitacion']); ?></td>
                                <td>
                                    <?php if (!empty($habitacion['numero_habitacion'])): ?>
                                        <span class="badge bg-secondary"><?php echo htmlspecialchars($habitacion['numero_habitacion']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php
                                        require_once __DIR__ . '/php/currency-helpers.php';
                                        echo formatearPrecio($habitacion['precio_noche'], $habitacion['moneda']);
                                    ?></strong>
                                    <br>
                                    <small class="text-muted">
                                        ≈ <?php 
                                        $precioUSD = convertirMoneda($habitacion['precio_noche'], $habitacion['moneda'], 'USD');
                                        echo formatearPrecio($precioUSD, 'USD');
                                        ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge bg-primary">
                                        <?php echo $habitacion['capacidad']; ?> personas
                                    </span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $habitacion['disponible'] ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo $habitacion['disponible'] ? 'Disponible' : 'No disponible'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-outline-danger" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                <h5>No hay habitaciones registradas</h5>
                <p class="text-muted">Agrega los diferentes tipos de habitación para este hotel</p>
                <button type="button" class="btn btn-civit-primary" data-bs-toggle="modal" data-bs-target="#nuevaHabitacionModal">
                    <i class="fas fa-plus me-1"></i> Agregar Primera Habitación
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>