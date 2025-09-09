<?php
session_start();
require_once __DIR__ . '/php/database.php';
require_once __DIR__ . '/php/auth.php';
require_once __DIR__ . '/php/cotizaciones-crud.php';
require_once __DIR__ . '/php/currency-helpers.php';

// Verificar permisos
verificarAutenticacion();
verificarPermisos(ROLES_VENDEDOR);

// Inicializar CRUD
$cotizacionesCRUD = new CotizacionesCRUD($conn);

// Generar código de cotización
$codigo_cotizacion = generarCodigoCotizacion();

// Procesar formulario
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $cliente_nombre = trim($_POST['cliente_nombre']);
    $cliente_email = trim($_POST['cliente_email']);
    $cliente_telefono = trim($_POST['cliente_telefono']);
    $cliente_documento = trim($_POST['cliente_documento']);
    $fecha_validez = trim($_POST['fecha_validez']);
    $notas = trim($_POST['notas']);
    $moneda = trim($_POST['moneda']);
    
    // Obtener servicios del formulario
    $servicios = json_decode($_POST['servicios_json'], true) ?? [];
    
    // Validaciones
    if (empty($cliente_nombre)) {
        $error = "El nombre del cliente es requerido";
    } elseif (empty($servicios)) {
        $error = "Debe agregar al menos un servicio a la cotización";
    } else {

// Calcular totales
$subtotal = 0;
foreach ($servicios as $servicio) {
    $subtotal += $servicio['subtotal'];
}
$impuestos = $subtotal * 0.13;
$total = $subtotal + $impuestos;
        
        // Datos para la cotización
        $datos_cotizacion = [
            'codigo_cotizacion' => $codigo_cotizacion,
            'fecha_validez' => $fecha_validez,
            'cliente_nombre' => $cliente_nombre,
            'cliente_email' => $cliente_email,
            'cliente_telefono' => $cliente_telefono,
            'cliente_documento' => $cliente_documento,
            'notas' => $notas,
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => $total,
            'moneda' => $moneda,
            'vendedor_id' => $_SESSION['usuario_id']
        ];
        
        // Crear cotización
        $cotizacion_id = $cotizacionesCRUD->crearCotizacion($datos_cotizacion);
        
        if ($cotizacion_id) {
            // Agregar servicios
            foreach ($servicios as $servicio) {
                $cotizacionesCRUD->agregarServicio($cotizacion_id, $servicio);
            }
            
            $success = "Cotización creada exitosamente!";
            // Redirigir a ver cotización
            echo "<script>setTimeout(function() { window.location.href = 'ver-cotizacion.php?id=' + $cotizacion_id; }, 2000);</script>";
        } else {
            $error = "Error al crear la cotización: " . $conn->error;
        }
    }
}

