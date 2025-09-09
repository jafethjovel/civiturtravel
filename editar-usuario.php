<?php
// Iniciar sesión primero
session_start();

// Incluir la base de datos con ruta absoluta
require_once __DIR__ . '/php/database.php';

// Verificar si la conexión se estableció
if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos");
}

// Obtener ID del usuario a editar
$usuario_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$usuario = null;

if ($usuario_id > 0) {
    // Obtener información del usuario
    if ($stmt = $conn->prepare("SELECT * FROM usuarios WHERE id = ?")) {
        $stmt->bind_param("i", $usuario_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $usuario = $result->fetch_assoc();
        $stmt->close();
    }
}

// Si no se encuentra el usuario, redirigir
if (!$usuario) {
    header("Location: usuarios.php");
    exit();
}

// Verificar permisos
$es_superadmin = $_SESSION['usuario_rol'] === 'superadmin';
$es_admin = $_SESSION['usuario_rol'] === 'admin';
$es_mismo_usuario = $usuario['id'] == $_SESSION['usuario_id'];

// Superadmin puede editar a todos, admin solo puede editar vendedores, usuarios solo pueden verse a sí mismos
if (!$es_superadmin && !$es_mismo_usuario && !($es_admin && $usuario['rol'] === 'vendedor')) {
    header("Location: usuarios.php");
    exit();
}

$page_title = "Editar Usuario";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Procesar formulario de actualización
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $rol = trim($_POST['rol']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Campos opcionales (solo si se proporcionan)
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($rol)) {
        $error = "Por favor complete todos los campos requeridos";
    } elseif (!empty($password) && $password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } elseif (!empty($password) && strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        // Verificar si el email ya existe para otro usuario
        if ($stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?")) {
            $stmt->bind_param("si", $email, $usuario_id);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "El email ya está registrado para otro usuario";
            } else {
                $stmt->close();
                
                // Preparar consulta de actualización
                if (empty($password)) {
                    // Actualizar sin cambiar contraseña
                    $query = "UPDATE usuarios SET nombre = ?, email = ?, rol = ?, activo = ?, fecha_actualizacion = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("sssii", $nombre, $email, $rol, $activo, $usuario_id);
                } else {
                    // Actualizar con nueva contraseña
                    $password_hash = password_hash($password, PASSWORD_DEFAULT);
                    $query = "UPDATE usuarios SET nombre = ?, email = ?, password = ?, rol = ?, activo = ?, fecha_actualizacion = NOW() WHERE id = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ssssii", $nombre, $email, $password_hash, $rol, $activo, $usuario_id);
                }
                
                if ($stmt->execute()) {
                    $success = "Usuario actualizado exitosamente!";
                    
                    // Actualizar datos locales
                    $usuario['nombre'] = $nombre;
                    $usuario['email'] = $email;
                    $usuario['rol'] = $rol;
                    $usuario['activo'] = $activo;
                    
                } else {
                    $error = "Error al actualizar el usuario: " . $stmt->error;
                }
                $stmt->close();
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Usuario</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="usuarios.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver a Usuarios
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST" id="usuarioForm">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Información del Usuario</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required
                                   value="<?php echo htmlspecialchars($usuario['nombre']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="<?php echo htmlspecialchars($usuario['email']); ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control" id="password" name="password"
                                   minlength="6" placeholder="Dejar en blanco para no cambiar">
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password"
                                   minlength="6" placeholder="Repite la contraseña">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="rol" class="form-label">Rol *</label>
                            <select class="form-select" id="rol" name="rol" required <?php echo (!$es_superadmin && !$es_mismo_usuario) ? 'disabled' : ''; ?>>
                                <option value="">Seleccionar rol...</option>
                                <option value="superadmin" <?php echo $usuario['rol'] == 'superadmin' ? 'selected' : ''; echo (!$es_superadmin) ? ' disabled' : ''; ?>>Superadministrador</option>
                                <option value="admin" <?php echo $usuario['rol'] == 'admin' ? 'selected' : ''; echo (!$es_superadmin) ? ' disabled' : ''; ?>>Administrador</option>
                                <option value="vendedor" <?php echo $usuario['rol'] == 'vendedor' ? 'selected' : ''; ?>>Vendedor</option>
                            </select>
                            <?php if (!$es_superadmin && !$es_mismo_usuario): ?>
                                <input type="hidden" name="rol" value="<?php echo $usuario['rol']; ?>">
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" 
                                    <?php echo $usuario['activo'] ? 'checked' : ''; echo ($es_mismo_usuario) ? ' disabled' : ''; ?>>
                                <label class="form-check-label" for="activo">Usuario activo</label>
                                <?php if ($es_mismo_usuario): ?>
                                    <input type="hidden" name="activo" value="1">
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Información del Sistema</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label">ID de Usuario</label>
                        <input type="text" class="form-control" value="<?php echo $usuario['id']; ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Fecha de Registro</label>
                        <input type="text" class="form-control" value="<?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?>" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Última Actualización</label>
                        <input type="text" class="form-control" value="<?php echo $usuario['fecha_actualizacion'] ? date('d/m/Y H:i', strtotime($usuario['fecha_actualizacion'])) : 'Nunca'; ?>" disabled>
                    </div>
                </div>
            </div>

            <div class="card card-civit">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-civit-primary btn-lg w-100 mb-2">
                        <i class="fas fa-save me-2"></i> Actualizar Usuario
                    </button>
                    <a href="usuarios.php" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-times me-1"></i> Cancelar
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<?php require_once 'includes/footer.php'; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación del formulario
    const formulario = document.getElementById('usuarioForm');
    
    formulario.addEventListener('submit', function(e) {
        let valid = true;
        
        // Validar email
        const email = document.getElementById('email');
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        
        if (!emailRegex.test(email.value)) {
            alert('Por favor ingrese un email válido');
            email.focus();
            valid = false;
        }
        
        // Validar contraseñas
        const password = document.getElementById('password');
        const confirmPassword = document.getElementById('confirm_password');
        
        if (password.value !== confirmPassword.value) {
            alert('Las contraseñas no coinciden');
            confirmPassword.focus();
            valid = false;
        }
        
        if (password.value && password.value.length < 6) {
            alert('La contraseña debe tener al menos 6 caracteres');
            password.focus();
            valid = false;
        }
        
        if (!valid) {
            e.preventDefault();
        }
    });
});
</script>