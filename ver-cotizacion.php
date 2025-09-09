<?php
session_start();
require_once __DIR__ . '/php/database.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/cotizaciones-crud.php';
require_once __DIR__ . '/php/currency-helpers.php';

verificarAutenticacion();
verificarPermisos(ROLES_VENDEDOR);

$cotizacionesCRUD = new CotizacionesCRUD($conn);

// Obtener ID de la cotización
$cotizacion_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($cotizacion_id === 0) {
    header("Location: cotizaciones.php");
    exit;
}

// Obtener datos de la cotización
$cotizacion = $cotizacionesCRUD->obtenerCotizacion($cotizacion_id);
$servicios = $cotizacionesCRUD->obtenerServicios($cotizacion_id);

if (!$cotizacion) {
    header("Location: cotizaciones.php");
    exit;
}

$page_title = "Cotización " . $cotizacion['codigo'];
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Cotización: <small class="text-muted"><?php echo $cotizacion['codigo']; ?></small></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="cotizaciones.php" class="btn btn-sm btn-outline-secondary me-2">
            <i class="fas fa-arrow-left me-1"></i> Volver
        </a>
        <a href="generar-pdf-cotizacion.php?id=<?php echo $cotizacion_id; ?>" class="btn btn-sm btn-civit-primary me-2" target="_blank">
            <i class="fas fa-file-pdf me-1"></i> Generar PDF
        </a>
        <div class="btn-group">
            <button type="button" class="btn btn-sm btn-outline-success dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-cog me-1"></i> Acciones
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="#" onclick="cambiarEstado(<?php echo $cotizacion_id; ?>, 'aprobada')">
                    <i class="fas fa-check text-success me-2"></i> Marcar como Aprobada
                </a></li>
                <li><a class="dropdown-item" href="#" onclick="cambiarEstado(<?php echo $cotizacion_id; ?>, 'rechazada')">
                    <i class="fas fa-times text-danger me-2"></i> Marcar como Rechazada
                </a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="#">
                    <i class="fas fa-trash me-2"></i> Eliminar Cotización
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Estado de la cotización -->
<div class="row mb-4">
    <div class="col-md-12">
        <div class="alert alert-<?php 
            echo $cotizacion['estado'] === 'aprobada' ? 'success' : 
                   ($cotizacion['estado'] === 'pendiente' ? 'warning' : 'danger'); 
        ?>">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <strong>Estado:</strong> 
                    <span class="text-uppercase"><?php echo $cotizacion['estado']; ?></span>
                    <?php if ($cotizacion['estado'] === 'pendiente'): ?>
                        <span class="badge bg-warning ms-2">Válida hasta: <?php echo date('d/m/Y', strtotime($cotizacion['fecha_vigencia'])); ?></span>
                    <?php endif; ?>
                </div>
                <div>
                    <strong>Fecha creación:</strong> <?php echo date('d/m/Y H:i', strtotime($cotizacion['fecha_creacion'])); ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-8">
        <!-- Información del Cliente -->
        <div class="card card-civit mb-4">
            <div class="card-header card-header-civit">
                <h5 class="mb-0">Información del Cliente</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Nombre:</strong><br><?php echo htmlspecialchars($cotizacion['cliente_nombre']); ?></p>
                        <p><strong>Email:</strong><br><?php echo htmlspecialchars($cotizacion['cliente_email']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Teléfono:</strong><br><?php echo htmlspecialchars($cotizacion['cliente_telefono']); ?></p>
                        <p><strong>Documento:</strong><br><?php echo htmlspecialchars($cotizacion['cliente_documento']); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Servicios de la Cotización -->
        <div class="card card-civit">
            <div class="card-header card-header-civit">
                <h5 class="mb-0">Servicios Cotizados</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Servicio</th>
                                <th class="text-center">Cantidad</th>
                                <th class="text-end">Precio Unitario</th>
                                <th class="text-end">Subtotal</th>
                            </tr>
                        </thead>
<tbody>
    <?php foreach ($servicios as $servicio): ?>
        <tr>
            <td>
                <div>
                    <strong><?php echo htmlspecialchars($servicio['detalles']); ?></strong>
                    <br>
                    <small class="text-muted text-uppercase">
                        <?php echo $servicio['tipo_servicio']; ?>
                    </small>
                </div>
            </td>
            <td class="text-center"><?php echo $servicio['cantidad']; ?></td>
            <td class="text-end">
                <?php echo formatearPrecio($servicio['precio'], $cotizacion['moneda']); ?>
            </td>
            <td class="text-end">
                <strong><?php echo formatearPrecio($servicio['subtotal'], $cotizacion['moneda']); ?></strong>
            </td>
        </tr>
    <?php endforeach; ?>
</tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Notas -->
        <?php if (!empty($cotizacion['notas'])): ?>
        <div class="card card-civit mt-4">
            <div class="card-header card-header-civit">
                <h5 class="mb-0">Notas Adicionales</h5>
            </div>
            <div class="card-body">
                <p class="mb-0"><?php echo nl2br(htmlspecialchars($cotizacion['notas'])); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <div class="col-md-4">
        <!-- Resumen de Cotización -->
        <div class="card card-civit mb-4">
            <div class="card-header card-header-civit">
                <h5 class="mb-0">Resumen de Cotización</h5>
            </div>
            <div class="card-body">
    <div class="d-flex justify-content-between mb-2">
        <span>Subtotal:</span>
        <span><?php echo formatearPrecio($cotizacion['subtotal'], $cotizacion['moneda']); ?></span>
    </div>
    <div class="d-flex justify-content-between mb-2">
        <span>Impuestos (13%):</span>
        <span><?php echo formatearPrecio($cotizacion['impuestos'], $cotizacion['moneda']); ?></span>
    </div>
    <hr>
    <div class="d-flex justify-content-between fw-bold fs-5">
        <span>Total:</span>
        <span><?php echo formatearPrecio($cotizacion['total'], $cotizacion['moneda']); ?></span>
    </div>
    
    <div class="mt-3 p-3 bg-light rounded">
        <small class="text-muted">
            <i class="fas fa-info-circle me-1"></i>
            Cotización válida hasta: <strong><?php echo date('d/m/Y', strtotime($cotizacion['fecha_vigencia'])); ?></strong>
        </small>
    </div>
</div>
        </div>

        <!-- Información de Vendedor -->
        <div class="card card-civit">
            <div class="card-header card-header-civit">
                <h5 class="mb-0">Información del Vendedor</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Código:</strong> <?php echo $cotizacion['codigo']; ?></p>
<p class="mb-1"><strong>Generada por:</strong> <?php echo $_SESSION['usuario_nombre']; ?></p>
<p class="mb-0"><strong>Fecha:</strong> <?php echo date('d/m/Y H:i', strtotime($cotizacion['fecha_creacion'])); ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
function cambiarEstado(cotizacionId, estado) {
    if (confirm('¿Está seguro de cambiar el estado de esta cotización?')) {
        fetch('php/actualizar-estado-cotizacion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                id: cotizacionId,
                estado: estado
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            alert('Error al cambiar el estado');
        });
    }
}
</script>