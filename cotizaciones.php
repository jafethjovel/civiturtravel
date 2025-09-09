<?php
session_start();
require_once __DIR__ . '/php/database.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/cotizaciones-crud.php';

// Verificar permisos
verificarAutenticacion();
verificarPermisos(ROLES_VENDEDOR);

// Inicializar CRUD
$cotizacionesCRUD = new CotizacionesCRUD($conn);

// Obtener parámetros de filtro
$filtro_estado = $_GET['estado'] ?? '';
$filtro_desde = $_GET['desde'] ?? '';
$filtro_hasta = $_GET['hasta'] ?? '';
$filtro_cliente = $_GET['cliente'] ?? '';

// Preparar filtros
$filtros = [];
if (!empty($filtro_estado)) {
    $filtros['estado'] = $filtro_estado;
}
if (!empty($filtro_desde)) {
    $filtros['desde'] = $filtro_desde;
}
if (!empty($filtro_hasta)) {
    $filtros['hasta'] = $filtro_hasta;
}

// Obtener cotizaciones
$cotizaciones = $cotizacionesCRUD->listarCotizaciones($filtros);

// DEBUG: Ver qué se está obteniendo
echo "<!-- DEBUG: ";
echo "Número de cotizaciones: " . count($cotizaciones);
echo "Primera cotización: " . print_r($cotizaciones[0] ?? 'VACIO', true);
echo " -->";

// Filtrar por cliente si se especificó
if (!empty($filtro_cliente)) {
    $cotizaciones = array_filter($cotizaciones, function($cot) use ($filtro_cliente) {
        return stripos($cot['cliente_nombre'], $filtro_cliente) !== false;
    });
}

$page_title = "Gestión de Cotizaciones";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Cotizaciones</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="nueva-cotizacion.php" class="btn btn-sm btn-civit-primary">
            <i class="fas fa-plus me-1"></i> Nueva Cotización
        </a>
    </div>
</div>

<!-- Filtros -->
<div class="card card-civit mb-4">
    <div class="card-header card-header-civit">
        <h5 class="mb-0">Filtros de Búsqueda</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select class="form-select" name="estado">
                    <option value="">Todos los estados</option>
                    <option value="pendiente" <?php echo $filtro_estado == 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="aprobada" <?php echo $filtro_estado == 'aprobada' ? 'selected' : ''; ?>>Aprobada</option>
                    <option value="rechazada" <?php echo $filtro_estado == 'rechazada' ? 'selected' : ''; ?>>Rechazada</option>
                    <option value="vencida" <?php echo $filtro_estado == 'vencida' ? 'selected' : ''; ?>>Vencida</option>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Desde</label>
                <input type="date" class="form-control" name="desde" value="<?php echo $filtro_desde; ?>">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Hasta</label>
                <input type="date" class="form-control" name="hasta" value="<?php echo $filtro_hasta; ?>">
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Cliente</label>
                <input type="text" class="form-control" name="cliente" value="<?php echo htmlspecialchars($filtro_cliente); ?>" 
                       placeholder="Nombre del cliente">
            </div>
            
            <div class="col-12 text-end">
                <button type="submit" class="btn btn-civit-primary">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
                <a href="cotizaciones.php" class="btn btn-outline-secondary ms-2">
                    <i class="fas fa-undo me-1"></i> Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Estadísticas Rápidas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body text-center">
                <h5 class="card-title"><?php echo count($cotizaciones); ?></h5>
                <p class="card-text">Total Cotizaciones</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark">
            <div class="card-body text-center">
                <h5 class="card-title"><?php echo count(array_filter($cotizaciones, fn($c) => $c['estado'] === 'pendiente')); ?></h5>
                <p class="card-text">Pendientes</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white">
            <div class="card-body text-center">
                <h5 class="card-title"><?php echo count(array_filter($cotizaciones, fn($c) => $c['estado'] === 'aprobada')); ?></h5>
                <p class="card-text">Aprobadas</p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-danger text-white">
            <div class="card-body text-center">
                <h5 class="card-title"><?php echo count(array_filter($cotizaciones, fn($c) => $c['estado'] === 'rechazada')); ?></h5>
                <p class="card-text">Rechazadas</p>
            </div>
        </div>
    </div>