$page_title = "Nueva Cotización";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Nueva Cotización <small class="text-muted"><?php echo $codigo_cotizacion; ?></small></h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="cotizaciones.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver a Cotizaciones
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST" id="formCotizacion">
    <input type="hidden" name="servicios_json" id="serviciosJson" value="[]">
    
    <div class="row">
        <div class="col-md-8">
            <!-- Información del Cliente -->
            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Información del Solicitante</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Nombre completo *</label>
                                <input type="text" class="form-control" name="cliente_nombre" required
                                       value="<?php echo isset($_POST['cliente_nombre']) ? htmlspecialchars($_POST['cliente_nombre']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" name="cliente_email"
                                       value="<?php echo isset($_POST['cliente_email']) ? htmlspecialchars($_POST['cliente_email']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Teléfono</label>
                                <input type="tel" class="form-control" name="cliente_telefono"
                                       value="<?php echo isset($_POST['cliente_telefono']) ? htmlspecialchars($_POST['cliente_telefono']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Documento de identidad</label>
                                <input type="text" class="form-control" name="cliente_documento"
                                       value="<?php echo isset($_POST['cliente_documento']) ? htmlspecialchars($_POST['cliente_documento']) : ''; ?>">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Servicios de la Cotización -->
            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Servicios Cotizados</h5>
                    <button type="button" class="btn btn-sm btn-civit-primary" data-bs-toggle="modal" data-bs-target="#modalServicios">
                        <i class="fas fa-plus me-1"></i> Agregar Servicio
                    </button>
                </div>
                <div class="card-body">
                    <div id="servicios-cotizacion">
                        <p class="text-muted text-center py-3" id="sin-servicios">No hay servicios agregados</p>
                        <div id="lista-servicios" style="display: none;"></div>
                    </div>
                </div>
            </div>
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
                        <span id="subtotal">$0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Impuestos (13%):</span>
                        <span id="impuestos">$0.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold">
                        <span>Total:</span>
                        <span id="total">$0.00</span>
                    </div>
                    
                    <div class="mb-3 mt-3">
                        <label class="form-label">Moneda *</label>
                        <select class="form-select" name="moneda" required>
                            <option value="USD" selected>USD - Dólar Americano</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="MXN">MXN - Peso Mexicano</option>
                            <option value="CRC">CRC - Colón Costarricense</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Fecha de validez *</label>
                        <input type="date" class="form-control" name="fecha_validez" required
                               value="<?php echo date('Y-m-d', strtotime('+15 days')); ?>">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Notas adicionales</label>
                        <textarea class="form-control" name="notas" rows="3"><?php echo isset($_POST['notas']) ? htmlspecialchars($_POST['notas']) : ''; ?></textarea>
                    </div>
                    
                    <button type="submit" class="btn btn-civit-primary w-100">
                        <i class="fas fa-save me-2"></i> Guardar Cotización
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<!-- Modal para agregar servicios -->
<div class="modal fade" id="modalServicios" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Servicios</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <ul class="nav nav-tabs" id="serviciosTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#aereos">
                            <i class="fas fa-plane me-1"></i> Boletos Aéreos
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#hoteles">
                            <i class="fas fa-hotel me-1"></i> Hoteles
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" data-bs-toggle="tab" data-bs-target="#paquetes">
                            <i class="fas fa-suitcase me-1"></i> Paquetes
                        </button>
                    </li>
                </ul>
                
                <div class="tab-content mt-3">
                    <!-- Pestaña de Vuelos -->
                    <div class="tab-pane fade show active" id="aereos">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" placeholder="Buscar vuelos por origen, destino o aerolínea..." 
                                       id="buscarVuelos" autocomplete="off">
                            </div>
                        </div>
                        <div id="resultadosVuelos" class="mt-3"></div>
                    </div>
                    
                    <!-- Pestaña de Hoteles -->
                    <div class="tab-pane fade" id="hoteles">
                        <div class="mb-3">
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-search"></i></span>
                                <input type="text" class="form-control" placeholder="Buscar hoteles por nombre, ciudad o país..." 
                                       id="buscarHoteles" autocomplete="off">
                            </div>
                        </div>
                        <div id="resultadosHoteles" class="mt-3"></div>
                    </div>
                    
                    <!-- Pestaña de Paquetes -->
                    <div class="tab-pane fade" id="paquetes">
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            Módulo de paquetes turísticos en desarrollo. Próximamente disponible.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
// Servicios en memoria
let servicios = [];

// Debounce para búsquedas
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Actualizar interfaz
function actualizarInterfaz() {
    const lista = document.getElementById('lista-servicios');
    const sinServicios = document.getElementById('sin-servicios');
    
    if (servicios.length === 0) {
        lista.style.display = 'none';
        sinServicios.style.display = 'block';
    } else {
        lista.style.display = 'block';
        sinServicios.style.display = 'none';
        
        lista.innerHTML = servicios.map((servicio, index) => `
            <div class="card mb-2">
                <div class="card-body py-2">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="flex-grow-1">
                            <h6 class="mb-0">${servicio.descripcion}</h6>
                            <small class="text-muted">
                                ${servicio.tipo_servicio.toUpperCase()} - 
                                ${servicio.precio} ${servicio.moneda} x ${servicio.cantidad}
                            </small>
                        </div>
                        <div class="text-end ms-3">
                            <strong>${servicio.subtotal.toFixed(2)} ${servicio.moneda}</strong>
                            <br>
                            <div class="btn-group btn-group-sm mt-1">
                                <button type="button" class="btn btn-outline-secondary" onclick="editarServicio(${index})">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-outline-danger" onclick="eliminarServicio(${index})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `).join('');
    }
    
    // Actualizar totales
    actualizarTotales(); // Cambia esta línea
}

// Añade esta nueva función
function actualizarTotales() {
    const subtotal = servicios.reduce((sum, servicio) => sum + parseFloat(servicio.subtotal), 0);
    const impuestos = subtotal * 0.13;
    const total = subtotal + impuestos;
    
    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
    document.getElementById('impuestos').textContent = '$' + impuestos.toFixed(2);
    document.getElementById('total').textContent = '$' + total.toFixed(2);
    
    // Actualizar JSON hidden
    document.getElementById('serviciosJson').value = JSON.stringify(servicios);
}

