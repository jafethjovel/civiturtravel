<?php
// Iniciar sesión primero
session_start();

//Incluir a la base de datos
require_once __DIR__ . '/php/database.php';
require_once __DIR__ . '/php/auth.php';

// INICIALIZAR VARIABLES UNA SOLA VEZ
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$ciudad = isset($_GET['ciudad']) ? trim($_GET['ciudad']) : '';
$categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : '';
$pais = isset($_GET['pais']) ? trim($_GET['pais']) : '';

// Verificar si la conexión se estableció
if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos");
}

// Verificar permisos
verificarAutenticacion();
verificarPermisos(ROLES_VENDEDOR);

$page_title = "Gestión de Hoteles";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Obtener parámetros de búsqueda
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$ciudad = isset($_GET['ciudad']) ? trim($_GET['ciudad']) : '';
$categoria = isset($_GET['categoria']) ? intval($_GET['categoria']) : '';
$pais = isset($_GET['pais']) ? trim($_GET['pais']) : '';

// Configuración de paginación
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$hoteles_por_pagina = 10;
$offset = ($pagina_actual - 1) * $hoteles_por_pagina;

// Construir consulta base
$query = "SELECT h.*, 
                 COUNT(ha.id) as total_habitaciones,
                 AVG(ha.precio_noche) as precio_promedio
          FROM hoteles h
          LEFT JOIN habitaciones ha ON h.id = ha.hotel_id
          WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (h.nombre LIKE ? OR h.direccion LIKE ? OR h.ciudad LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term, $search_term]);
    $types .= "sss";
}

if (!empty($ciudad)) {
    $query .= " AND h.ciudad = ?";
    $params[] = $ciudad;
    $types .= "s";
}

if (!empty($categoria)) {
    $query .= " AND h.categoria = ?";
    $params[] = $categoria;
    $types .= "i";
}

if (!empty($pais)) {
    $query .= " AND h.pais = ?";
    $params[] = $pais;
    $types .= "s";
}

// PRIMERO: Obtener el total de hoteles (sin LIMIT)
$query_total = "SELECT COUNT(DISTINCT h.id) as total 
                FROM hoteles h 
                LEFT JOIN habitaciones ha ON h.id = ha.hotel_id 
                WHERE 1=1";

// Aplicar mismos filtros a query_total
if (!empty($search)) {
    $query_total .= " AND (h.nombre LIKE ? OR h.direccion LIKE ? OR h.ciudad LIKE ?)";
}

if (!empty($ciudad)) {
    $query_total .= " AND h.ciudad = ?";
}

if (!empty($categoria)) {
    $query_total .= " AND h.categoria = ?";
}

if (!empty($pais)) {
    $query_total .= " AND h.pais = ?";
}

// Obtener total de hoteles
$total_hoteles = 0;
if ($stmt_total = $conn->prepare($query_total)) {
    // Usar los mismos parámetros para la consulta total
    if (!empty($params)) {
        $stmt_total->bind_param($types, ...$params);
    }
    $stmt_total->execute();
    $result_total = $stmt_total->get_result();
    $row_total = $result_total->fetch_assoc();
    $total_hoteles = $row_total['total'];
    $stmt_total->close();
}

// SEGUNDO: Ahora preparar la consulta principal con LIMIT
$query .= " GROUP BY h.id ORDER BY h.nombre ASC LIMIT ? OFFSET ?";

// Crear nuevos arrays para los parámetros del LIMIT
$params_limit = $params;
$params_limit[] = $hoteles_por_pagina;
$params_limit[] = $offset;
$types_limit = $types . "ii";

// Obtener hoteles para la página actual
$hoteles = [];
if ($stmt = $conn->prepare($query)) {
    if (!empty($params_limit)) {
        $stmt->bind_param($types_limit, ...$params_limit);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $hoteles[] = $row;
    }
    $stmt->close();
}

// Calcular total de páginas
$total_paginas = ceil($total_hoteles / $hoteles_por_pagina);

// Obtener países únicos para el filtro
$paises = [];
if ($stmt = $conn->prepare("SELECT DISTINCT pais FROM hoteles WHERE pais IS NOT NULL ORDER BY pais")) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $paises[] = $row['pais'];
    }
    $stmt->close();
}

// Obtener ciudades únicas para el filtro
$ciudades = [];
if ($stmt = $conn->prepare("SELECT DISTINCT ciudad FROM hoteles WHERE ciudad IS NOT NULL ORDER BY ciudad")) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $ciudades[] = $row['ciudad'];
    }
    $stmt->close();
}

// Función para generar URLs de paginación manteniendo los filtros
function generarUrlPaginacion($pagina) {
    $params = $_GET;
    $params['pagina'] = $pagina;
    return 'hoteles.php?' . http_build_query($params);
}

?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Hoteles</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="nuevo-hotel.php" class="btn btn-sm btn-civit-primary">
            <i class="fas fa-hotel me-1"></i> Nuevo Hotel
        </a>
    </div>
</div>

<!-- Filtros de Búsqueda Internacional -->
<div class="card card-civit mb-4">
    <div class="card-header card-header-civit">
        <h5 class="mb-0">Búsqueda Global de Hoteles</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="hoteles.php">
            <div class="row g-3">
                <div class="col-md-3">
                    <label for="search" class="form-label">Buscar hotel</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Nombre, ciudad o destino" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-3">
                    <label for="ciudad" class="form-label">Ciudad</label>
                    <input type="text" class="form-control" id="ciudad" name="ciudad" 
                           placeholder="Nombre de ciudad" value="<?php echo htmlspecialchars($ciudad); ?>">
                </div>
                <div class="col-md-3">
    <label for="pais" class="form-label">País</label>
    <select class="form-select" id="pais" name="pais">
        <option value="">Todos los países</option>
        <?php foreach ($paises as $pais_option): ?>
            <option value="<?php echo $pais_option; ?>" 
                <?php echo (isset($_GET['pais']) && $_GET['pais'] == $pais_option) ? 'selected' : ''; ?>>
                <?php echo $pais_option; ?>
            </option>
        <?php endforeach; ?>
    </select>