</div>

<!-- Lista de Cotizaciones -->
<div class="card card-civit">
    <div class="card-header card-header-civit d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Listado de Cotizaciones</h5>
        <span class="badge bg-secondary"><?php echo count($cotizaciones); ?> encontradas</span>
    </div>
    <div class="card-body">
        <?php if (count($cotizaciones) > 0): ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Cliente</th>
                            <th>Fecha Creación</th>
                            <th>Validez</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Vendedor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cotizaciones as $cotizacion): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($cotizacion['codigo_cotizacion'] ?? 'N/A'); ?></strong>
                                </td>
                                <td>
                                    <div><?php echo htmlspecialchars($cotizacion['cliente_nombre'] ?? 'N/A'); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($cotizacion['cliente_email'] ?? ''); ?></small>
                                </td>
                                <td>
                                    <?php echo isset($cotizacion['fecha_creacion']) ? date('d/m/Y', strtotime($cotizacion['fecha_creacion'])) : 'N/A'; ?>
                                    <br>
                                    <small class="text-muted"><?php echo isset($cotizacion['fecha_creacion']) ? date('H:i', strtotime($cotizacion['fecha_creacion'])) : ''; ?></small>
                                </td>
                                <td>
                                    <?php echo isset($cotizacion['fecha_validez']) ? date('d/m/Y', strtotime($cotizacion['fecha_validez'])) : 'N/A'; ?>
                                    <?php if (isset($cotizacion['fecha_validez']) && strtotime($cotizacion['fecha_validez']) < time() && ($cotizacion['estado'] ?? '') === 'pendiente'): ?>
                                        <br>
                                        <span class="badge bg-danger">Vencida</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo isset($cotizacion['total']) ? number_format($cotizacion['total'], 2) : '0.00'; ?> <?php echo $cotizacion['moneda'] ?? 'USD'; ?></strong>
                                </td>
                                <td>
                                    <?php if (isset($cotizacion['estado'])): ?>
                                        <span class="badge bg-<?php 
                                            echo $cotizacion['estado'] === 'aprobada' ? 'success' : 
                                                   ($cotizacion['estado'] === 'pendiente' ? 'warning' : 
                                                   ($cotizacion['estado'] === 'rechazada' ? 'danger' : 'secondary')); 
                                        ?>">
                                            <?php echo strtoupper($cotizacion['estado']); ?>
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-secondary">N/A</span>
                                    <?php endif; ?>
                                </td>
                                <td>
    <?php echo htmlspecialchars($cotizacion['vendedor_nombre'] ?? 'N/A'); ?>
</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if (isset($cotizacion['id'])): ?>
                                            <a href="ver-cotizacion.php?id=<?php echo $cotizacion['id']; ?>" class="btn btn-outline-primary" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="generar-pdf-cotizacion.php?id=<?php echo $cotizacion['id']; ?>" class="btn btn-outline-info" title="PDF" target="_blank">
                                                <i class="fas fa-file-pdf"></i>
                                            </a>
                                            <a href="editar-cotizacion.php?id=<?php echo $cotizacion['id']; ?>" class="btn btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-outline-secondary" disabled>
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                <h5>No hay cotizaciones registradas</h5>
                <p class="text-muted">Comienza creando tu primera cotización</p>
                <a href="nueva-cotizacion.php" class="btn btn-civit-primary">
                    <i class="fas fa-plus me-1"></i> Crear Cotización
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
// Función para actualizar estado automáticamente si está vencida
document.addEventListener('DOMContentLoaded', function() {
    // Aquí podrías agregar lógica para marcar automáticamente como vencidas
    // las cotizaciones pendientes que hayan pasado su fecha de validez
});
</script>