// Eliminar servicio
function eliminarServicio(index) {
    servicios.splice(index, 1);
    actualizarInterfaz();
}

// Editar cantidad /servicio
function editarServicio(index) {
    const servicio = servicios[index];
    const nuevaCantidad = prompt(`Cantidad actual: ${servicio.cantidad}\nIngrese la nueva cantidad:`, servicio.cantidad);
    
    if (nuevaCantidad && !isNaN(nuevaCantidad) && nuevaCantidad > 0) {
        servicios[index].cantidad = parseInt(nuevaCantidad);
        servicios[index].subtotal = servicios[index].precio * servicios[index].cantidad;
        actualizarInterfaz();
    }
}

// Agregar servicio
function agregarServicio(servicio) {
    // Asegurar que tenemos todos los campos necesarios
    const servicioCompleto = {
        tipo_servicio: servicio.tipo_servicio,
        servicio_id: servicio.servicio_id,
        descripcion: servicio.descripcion,
        detalles: servicio.detalles,
        precio: servicio.precio,
        cantidad: servicio.cantidad || 1,
        subtotal: servicio.subtotal || (servicio.precio * servicio.cantidad)
    };
    
    // Verificar si el servicio ya existe
    const servicioExistente = servicios.find(s => 
        s.servicio_id === servicioCompleto.servicio_id && 
        s.tipo_servicio === servicioCompleto.tipo_servicio
    );
    
    if (servicioExistente) {
        if (confirm('Este servicio ya está agregado. ¿Desea aumentar la cantidad?')) {
            servicioExistente.cantidad += servicioCompleto.cantidad;
            servicioExistente.subtotal = servicioExistente.precio * servicioExistente.cantidad;
            actualizarInterfaz();
        }
    } else {
        servicios.push(servicioCompleto);
        actualizarInterfaz();
    }
    
    $('#modalServicios').modal('hide');
}

// Buscar vuelos
const buscarVuelos = debounce(function(query) {
    if (query.length < 2) {
        document.getElementById('resultadosVuelos').innerHTML = '';
        return;
    }
    
    document.getElementById('resultadosVuelos').innerHTML = `
        <div class="text-center">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Buscando...</span>
            </div>
            <span class="ms-2">Buscando vuelos...</span>
        </div>
    `;
    
    fetch(`php/buscar-vuelos.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(vuelos => {
            if (vuelos.length === 0) {
                document.getElementById('resultadosVuelos').innerHTML = `
                    <div class="alert alert-warning">
                        No se encontraron vuelos que coincidan con "${query}"
                    </div>
                `;
            } else {
                document.getElementById('resultadosVuelos').innerHTML = vuelos.map(vuelo => `
                    <div class="card mb-2">
                        <div class="card-body">
                            <h6 class="card-title">${vuelo.descripcion}</h6>
                            <p class="card-text mb-1">
                                <small>Salida: ${new Date(vuelo.detalles.fecha_salida).toLocaleDateString()}</small>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0 text-success">${vuelo.precio} ${vuelo.moneda}</span>
                                <button class="btn btn-civit-primary btn-sm" 
                                        onclick="seleccionarVuelo(${JSON.stringify(vuelo).replace(/"/g, '&quot;')})">
                                    <i class="fas fa-plus me-1"></i> Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => {
            document.getElementById('resultadosVuelos').innerHTML = `
                <div class="alert alert-danger">
                    Error al buscar vuelos. Intente nuevamente.
                </div>
            `;
        });
}, 500);