</div>
                <div class="col-md-3">
                    <label for="categoria" class="form-label">Categoría</label>
                    <select class="form-select" id="categoria" name="categoria">
                        <option value="">Todas las categorías</option>
                        <option value="1" <?php echo $categoria == 1 ? 'selected' : ''; ?>>1 Estrella</option>
                        <option value="2" <?php echo $categoria == 2 ? 'selected' : ''; ?>>2 Estrellas</option>
                        <option value="3" <?php echo $categoria == 3 ? 'selected' : ''; ?>>3 Estrellas</option>
                        <option value="4" <?php echo $categoria == 4 ? 'selected' : ''; ?>>4 Estrellas</option>
                        <option value="5" <?php echo $categoria == 5 ? 'selected' : ''; ?>>5 Estrellas</option>
                    </select>
                </div>
                <div class="col-md-12 text-end mt-3">
                    <button type="submit" class="btn btn-civit-primary">
                        <i class="fas fa-search me-1"></i> Buscar en Destinos Globales
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de hoteles -->
<div class="card card-civit">
    <div class="card-header card-header-civit d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lista de Hoteles</h5>
        <span class="badge bg-secondary"><?php echo count($hoteles); ?> hoteles encontrados</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Hotel</th>
                        <th>Ubicación</th>
                        <th>Categoría</th>
                        <th>Habitaciones</th>
                        <th>Precio/Noche</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($hoteles) > 0): ?>
                        <?php foreach ($hoteles as $hotel): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($hotel['nombre']); ?></div>
                                    <small class="text-muted"><?php echo htmlspecialchars($hotel['cadena_hotelera']); ?></small>
                                </td>
                                <!-- En la tabla de hoteles, modificar la columna de ubicación -->
<td>
    <?php
    require_once __DIR__ . '/includes/country-flags.php';
    echo mostrarBanderaPais($hotel['pais'], '16px');
    ?>
    <div><?php echo htmlspecialchars($hotel['ciudad']); ?></div>
    <small class="text-muted"><?php echo htmlspecialchars($hotel['pais']); ?></small>
</td>
                                <td>
                                    <?php echo str_repeat('⭐', $hotel['categoria']); ?>
                                    <small class="text-muted">(<?php echo $hotel['categoria']; ?> estrellas)</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary"><?php echo $hotel['total_habitaciones']; ?> hab.</span>
                                </td>
                                <!-- En la tabla de hoteles, modificar la columna de precio -->
<td>
    <strong><?php
        require_once __DIR__ . '/php/currency-helpers.php';
        $moneda = $hotel['moneda'] ?? 'USD';
        
        // Obtener precios mínimos y máximos
        $stmt_precios = $conn->prepare("SELECT MIN(precio_noche) as min_precio, MAX(precio_noche) as max_precio FROM habitaciones WHERE hotel_id = ?");
        $stmt_precios->bind_param("i", $hotel['id']);
        $stmt_precios->execute();
        $precios = $stmt_precios->get_result()->fetch_assoc();
        $stmt_precios->close();
        
        if ($precios['min_precio'] && $precios['max_precio']) {
            if ($precios['min_precio'] == $precios['max_precio']) {
                echo formatearPrecio($precios['min_precio'], $moneda);
            } else {
                echo formatearPrecio($precios['min_precio'], $moneda) . ' - ' . formatearPrecio($precios['max_precio'], $moneda);
            }
        } else {
            echo 'Sin precios';
        }
    ?></strong>
</td>
                                <td>
                                    <span class="badge <?php echo $hotel['activo'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $hotel['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <a href="editar-hotel.php?id=<?php echo $hotel['id']; ?>" class="btn btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="habitaciones.php?hotel_id=<?php echo $hotel['id']; ?>" class="btn btn-outline-info" title="Habitaciones">
                                            <i class="fas fa-bed"></i>
                                        </a>
                                        <a href="disponibilidad.php?hotel_id=<?php echo $hotel['id']; ?>" class="btn btn-outline-success" title="Disponibilidad">
                                            <i class="fas fa-calendar-check"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center py-4">
                                <i class="fas fa-hotel fa-3x text-muted mb-3"></i>
                                <h5>No hay hoteles registrados</h5>
                                <p class="text-muted">Comienza agregando tu primer hotel</p>
                                <a href="nuevo-hotel.php" class="btn btn-civit-primary">Agregar Hotel</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (count($hoteles) > 0 && $total_paginas > 1): ?>
<nav aria-label="Page navigation example" class="mt-3">
    <ul class="pagination justify-content-center">
        <li class="page-item <?php echo $pagina_actual == 1 ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo generarUrlPaginacion($pagina_actual - 1); ?>" tabindex="-1">
                Anterior
            </a>
        </li>
        
        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li class="page-item <?php echo $pagina_actual == $i ? 'active' : ''; ?>">
                <a class="page-link" href="<?php echo generarUrlPaginacion($i); ?>">
                    <?php echo $i; ?>
                </a>
            </li>
        <?php endfor; ?>
        
        <li class="page-item <?php echo $pagina_actual == $total_paginas ? 'disabled' : ''; ?>">
            <a class="page-link" href="<?php echo generarUrlPaginacion($pagina_actual + 1); ?>">
                Siguiente
            </a>
        </li>
    </ul>
</nav>
<?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
// Filtrar tabla localmente
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