<?php
// Iniciar sesión primero
session_start();

// Incluir la base de datos con ruta absoluta
require_once __DIR__ . '/php/database.php';

// Verificar si la conexión se estableció
if (!$conn) {
    die("Error: No se pudo conectar a la base de datos");
}

$page_title = "Dashboard";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Obtener estadísticas
$stats = [
    'total_cotizaciones' => 0,
    'total_clientes' => 0,
    'cotizaciones_pendientes' => 0,
    'ingresos_totales' => 0
];

// Verificar que $conn existe antes de usarlo
if (isset($conn)) {
    if ($stmt = $conn->prepare("SELECT COUNT(*) as total FROM cotizaciones")) {
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_cotizaciones'] = $result->fetch_assoc()['total'];
        $stmt->close();
    }

    if ($stmt = $conn->prepare("SELECT COUNT(*) as total FROM clientes")) {
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['total_clientes'] = $result->fetch_assoc()['total'];
        $stmt->close();
    }

    if ($stmt = $conn->prepare("SELECT COUNT(*) as total FROM cotizaciones WHERE estado = 'pendiente'")) {
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['cotizaciones_pendientes'] = $result->fetch_assoc()['total'];
        $stmt->close();
    }

    if ($stmt = $conn->prepare("SELECT SUM(total) as total FROM cotizaciones WHERE estado = 'confirmada'")) {
        $stmt->execute();
        $result = $stmt->get_result();
        $stats['ingresos_totales'] = $result->fetch_assoc()['total'] ?? 0;
        $stmt->close();
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Dashboard</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <button type="button" class="btn btn-sm btn-outline-secondary">Exportar</button>
            <button type="button" class="btn btn-sm btn-outline-secondary">Imprimir</button>
        </div>
        <a href="nuevo-vuelo.php" class="btn btn-sm btn-civit-primary">
            <i class="fas fa-plus me-1"></i> Nueva Cotización
        </a>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-stat-1 text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h6 class="fw-bold">Cotizaciones</h6>
                        <h4 class="card-title mb-0"><?php echo $stats['total_cotizaciones']; ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-file-invoice fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-dark bg-opacity-25 d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="cotizaciones.php">Ver detalles</a>
                <div class="small text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-stat-2 text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h6 class="fw-bold">Clientes</h6>
                        <h4 class="card-title mb-0"><?php echo $stats['total_clientes']; ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-users fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-dark bg-opacity-25 d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="clientes.php">Ver detalles</a>
                <div class="small text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-stat-3 text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h6 class="fw-bold">Pendientes</h6>
                        <h4 class="card-title mb-0"><?php echo $stats['cotizaciones_pendientes']; ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-clock fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-dark bg-opacity-25 d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="cotizaciones.php?estado=pendiente">Ver detalles</a>
                <div class="small text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card stat-card bg-stat-4 text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h6 class="fw-bold">Ingresos</h6>
                        <h4 class="card-title mb-0">$<?php echo number_format($stats['ingresos_totales'], 2); ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
            <div class="card-footer bg-dark bg-opacity-25 d-flex align-items-center justify-content-between">
                <a class="small text-white stretched-link" href="reportes.php">Ver detalles</a>
                <div class="small text-white"><i class="fas fa-arrow-right"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Cotizaciones Recientes -->
<div class="row">
    <div class="col-md-8">
        <div class="card card-civit mb-4">
            <div class="card-header card-header-civit">
                <h5 class="mb-0">Cotizaciones Recientes</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Fecha</th>
                                <th>Total</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cotizaciones_recientes = [];
                            if (isset($conn)) {
                                if ($stmt = $conn->prepare("
                                    SELECT c.*, cl.nombre as cliente_nombre 
                                    FROM cotizaciones c 
                                    LEFT JOIN clientes cl ON c.cliente_id = cl.id 
                                    ORDER BY c.fecha_creacion DESC 
                                    LIMIT 5
                                ")) {
                                    $stmt->execute();
                                    $result = $stmt->get_result();
                                    while ($row = $result->fetch_assoc()) {
                                        $cotizaciones_recientes[] = $row;
                                    }
                                    $stmt->close();
                                }
                            }
                            
                            if (count($cotizaciones_recientes) > 0):
                                foreach ($cotizaciones_recientes as $cotizacion):
                                    $badge_class = '';
                                    switch ($cotizacion['estado']) {
                                        case 'confirmada': $badge_class = 'badge-confirmed'; break;
                                        case 'pendiente': $badge_class = 'badge-pending'; break;
                                        case 'rechazada': $badge_class = 'badge-rejected'; break;
                                        default: $badge_class = 'badge-secondary';
                                    }
                            ?>
                                <tr>
                                    <td><?php echo $cotizacion['codigo']; ?></td>
                                    <td><?php echo $cotizacion['cliente_nombre'] ?? 'N/A'; ?></td>
                                    <td><?php echo $cotizacion['servicio']; ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($cotizacion['fecha_emision'])); ?></td>
                                    <td>$<?php echo number_format($cotizacion['total'], 2); ?></td>
                                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($cotizacion['estado']); ?></span></td>
                                </tr>
                            <?php 
                                endforeach;
                            else:
                            ?>
                                <tr>
                                    <td colspan="6" class="text-center py-4">
                                        <i class="fas fa-file-invoice fa-3x text-muted mb-3"></i>
                                        <h5>No hay cotizaciones</h5>
                                        <p class="text-muted">Comienza creando tu primera cotización</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="text-center mt-3">
                    <a href="cotizaciones.php" class="btn btn-civit-primary">Ver todas las cotizaciones</a>
                </div>
            </div>
        </div>

        <!-- Agregar esta sección en el dashboard principal -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="card card-civit">
            <div class="card-header card-header-civit">
                <h5 class="mb-0"><i class="fas fa-globe-americas me-2"></i>Destinos Internacionales Destacados</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-2 col-4 mb-3">
                        <img src="https://flagcdn.com/w80/us.png" width="40" height="40" class="mb-2" alt="USA">
                        <div class="small">Estados Unidos</div>
                        <small class="text-muted">15 hoteles</small>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <img src="https://flagcdn.com/w80/es.png" width="40" height="40" class="mb-2" alt="España">
                        <div class="small">España</div>
                        <small class="text-muted">8 hoteles</small>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <img src="https://flagcdn.com/w80/mx.png" width="40" height="40" class="mb-2" alt="México">
                        <div class="small">México</div>
                        <small class="text-muted">12 hoteles</small>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <img src="https://flagcdn.com/w80/it.png" width="40" height="40" class="mb-2" alt="Italia">
                        <div class="small">Italia</div>
                        <small class="text-muted">6 hoteles</small>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <img src="https://flagcdn.com/w80/th.png" width="40" height="40" class="mb-2" alt="Tailandia">
                        <div class="small">Tailandia</div>
                        <small class="text-muted">5 hoteles</small>
                    </div>
                    <div class="col-md-2 col-4 mb-3">
                        <img src="https://flagcdn.com/w80/cr.png" width="40" height="40" class="mb-2" alt="Costa Rica">
                        <div class="small">Costa Rica</div>
                        <small class="text-muted">7 hoteles</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    </div>
    <div class="col-md-4">
        <div class="card card-civit mb-4">
            <div class="card-header card-header-civit">
                <h5 class="mb-0">Acciones Rápidas</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="nuevo-vuelo.php" class="btn btn-civit-primary mb-2">
                        <i class="fas fa-plus me-2"></i> Nueva Cotización
                    </a>
                    <a href="nuevo-cliente.php" class="btn btn-outline-primary mb-2">
                        <i class="fas fa-user-plus me-2"></i> Agregar Cliente
                    </a>
                    <a href="vuelos.php" class="btn btn-outline-primary mb-2">
                        <i class="fas fa-plane me-2"></i> Cotizar Vuelo
                    </a>
                    <a href="reportes.php" class="btn btn-outline-primary mb-2">
                        <i class="fas fa-chart-bar me-2"></i> Generar Reporte
                    </a>
                </div>
            </div>
        </div>
        
        <div class="card card-civit">
            <div class="card-header card-header-civit">
                <h5 class="mb-0">Estado de Cotizaciones</h5>
            </div>
            <div class="card-body text-center">
                <canvas id="statusChart" width="100%" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<?php
require_once 'includes/footer.php';
?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Gráfico de estado de cotizaciones
    const ctx = document.getElementById('statusChart').getContext('2d');
    const statusChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Confirmadas', 'Pendientes', 'Rechazadas'],
            datasets: [{
                data: [
                    <?php 
                    $confirmadas = 0;
                    $pendientes = 0;
                    $rechazadas = 0;
                    
                    if (isset($conn)) {
                        if ($stmt = $conn->prepare("SELECT estado, COUNT(*) as total FROM cotizaciones GROUP BY estado")) {
                            $stmt->execute();
                            $result = $stmt->get_result();
                            while ($row = $result->fetch_assoc()) {
                                if ($row['estado'] == 'confirmada') $confirmadas = $row['total'];
                                if ($row['estado'] == 'pendiente') $pendientes = $row['total'];
                                if ($row['estado'] == 'rechazada') $rechazadas = $row['total'];
                            }
                            $stmt->close();
                        }
                    }
                    echo "$confirmadas, $pendientes, $rechazadas";
                    ?>
                ],
                backgroundColor: [
                    '#27ae60',
                    '#f39c12',
                    '#e74c3c'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        font: {
                            size: 12
                        }
                    }
                }
            },
            cutout: '70%'
        }
    });
});
</script>