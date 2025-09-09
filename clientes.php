<?php
session_start();
require_once __DIR__ . '/php/auth.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar permisos para módulo de clientes
verificarPermisos(ROLES_VENDEDOR);

// Incluir la base de datos con ruta absoluta
require_once __DIR__ . '/php/database.php';

// Verificar si la conexión se estableció
if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos");
}

$page_title = "Gestión de Clientes";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Obtener parámetros de búsqueda y filtro
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$tipo_documento = isset($_GET['tipo_documento']) ? trim($_GET['tipo_documento']) : '';

// Construir consulta base
$query = "SELECT * FROM clientes WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (nombre LIKE ? OR email LIKE ? OR telefono LIKE ? OR numero_documento LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term, $search_term]);
    $types .= "ssss";
}

if (!empty($tipo_documento)) {
    $query .= " AND tipo_documento = ?";
    $params[] = $tipo_documento;
    $types .= "s";
}

$query .= " ORDER BY nombre ASC";

// Obtener clientes
$clientes = [];
if ($stmt = $conn->prepare($query)) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $clientes[] = $row;
    }
    $stmt->close();
}

// Estadísticas
$total_clientes = count($clientes);
$clientes_activos = $total_clientes; // En una implementación real, tendrías un campo 'activo'
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Clientes</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="nuevo-cliente.php" class="btn btn-sm btn-civit-primary">
            <i class="fas fa-user-plus me-1"></i> Nuevo Cliente
        </a>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="card stat-card bg-stat-1 text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h6 class="fw-bold">Total de Clientes</h6>
                        <h4 class="card-title mb-0"><?php echo $total_clientes; ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card bg-stat-2 text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h6 class="fw-bold">Clientes Activos</h6>
                        <h4 class="card-title mb-0"><?php echo $clientes_activos; ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-user-check fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card stat-card bg-stat-3 text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h6 class="fw-bold">Cotizaciones Activas</h6>
                        <h4 class="card-title mb-0"><?php 
                            $cotizaciones_activas = 0;
                            if ($stmt = $conn->prepare("SELECT COUNT(*) as total FROM cotizaciones WHERE estado = 'pendiente'")) {
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $cotizaciones_activas = $result->fetch_assoc()['total'];
                                $stmt->close();
                            }
                            echo $cotizaciones_activas;
                        ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-file-invoice fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filtros de Búsqueda -->
<div class="card card-civit mb-4">
    <div class="card-header card-header-civit">
        <h5 class="mb-0">Filtros de Búsqueda</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="clientes.php">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Buscar cliente</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Nombre, email, teléfono o documento" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-4">
                    <label for="tipo_documento" class="form-label">Tipo de documento</label>
                    <select class="form-select" id="tipo_documento" name="tipo_documento">
                        <option value="">Todos los tipos</option>
                        <option value="dui" <?php echo $tipo_documento == 'dui' ? 'selected' : ''; ?>>DUI</option>
                        <option value="pasaporte" <?php echo $tipo_documento == 'pasaporte' ? 'selected' : ''; ?>>Pasaporte</option>
                        <option value="nit" <?php echo $tipo_documento == 'nit' ? 'selected' : ''; ?>>NIT</option>
                        <option value="otros" <?php echo $tipo_documento == 'otros' ? 'selected' : ''; ?>>Otros</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-civit-primary">
                            <i class="fas fa-search me-1"></i> Buscar
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de clientes -->
<div class="card card-civit">
    <div class="card-header card-header-civit d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lista de Clientes</h5>
        <span class="badge bg-secondary"><?php echo $total_clientes; ?> resultados</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Documento</th>
                        <th>Fecha Registro</th>
                        <th>Cotizaciones</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($clientes) > 0): ?>
                        <?php foreach ($clientes as $cliente): 
                            // Obtener número de cotizaciones para este cliente
                            $num_cotizaciones = 0;
                            if ($stmt = $conn->prepare("SELECT COUNT(*) as total FROM cotizaciones WHERE cliente_id = ?")) {
                                $stmt->bind_param("i", $cliente['id']);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                $num_cotizaciones = $result->fetch_assoc()['total'];
                                $stmt->close();
                            }
                        ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($cliente['nombre']); ?></div>
                                    <?php if (!empty($cliente['nacionalidad'])): ?>
                                        <small class="text-muted"><?php echo htmlspecialchars($cliente['nacionalidad']); ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($cliente['email']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($cliente['telefono']); ?></small>
                                </td>
                                <td>
                                    <div><?php echo strtoupper($cliente['tipo_documento']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($cliente['numero_documento']); ?></small>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($cliente['fecha_creacion'])); ?>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo $num_cotizaciones; ?> cotizaciones</span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="editar-cliente.php?id=<?php echo $cliente['id']; ?>" class="btn btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="cotizaciones.php?cliente_id=<?php echo $cliente['id']; ?>" class="btn btn-outline-info" title="Ver cotizaciones">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                        <button class="btn btn-outline-danger" title="Eliminar" onclick="confirmarEliminacion(<?php echo $cliente['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5>No hay clientes registrados</h5>
                                <p class="text-muted">Comienza agregando tu primer cliente</p>
                                <a href="nuevo-cliente.php" class="btn btn-civit-primary">Agregar Cliente</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_clientes > 0): ?>
        <nav aria-label="Page navigation example" class="mt-3">
            <ul class="pagination justify-content-center">
                <li class="page-item disabled">
                    <a class="page-link" href="#" tabindex="-1">Anterior</a>
                </li>
                <li class="page-item active"><a class="page-link" href="#">1</a></li>
                <li class="page-item"><a class="page-link" href="#">2</a></li>
                <li class="page-item"><a class="page-link" href="#">3</a></li>
                <li class="page-item">
                    <a class="page-link" href="#">Siguiente</a>
                </li>
            </ul>
        </nav>
        <?php endif; ?>
    </div>
</div>

<!-- Modal de confirmación de eliminación -->
<div class="modal fade" id="confirmarEliminacionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar este cliente?</p>
                <p class="text-danger"><strong>Advertencia:</strong> Esta acción no se puede deshacer.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarEliminacionBtn">Eliminar Cliente</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
let clienteIdAEliminar = null;

function confirmarEliminacion(id) {
    clienteIdAEliminar = id;
    const modal = new bootstrap.Modal(document.getElementById('confirmarEliminacionModal'));
    modal.show();
}

document.getElementById('confirmarEliminacionBtn').addEventListener('click', function() {
    if (clienteIdAEliminar) {
        // Aquí iría la llamada AJAX para eliminar el cliente
        alert('Función de eliminación será implementada para el cliente ID: ' + clienteIdAEliminar);
        $('#confirmarEliminacionModal').modal('hide');
    }
});

// Filtrar tabla localmente para búsqueda en tiempo real
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const table = document.querySelector('table');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    if (searchInput && rows.length > 0) {
        searchInput.addEventListener('input', function() {
            const searchText = this.value.toLowerCase();
            
            for (let i = 0; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName('td');
                let showRow = false;
                
                for (let j = 0; j < cells.length; j++) {
                    if (cells[j].textContent.toLowerCase().includes(searchText)) {
                        showRow = true;
                        break;
                    }
                }
                
                rows[i].style.display = showRow ? '' : 'none';
            }
        });
    }
});
</script>