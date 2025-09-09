<?php
// Iniciar sesión primero
session_start();

// Incluir la base de datos con ruta absoluta
require_once __DIR__ . '/php/database.php';
require_once __DIR__ . '/php/auth.php';

// Verificar si la conexión se estableció
if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos");
}

// Verificar permisos (solo admin y superadmin)
verificarAutenticacion();
verificarPermisos(ROLES_ADMIN);

$page_title = "Reportes y Estadísticas";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Obtener parámetros de filtro
$fecha_inicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : date('Y-m-01');
$fecha_fin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : date('Y-m-t');
$tipo_reporte = isset($_GET['tipo_reporte']) ? $_GET['tipo_reporte'] : 'ventas';

// Estadísticas generales
$estadisticas = [
    'total_ventas' => 0,
    'total_cotizaciones' => 0,
    'cotizaciones_confirmadas' => 0,
    'tasa_conversion' => 0,
    'ventas_por_vendedor' => [],
    'ventas_por_servicio' => []
];

// Obtener total de ventas en el período
if ($stmt = $conn->prepare("
    SELECT SUM(total) as total_ventas 
    FROM cotizaciones 
    WHERE estado = 'confirmada' 
    AND fecha_emision BETWEEN ? AND ?
")) {
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    $estadisticas['total_ventas'] = $result->fetch_assoc()['total_ventas'] ?? 0;
    $stmt->close();
}

// Obtener total de cotizaciones
if ($stmt = $conn->prepare("
    SELECT COUNT(*) as total_cotizaciones 
    FROM cotizaciones 
    WHERE fecha_emision BETWEEN ? AND ?
")) {
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    $estadisticas['total_cotizaciones'] = $result->fetch_assoc()['total_cotizaciones'] ?? 0;
    $stmt->close();
}

// Obtener cotizaciones confirmadas
if ($stmt = $conn->prepare("
    SELECT COUNT(*) as confirmadas 
    FROM cotizaciones 
    WHERE estado = 'confirmada' 
    AND fecha_emision BETWEEN ? AND ?
")) {
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    $confirmadas = $result->fetch_assoc()['confirmadas'] ?? 0;
    $stmt->close();
    
    // Calcular tasa de conversión
    if ($estadisticas['total_cotizaciones'] > 0) {
        $estadisticas['tasa_conversion'] = round(($confirmadas / $estadisticas['total_cotizaciones']) * 100, 2);
    }
}

// Ventas por vendedor
if ($stmt = $conn->prepare("
    SELECT u.nombre as vendedor, COUNT(c.id) as total_ventas, SUM(c.total) as monto_total
    FROM cotizaciones c
    INNER JOIN usuarios u ON c.usuario_id = u.id
    WHERE c.estado = 'confirmada' 
    AND c.fecha_emision BETWEEN ? AND ?
    GROUP BY u.id
    ORDER BY monto_total DESC
")) {
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $estadisticas['ventas_por_vendedor'][] = $row;
    }
    $stmt->close();
}

// Ventas por tipo de servicio
if ($stmt = $conn->prepare("
    SELECT 
        CASE 
            WHEN servicio LIKE '%vuelo%' THEN 'Vuelos'
            WHEN servicio LIKE '%hotel%' THEN 'Hoteles'
            WHEN servicio LIKE '%paquete%' THEN 'Paquetes'
            WHEN servicio LIKE '%crucero%' THEN 'Cruceros'
            ELSE 'Otros'
        END as tipo_servicio,
        COUNT(*) as total_ventas,
        SUM(total) as monto_total
    FROM cotizaciones 
    WHERE estado = 'confirmada' 
    AND fecha_emision BETWEEN ? AND ?
    GROUP BY tipo_servicio
    ORDER BY monto_total DESC
")) {
    $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $estadisticas['ventas_por_servicio'][] = $row;
    }
    $stmt->close();
}

// Tendencia mensual (últimos 6 meses)
$tendencia_mensual = [];
for ($i = 5; $i >= 0; $i--) {
    $mes = date('Y-m', strtotime("-$i months"));
    $mes_nombre = date('M Y', strtotime("-$i months"));
    
    if ($stmt = $conn->prepare("
        SELECT SUM(total) as ventas_mes
        FROM cotizaciones 
        WHERE estado = 'confirmada' 
        AND DATE_FORMAT(fecha_emision, '%Y-%m') = ?
    ")) {
        $stmt->bind_param("s", $mes);
        $stmt->execute();
        $result = $stmt->get_result();
        $ventas = $result->fetch_assoc()['ventas_mes'] ?? 0;
        $stmt->close();
        
        $tendencia_mensual[] = [
            'mes' => $mes_nombre,
            'ventas' => $ventas
        ];
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Reportes y Estadísticas</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-outline-secondary me-2" onclick="window.print()">
            <i class="fas fa-print me-1"></i> Imprimir
        </button>
        <button class="btn btn-sm btn-civit-primary" onclick="exportarReporte()">
            <i class="fas fa-file-export me-1"></i> Exportar
        </button>
    </div>
</div>

<!-- Filtros de Reportes -->
<div class="card card-civit mb-4">
    <div class="card-header card-header-civit">
        <h5 class="mb-0">Filtros del Reporte</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="reportes.php">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
                    <input type="date" class="form-control" id="fecha_inicio" name="fecha_inicio" 
                           value="<?php echo $fecha_inicio; ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="fecha_fin" class="form-label">Fecha Fin</label>
                    <input type="date" class="form-control" id="fecha_fin" name="fecha_fin" 
                           value="<?php echo $fecha_fin; ?>" required>
                </div>
                <div class="col-md-3">
                    <label for="tipo_reporte" class="form-label">Tipo de Reporte</label>
                    <select class="form-select" id="tipo_reporte" name="tipo_reporte">
                        <option value="ventas" <?php echo $tipo_reporte == 'ventas' ? 'selected' : ''; ?>>Ventas</option>
                        <option value="cotizaciones" <?php echo $tipo_reporte == 'cotizaciones' ? 'selected' : ''; ?>>Cotizaciones</option>
                        <option value="clientes" <?php echo $tipo_reporte == 'clientes' ? 'selected' : ''; ?>>Clientes</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-civit-primary">
                            <i class="fas fa-filter me-1"></i> Aplicar Filtros
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resumen Estadístico -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-stat-1 text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h6 class="fw-bold">Ventas Totales</h6>
                        <h4 class="card-title mb-0">$<?php echo number_format($estadisticas['total_ventas'], 2); ?></h4>
                        <small class="opacity-75">Período seleccionado</small>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-dollar-sign fa-2x opacity-50"></i>
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
                        <h6 class="fw-bold">Cotizaciones</h6>
                        <h4 class="card-title mb-0"><?php echo $estadisticas['total_cotizaciones']; ?></h4>
                        <small class="opacity-75">Total generadas</small>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-file-invoice fa-2x opacity-50"></i>
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
                        <h6 class="fw-bold">Tasa de Conversión</h6>
                        <h4 class="card-title mb-0"><?php echo $estadisticas['tasa_conversion']; ?>%</h4>
                        <small class="opacity-75">De cotización a venta</small>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-chart-line fa-2x opacity-50"></i>
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
                        <h6 class="fw-bold">Período</h6>
                        <h4 class="card-title mb-0"><?php echo date('d M', strtotime($fecha_inicio)) . ' - ' . date('d M Y', strtotime($fecha_fin)); ?></h4>
                        <small class="opacity-75">Fecha del reporte</small>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-calendar-alt fa-2x opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Gráficos y Reportes -->
<div class="row">
    <!-- Tendencia de Ventas -->
    <div class="col-md-8">
        <div class="card card-civit mb-4">
            <div class="card-header card-header-civit">
                <h5 class="mb-0">Tendencia de Ventas (Últimos 6 Meses)</h5>
            </div>
            <div class="card-body">
                <canvas id="tendenciaVentasChart" height="250"></canvas>
            </div>
        </div>
    </div>

    <!-- Distribución por Servicio -->
    <div class="col-md-4">
        <div class="card card-civit mb-4">
            <div class="card-header card-header-civit">
                <h5 class="mb-0">Ventas por Tipo de Servicio</h5>
            </div>
            <div class="card-body">
                <canvas id="serviciosChart" height="250"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Tablas Detalladas -->
<div class="row">
    <!-- Top Vendedores -->
    <div class="col-md-6">
        <div class="card card-civit mb-4">
            <div class="card-header card-header-civit">
                <h5 class="mb-0">Top Vendedores</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Vendedor</th>
                                <th>Ventas</th>
                                <th>Monto</th>
                                <th>% Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estadisticas['ventas_por_vendedor'] as $vendedor): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($vendedor['vendedor']); ?></td>
                                    <td><?php echo $vendedor['total_ventas']; ?></td>
                                    <td>$<?php echo number_format($vendedor['monto_total'], 2); ?></td>
                                    <td>
                                        <?php 
                                        $porcentaje = $estadisticas['total_ventas'] > 0 
                                            ? round(($vendedor['monto_total'] / $estadisticas['total_ventas']) * 100, 1) 
                                            : 0;
                                        echo $porcentaje . '%';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Ventas por Servicio -->
    <div class="col-md-6">
        <div class="card card-civit mb-4">
            <div class="card-header card-header-civit">
                <h5 class="mb-0">Ventas por Servicio</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Servicio</th>
                                <th>Ventas</th>
                                <th>Monto</th>
                                <th>% Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estadisticas['ventas_por_servicio'] as $servicio): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($servicio['tipo_servicio']); ?></td>
                                    <td><?php echo $servicio['total_ventas']; ?></td>
                                    <td>$<?php echo number_format($servicio['monto_total'], 2); ?></td>
                                    <td>
                                        <?php 
                                        $porcentaje = $estadisticas['total_ventas'] > 0 
                                            ? round(($servicio['monto_total'] / $estadisticas['total_ventas']) * 100, 1) 
                                            : 0;
                                        echo $porcentaje . '%';
                                        ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reporte Detallado -->
<div class="card card-civit">
    <div class="card-header card-header-civit">
        <h5 class="mb-0">Reporte Detallado de Ventas</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped" id="tablaReporte">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Servicio</th>
                        <th>Vendedor</th>
                        <th>Estado</th>
                        <th class="text-end">Monto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Obtener ventas detalladas
                    $ventas_detalladas = [];
                    if ($stmt = $conn->prepare("
                        SELECT c.fecha_emision, c.servicio, c.total, c.estado,
                               cl.nombre as cliente, u.nombre as vendedor
                        FROM cotizaciones c
                        LEFT JOIN clientes cl ON c.cliente_id = cl.id
                        LEFT JOIN usuarios u ON c.usuario_id = u.id
                        WHERE c.fecha_emision BETWEEN ? AND ?
                        ORDER BY c.fecha_emision DESC
                        LIMIT 100
                    ")) {
                        $stmt->bind_param("ss", $fecha_inicio, $fecha_fin);
                        $stmt->execute();
                        $result = $stmt->get_result();
                        while ($row = $result->fetch_assoc()) {
                            $ventas_detalladas[] = $row;
                        }
                        $stmt->close();
                    }

                    foreach ($ventas_detalladas as $venta):
                    ?>
                        <tr>
                            <td><?php echo date('d/m/Y', strtotime($venta['fecha_emision'])); ?></td>
                            <td><?php echo htmlspecialchars($venta['cliente']); ?></td>
                            <td><?php echo htmlspecialchars($venta['servicio']); ?></td>
                            <td><?php echo htmlspecialchars($venta['vendedor']); ?></td>
                            <td>
                                <span class="badge <?php echo $venta['estado'] == 'confirmada' ? 'bg-success' : 'bg-warning'; ?>">
                                    <?php echo ucfirst($venta['estado']); ?>
                                </span>
                            </td>
                            <td class="text-end">$<?php echo number_format($venta['total'], 2); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="fw-bold">
                        <td colspan="5" class="text-end">Total:</td>
                        <td class="text-end">$<?php echo number_format($estadisticas['total_ventas'], 2); ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Gráfico de Tendencia de Ventas
const tendenciaCtx = document.getElementById('tendenciaVentasChart').getContext('2d');
const tendenciaChart = new Chart(tendenciaCtx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($tendencia_mensual, 'mes')); ?>,
        datasets: [{
            label: 'Ventas Mensuales',
            data: <?php echo json_encode(array_column($tendencia_mensual, 'ventas')); ?>,
            borderColor: '#3498db',
            backgroundColor: 'rgba(52, 152, 219, 0.1)',
            borderWidth: 2,
            fill: true,
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '$' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Gráfico de Servicios
const serviciosCtx = document.getElementById('serviciosChart').getContext('2d');
const serviciosChart = new Chart(serviciosCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_column($estadisticas['ventas_por_servicio'], 'tipo_servicio')); ?>,
        datasets: [{
            data: <?php echo json_encode(array_column($estadisticas['ventas_por_servicio'], 'monto_total')); ?>,
            backgroundColor: [
                '#3498db', '#2ecc71', '#e74c3c', '#f39c12', '#9b59b6', '#34495e'
            ]
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'bottom'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const value = context.raw;
                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                        const percentage = Math.round((value / total) * 100);
                        return `$${value.toLocaleString()} (${percentage}%)`;
                    }
                }
            }
        }
    }
});

// Función para exportar reporte
function exportarReporte() {
    // Crear tabla HTML para exportar
    const tabla = document.getElementById('tablaReporte');
    const html = tabla.outerHTML;
    
    // Crear blob y descargar
    const blob = new Blob([html], { type: 'application/vnd.ms-excel' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `reporte-ventas-${new Date().toISOString().split('T')[0]}.xls`;
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>