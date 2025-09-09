<?php
// Iniciar sesión primero
session_start();

// Incluir la base de datos con ruta absoluta
require_once __DIR__ . '/php/database.php';

// Verificar si la conexión se estableció
if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos");
}

// Verificar permisos (solo superadmin puede crear usuarios)
if ($_SESSION['usuario_rol'] !== 'superadmin') {
    header("Location: usuarios.php");
    exit();
}

$page_title = "Nuevo Usuario";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Procesar formulario
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $confirm_password = trim($_POST['confirm_password']);
    $rol = trim($_POST['rol']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($password) || empty($confirm_password) || empty($rol)) {
        $error = "Por favor complete todos los campos requeridos";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden";
    } elseif (strlen($password) < 6) {
        $error = "La contraseña debe tener al menos 6 caracteres";
    } else {
        // Verificar si el email ya existe
        if ($stmt = $conn->prepare("SELECT id FROM usuarios WHERE email = ?")) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "El email ya está registrado para otro usuario";
            } else {
                $stmt->close();
                
                // Hash de la contraseña
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                
                // Insertar nuevo usuario
                if ($stmt = $conn->prepare("INSERT INTO usuarios (nombre, email, password, rol, activo) VALUES (?, ?, ?, ?, ?)")) {
                    $stmt->bind_param("ssssi", $nombre, $email, $password_hash, $rol, $activo);
                    
                    if ($stmt->execute()) {
                        $success = "Usuario creado exitosamente!";
                        
                        // Limpiar campos
                        $_POST = array();
                    } else {
                        $error = "Error al crear el usuario: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Nuevo Usuario</h1>
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
                                   value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">Contraseña *</label>
                            <input type="password" class="form-control" id="password" name="password" required
                                   minlength="6" placeholder="Mínimo 6 caracteres">
                            <div class="form-text">La contraseña debe tener al menos 6 caracteres</div>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirmar Contraseña *</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password" required
                                   minlength="6" placeholder="Repite la contraseña">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="rol" class="form-label">Rol *</label>
                            <select class="form-select" id="rol" name="rol" required>
                                <option value="">Seleccionar rol...</option>
                                <option value="superadmin" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'superadmin') ? 'selected' : ''; ?>>Superadministrador</option>
                                <option value="admin" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                                <option value="vendedor" <?php echo (isset($_POST['rol']) && $_POST['rol'] == 'vendedor') ? 'selected' : ''; ?>>Vendedor</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <div class="form-check form-switch mt-2">
                                <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" 
                                    <?php echo (!isset($_POST['activo']) || $_POST['activo']) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="activo">Usuario activo</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Información de Roles</h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6 class="alert-heading">Permisos por rol:</h6>
                        <ul class="mb-0 ps-3">
                            <li><strong>Superadministrador:</strong> Acceso completo al sistema</li>
                            <li><strong>Administrador:</strong> Gestionar usuarios y contenido</li>
                            <li><strong>Vendedor:</strong> Solo crear y gestionar cotizaciones</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="card card-civit">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-civit-primary btn-lg w-100 mb-2">
                        <i class="fas fa-save me-2"></i> Crear Usuario
                    </button>
                    <button type="reset" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-undo me-1"></i> Limpiar Formulario
                    </button>
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
        
        if (password.value.length < 6) {
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