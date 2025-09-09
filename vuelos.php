<?php
session_start();
require_once __DIR__ . '/php/auth.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar permisos para módulo de vuelos
verificarPermisos(ROLES_VENDEDOR);

// Incluir la base de datos con ruta absoluta
require_once __DIR__ . '/php/database.php';

// Verificar si la conexión se estableció
if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos");
}

$page_title = "Boletos Aéreos";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Obtener cotizaciones de vuelos
$vuelos = [];
if (isset($conn)) {
    if ($stmt = $conn->prepare("
        SELECT c.*, cl.nombre as cliente_nombre, cl.email as cliente_email, v.aerolinea, v.origen, v.destino
        FROM cotizaciones c 
        LEFT JOIN clientes cl ON c.cliente_id = cl.id 
        LEFT JOIN vuelos v ON c.id = v.cotizacion_id
        WHERE c.servicio LIKE '%vuelo%' OR v.id IS NOT NULL
        ORDER BY c.fecha_creacion DESC
    ")) {
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $vuelos[] = $row;
        }
        $stmt->close();
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Cotización de Boletos Aéreos</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="nuevo-vuelo.php" class="btn btn-sm btn-civit-primary">
            <i class="fas fa-plus me-1"></i> Nueva Cotización de Vuelo
        </a>
    </div>
</div>

<!-- Estadísticas de vuelos -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-stat-1 text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h6 class="fw-bold">Vuelos Cotizados</h6>
                        <h4 class="card-title mb-0"><?php echo count($vuelos); ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-plane fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-stat-2 text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h6 class="fw-bold">Confirmados</h6>
                        <h4 class="card-title mb-0"><?php 
                            $confirmados = 0;
                            foreach ($vuelos as $vuelo) {
                                if ($vuelo['estado'] == 'confirmada') $confirmados++;
                            }
                            echo $confirmados; 
                        ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-check-circle fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-stat-3 text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h6 class="fw-bold">Pendientes</h6>
                        <h4 class="card-title mb-0"><?php 
                            $pendientes = 0;
                            foreach ($vuelos as $vuelo) {
                                if ($vuelo['estado'] == 'pendiente') $pendientes++;
                            }
                            echo $pendientes; 
                        ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card stat-card bg-stat-4 text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h6 class="fw-bold">Ingresos Vuelos</h6>
                        <h4 class="card-title mb-0">$<?php 
                            $total = 0;
                            foreach ($vuelos as $vuelo) {
                                $total += $vuelo['total'];
                            }
                            echo number_format($total, 2); 
                        ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Lista de cotizaciones de vuelos -->
<div class="card card-civit">
    <div class="card-header card-header-civit d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Cotizaciones de Vuelos</h5>
        <div class="d-flex">
            <input type="text" class="form-control form-control-sm me-2" placeholder="Buscar..." id="searchInput">
            <select class="form-select form-select-sm" style="width: auto;" id="statusFilter">
                <option value="">Todos los estados</option>
                <option value="confirmada">Confirmadas</option>
                <option value="pendiente">Pendientes</option>
                <option value="rechazada">Rechazadas</option>
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover" id="vuelosTable">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th>Origen - Destino</th>
                        <th>Aerolínea</th>
                        <th>Fecha Salida</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($vuelos) > 0): ?>
                        <?php foreach ($vuelos as $vuelo): ?>
                            <tr>
                                <td><?php echo $vuelo['codigo']; ?></td>
                                <td>
                                    <div><?php echo $vuelo['cliente_nombre']; ?></div>
                                    <small class="text-muted"><?php echo $vuelo['cliente_email']; ?></small>
                                </td>
                                <td>
                                    <?php if (!empty($vuelo['origen']) && !empty($vuelo['destino'])): ?>
                                        <?php echo $vuelo['origen'] . ' - ' . $vuelo['destino']; ?>
                                    <?php else: ?>
                                        <span class="text-muted">Sin detalles</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $vuelo['aerolinea'] ?? 'N/A'; ?></td>
                                <td>
                                    <?php if (!empty($vuelo['fecha_emision'])): ?>
                                        <?php echo date('d/m/Y', strtotime($vuelo['fecha_emision'])); ?>
                                    <?php endif; ?>
                                </td>
                                <td>$<?php echo number_format($vuelo['total'], 2); ?></td>
                                <td>
                                    <?php 
                                    $badge_class = '';
                                    switch ($vuelo['estado']) {
                                        case 'confirmada': $badge_class = 'badge-confirmed'; break;
                                        case 'pendiente': $badge_class = 'badge-pending'; break;
                                        case 'rechazada': $badge_class = 'badge-rejected'; break;
                                        default: $badge_class = 'badge-secondary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($vuelo['estado']); ?></span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="editar-vuelo.php?id=<?php echo $vuelo['id']; ?>" class="btn btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="#" class="btn btn-outline-success" title="Generar PDF">
                                            <i class="fas fa-file-pdf"></i>
                                        </a>
                                        <button class="btn btn-outline-info" title="Cambiar Estado" onclick="cambiarEstado(<?php echo $vuelo['id']; ?>)">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-plane fa-3x text-muted mb-3"></i>
                                <h5>No hay cotizaciones de vuelos</h5>
                                <p class="text-muted">Comienza creando tu primera cotización de vuelo</p>
                                <a href="nuevo-vuelo.php" class="btn btn-civit-primary">Crear Cotización</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal para Cambiar Estado -->
<div class="modal fade" id="estadoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambiar Estado de Cotización</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="estadoForm">
                    <input type="hidden" id="cotizacionId" name="cotizacion_id">
                    <div class="mb-3">
                        <label for="nuevoEstado" class="form-label">Nuevo Estado</label>
                        <select class="form-select" id="nuevoEstado" name="nuevo_estado" required>
                            <option value="pendiente">Pendiente</option>
                            <option value="confirmada">Confirmada</option>
                            <option value="rechazada">Rechazada</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="notasEstado" class="form-label">Notas</label>
                        <textarea class="form-control" id="notasEstado" name="notas" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-civit-primary" onclick="guardarEstado()">Guardar Cambios</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
// Filtrar tabla
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const statusFilter = document.getElementById('statusFilter');
    const table = document.getElementById('vuelosTable');
    const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');
    
    function filterTable() {
        const searchText = searchInput.value.toLowerCase();
        const statusValue = statusFilter.value;
        
        for (let i = 0; i < rows.length; i++) {
            const cells = rows[i].getElementsByTagName('td');
            let showRow = true;
            
            if (searchText) {
                let rowText = '';
                for (let j = 0; j < cells.length; j++) {
                    rowText += cells[j].textContent.toLowerCase() + ' ';
                }
                if (!rowText.includes(searchText)) {
                    showRow = false;
                }
            }
            
            if (statusValue && showRow) {
                const statusCell = cells[6]; // Columna de estado
                if (!statusCell.textContent.toLowerCase().includes(statusValue)) {
                    showRow = false;
                }
            }
            
            rows[i].style.display = showRow ? '' : 'none';
        }
    }
    
    searchInput.addEventListener('input', filterTable);
    statusFilter.addEventListener('change', filterTable);
});

// Cambiar estado
function cambiarEstado(id) {
    document.getElementById('cotizacionId').value = id;
    const modal = new bootstrap.Modal(document.getElementById('estadoModal'));
    modal.show();
}

// Guardar estado con fetch API
function guardarEstado() {
    const formData = new FormData(document.getElementById('estadoForm'));
    
    fetch('php/actualizar-estado.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Cerrar modal y recargar página
            const modal = bootstrap.Modal.getInstance(document.getElementById('estadoModal'));
            modal.hide();
            location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error al actualizar el estado');
    });
}
</script>