// Buscar hoteles
const buscarHoteles = debounce(function(query) {
    if (query.length < 2) {
        document.getElementById('resultadosHoteles').innerHTML = '';
        return;
    }
    
    document.getElementById('resultadosHoteles').innerHTML = `
        <div class="text-center">
            <div class="spinner-border spinner-border-sm" role="status">
                <span class="visually-hidden">Buscando...</span>
            </div>
            <span class="ms-2">Buscando hoteles...</span>
        </div>
    `;
    
    fetch(`php/buscar-hoteles.php?q=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(hoteles => {
            if (hoteles.length === 0) {
                document.getElementById('resultadosHoteles').innerHTML = `
                    <div class="alert alert-warning">
                        No se encontraron hoteles que coincidan con "${query}"
                    </div>
                `;
            } else {
                document.getElementById('resultadosHoteles').innerHTML = hoteles.map(hotel => `
                    <div class="card mb-2">
                        <div class="card-body">
                            <h6 class="card-title">${hotel.descripcion}</h6>
                            <p class="card-text mb-1">
                                <small>${hotel.detalles.ciudad}, ${hotel.detalles.pais} • ${'⭐'.repeat(hotel.detalles.categoria)}</small>
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="h5 mb-0 text-success">Desde ${hotel.precio} ${hotel.moneda}/noche</span>
                                <button class="btn btn-civit-primary btn-sm" 
                                        onclick="seleccionarHotel(${JSON.stringify(hotel).replace(/"/g, '&quot;')})">
                                    <i class="fas fa-plus me-1"></i> Agregar
                                </button>
                            </div>
                        </div>
                    </div>
                `).join('');
            }
        })
        .catch(error => {
            document.getElementById('resultadosHoteles').innerHTML = `
                <div class="alert alert-danger">
                    Error al buscar hoteles. Intente nuevamente.
                </div>
            `;
        });
}, 500);

// En la función buscarVuelos, después del fetch:
.then(vuelos => {
    console.log('Vuelos encontrados:', vuelos); // ← Debug
    if (vuelos.length === 0) {
        // ... resto del código
    }
})

// Seleccionar vuelo
function seleccionarVuelo(vuelo) {
    const cantidad = prompt('¿Cuántos pasajes desea agregar?', '1');
    if (cantidad && !isNaN(cantidad) && cantidad > 0) {
        const subtotal = parseFloat(vuelo.precio) * parseInt(cantidad);
        
        agregarServicio({
            tipo_servicio: 'aereo',
            servicio_id: vuelo.id,
            descripcion: `Vuelo: ${vuelo.descripcion}`,
            detalles: JSON.stringify({
                origen: vuelo.detalles.origen,
                destino: vuelo.detalles.destino,
                aerolinea: vuelo.detalles.aerolinea,
                fecha_salida: vuelo.detalles.fecha_salida,
                fecha_regreso: vuelo.detalles.fecha_regreso,
                clase: vuelo.detalles.clase,
                numero_vuelo: vuelo.codigo // ← Nombre correcto
            }),
            precio: parseFloat(vuelo.precio),
            cantidad: parseInt(cantidad),
            subtotal: subtotal,
            moneda: vuelo.moneda || 'USD'
        });
    }
}

// Seleccionar hotel
function seleccionarHotel(hotel) {
    const noches = prompt('¿Cuántas noches de hospedaje?', '2');
    const habitaciones = prompt('¿Cuántas habitaciones?', '1');
    
    if (noches && !isNaN(noches) && noches > 0 && habitaciones && !isNaN(habitaciones) && habitaciones > 0) {
        const subtotal = parseFloat(hotel.precio) * parseInt(noches) * parseInt(habitaciones);
        
        agregarServicio({
            tipo_servicio: 'hotel',
            servicio_id: hotel.id,
            descripcion: `Hotel: ${hotel.nombre}`,
            detalles: JSON.stringify({
                ciudad: hotel.detalles.ciudad,
                pais: hotel.detalles.pais,
                categoria: hotel.detalles.categoria,
                check_in: hotel.detalles.check_in,
                check_out: hotel.detalles.check_out,
                habitaciones: parseInt(habitaciones)
            }),
            precio: parseFloat(hotel.precio),
            cantidad: parseInt(noches) * parseInt(habitaciones),
            subtotal: subtotal,
            moneda: hotel.moneda
        });
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    actualizarInterfaz();
    
    // Búsqueda de vuelos
    document.getElementById('buscarVuelos').addEventListener('input', function(e) {
        buscarVuelos(e.target.value.trim());
    });
    
    // Búsqueda de hoteles
    document.getElementById('buscarHoteles').addEventListener('input', function(e) {
        buscarHoteles(e.target.value.trim());
    });
    
    // Limpiar resultados al cambiar de pestaña
    $('#modalServicios').on('hidden.bs.modal', function() {
        document.getElementById('buscarVuelos').value = '';
        document.getElementById('buscarHoteles').value = '';
        document.getElementById('resultadosVuelos').innerHTML = '';
        document.getElementById('resultadosHoteles').innerHTML = '';
    });
});

// Al final de tu JavaScript
console.log('Servicios actualizados:', servicios);
</script>