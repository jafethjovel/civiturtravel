<?php
session_start();
require_once __DIR__ . '/php/auth.php';

// Verificar autenticación
verificarAutenticacion();

// Verificar permisos - Solo superadmin puede gestionar usuarios
verificarPermisos(ROLES_SUPERADMIN);

// Incluir la base de datos con ruta absoluta
require_once __DIR__ . '/php/database.php';

// Verificar si la conexión se estableció
if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos");
}

// Verificar permisos (solo superadmin y admin pueden acceder)
if ($_SESSION['usuario_rol'] !== 'superadmin' && $_SESSION['usuario_rol'] !== 'admin') {
    header("Location: index.php");
    exit();
}

$page_title = "Gestión de Usuarios";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Obtener parámetros de búsqueda
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$rol = isset($_GET['rol']) ? trim($_GET['rol']) : '';

// Construir consulta base
$query = "SELECT * FROM usuarios WHERE 1=1";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (nombre LIKE ? OR email LIKE ?)";
    $search_term = "%$search%";
    $params = array_merge($params, [$search_term, $search_term]);
    $types .= "ss";
}

if (!empty($rol)) {
    $query .= " AND rol = ?";
    $params[] = $rol;
    $types .= "s";
}

$query .= " ORDER BY nombre ASC";

// Obtener usuarios
$usuarios = [];
if ($stmt = $conn->prepare($query)) {
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }
    $stmt->close();
}

// Estadísticas
$total_usuarios = count($usuarios);
$superadmins = array_filter($usuarios, function($u) { return $u['rol'] === 'superadmin'; });
$admins = array_filter($usuarios, function($u) { return $u['rol'] === 'admin'; });
$vendedores = array_filter($usuarios, function($u) { return $u['rol'] === 'vendedor'; });
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Gestión de Usuarios</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <?php if ($_SESSION['usuario_rol'] === 'superadmin'): ?>
        <a href="nuevo-usuario.php" class="btn btn-sm btn-civit-primary">
            <i class="fas fa-user-plus me-1"></i> Nuevo Usuario
        </a>
        <?php endif; ?>
    </div>
</div>

<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-md-3">
        <div class="card stat-card bg-stat-1 text-white">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-8">
                        <h6 class="fw-bold">Total Usuarios</h6>
                        <h4 class="card-title mb-0"><?php echo $total_usuarios; ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-users fa-2x opacity-50"></i>
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
                        <h6 class="fw-bold">Superadmins</h6>
                        <h4 class="card-title mb-0"><?php echo count($superadmins); ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-crown fa-2x opacity-50"></i>
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
                        <h6 class="fw-bold">Administradores</h6>
                        <h4 class="card-title mb-0"><?php echo count($admins); ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-user-shield fa-2x opacity-50"></i>
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
                        <h6 class="fw-bold">Vendedores</h6>
                        <h4 class="card-title mb-0"><?php echo count($vendedores); ?></h4>
                    </div>
                    <div class="col-4 text-end">
                        <i class="fas fa-user-tie fa-2x opacity-50"></i>
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
        <form method="GET" action="usuarios.php">
            <div class="row g-3">
                <div class="col-md-6">
                    <label for="search" class="form-label">Buscar usuario</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Nombre o email" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <div class="col-md-4">
                    <label for="rol" class="form-label">Rol</label>
                    <select class="form-select" id="rol" name="rol">
                        <option value="">Todos los roles</option>
                        <option value="superadmin" <?php echo $rol == 'superadmin' ? 'selected' : ''; ?>>Superadmin</option>
                        <option value="admin" <?php echo $rol == 'admin' ? 'selected' : ''; ?>>Administrador</option>
                        <option value="vendedor" <?php echo $rol == 'vendedor' ? 'selected' : ''; ?>>Vendedor</option>
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

<!-- Lista de usuarios -->
<div class="card card-civit">
    <div class="card-header card-header-civit d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Lista de Usuarios</h5>
        <span class="badge bg-secondary"><?php echo $total_usuarios; ?> usuarios</span>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha Registro</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($usuarios) > 0): ?>
                        <?php foreach ($usuarios as $usuario): ?>
                            <tr>
                                <td>
                                    <div class="fw-bold"><?php echo htmlspecialchars($usuario['nombre']); ?></div>
                                    <?php if ($usuario['id'] == $_SESSION['usuario_id']): ?>
                                        <small class="text-primary">(Tú)</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($usuario['email']); ?></td>
                                <td>
                                    <?php 
                                    $badge_class = '';
                                    switch ($usuario['rol']) {
                                        case 'superadmin': $badge_class = 'bg-danger'; break;
                                        case 'admin': $badge_class = 'bg-warning text-dark'; break;
                                        case 'vendedor': $badge_class = 'bg-info'; break;
                                        default: $badge_class = 'bg-secondary';
                                    }
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($usuario['rol']); ?></span>
                                </td>
                                <td>
                                    <span class="badge <?php echo $usuario['activo'] ? 'bg-success' : 'bg-secondary'; ?>">
                                        <?php echo $usuario['activo'] ? 'Activo' : 'Inactivo'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($usuario['fecha_creacion'])); ?>
                                </td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <?php if ($_SESSION['usuario_rol'] === 'superadmin' || ($_SESSION['usuario_rol'] === 'admin' && $usuario['rol'] === 'vendedor')): ?>
                                        <a href="editar-usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-outline-primary" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($_SESSION['usuario_rol'] === 'superadmin' && $usuario['id'] != $_SESSION['usuario_id']): ?>
                                        <button class="btn btn-outline-danger" title="Eliminar" onclick="confirmarEliminacion(<?php echo $usuario['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5>No hay usuarios registrados</h5>
                                <p class="text-muted">Comienza agregando el primer usuario</p>
                                <a href="nuevo-usuario.php" class="btn btn-civit-primary">Agregar Usuario</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
                <p>¿Estás seguro de que deseas eliminar este usuario?</p>
                <p class="text-danger"><strong>Advertencia:</strong> Esta acción no se puede deshacer y afectará todas las cotizaciones asociadas a este usuario.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmarEliminacionBtn">Eliminar Usuario</button>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
let usuarioIdAEliminar = null;

function confirmarEliminacion(id) {
    usuarioIdAEliminar = id;
    const modal = new bootstrap.Modal(document.getElementById('confirmarEliminacionModal'));
    modal.show();
}

document.getElementById('confirmarEliminacionBtn').addEventListener('click', function() {
    if (usuarioIdAEliminar) {
        // Aquí iría la llamada AJAX para eliminar el usuario
        alert('Función de eliminación será implementada para el usuario ID: ' + usuarioIdAEliminar);
        $('#confirmarEliminacionModal').modal('hide');
    }
});
</script>