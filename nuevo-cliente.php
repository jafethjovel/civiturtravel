<?php
// Iniciar sesión primero
session_start();

// Incluir la base de datos con ruta absoluta
require_once __DIR__ . '/php/database.php';

// Verificar si la conexión se estableció
if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos");
}

$page_title = "Nuevo Cliente";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Procesar formulario
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);
    $tipo_documento = trim($_POST['tipo_documento']);
    $numero_documento = trim($_POST['numero_documento']);
    $fecha_nacimiento = !empty($_POST['fecha_nacimiento']) ? trim($_POST['fecha_nacimiento']) : null;
    $nacionalidad = trim($_POST['nacionalidad']);
    $notas = trim($_POST['notas']);
    
    // Validaciones
    if (empty($nombre) || empty($email) || empty($tipo_documento) || empty($numero_documento)) {
        $error = "Por favor complete todos los campos requeridos";
    } else {
        // Verificar si el email ya existe
        if ($stmt = $conn->prepare("SELECT id FROM clientes WHERE email = ?")) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "El email ya está registrado para otro cliente";
            } else {
                $stmt->close();
                
                // Insertar nuevo cliente
                if ($stmt = $conn->prepare("INSERT INTO clientes (nombre, email, telefono, direccion, tipo_documento, numero_documento, fecha_nacimiento, nacionalidad, notas) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
                    $stmt->bind_param("sssssssss", $nombre, $email, $telefono, $direccion, $tipo_documento, $numero_documento, $fecha_nacimiento, $nacionalidad, $notas);
                    
                    if ($stmt->execute()) {
                        $success = "Cliente registrado exitosamente!";
                        
                        // Redirigir o limpiar formulario
                        if (isset($_GET['return_to'])) {
                            header("Location: " . $_GET['return_to']);
                            exit();
                        } else {
                            // Limpiar campos
                            $_POST = array();
                        }
                    } else {
                        $error = "Error al registrar el cliente: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Nuevo Cliente</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="clientes.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver a Clientes
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST" id="clienteForm">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Información Personal</h5>
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
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono"
                                   value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="nacionalidad" class="form-label">Nacionalidad</label>
                            <input type="text" class="form-control" id="nacionalidad" name="nacionalidad"
                                   value="<?php echo isset($_POST['nacionalidad']) ? htmlspecialchars($_POST['nacionalidad']) : ''; ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                   value="<?php echo isset($_POST['fecha_nacimiento']) ? htmlspecialchars($_POST['fecha_nacimiento']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="1"><?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Documentación</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="tipo_documento" class="form-label">Tipo de Documento *</label>
                        <select class="form-select" id="tipo_documento" name="tipo_documento" required>
                            <option value="">Seleccionar tipo...</option>
                            <option value="dui" <?php echo (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'dui') ? 'selected' : ''; ?>>DUI</option>
                            <option value="pasaporte" <?php echo (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'pasaporte') ? 'selected' : ''; ?>>Pasaporte</option>
                            <option value="nit" <?php echo (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'nit') ? 'selected' : ''; ?>>NIT</option>
                            <option value="otros" <?php echo (isset($_POST['tipo_documento']) && $_POST['tipo_documento'] == 'otros') ? 'selected' : ''; ?>>Otros</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="numero_documento" class="form-label">Número de Documento *</label>
                        <input type="text" class="form-control" id="numero_documento" name="numero_documento" required
                               value="<?php echo isset($_POST['numero_documento']) ? htmlspecialchars($_POST['numero_documento']) : ''; ?>">
                    </div>
                </div>
            </div>

            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Notas Adicionales</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <textarea class="form-control" id="notas" name="notas" rows="4" 
                                  placeholder="Información adicional sobre el cliente..."><?php echo isset($_POST['notas']) ? htmlspecialchars($_POST['notas']) : ''; ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card card-civit">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-civit-primary btn-lg w-100 mb-2">
                        <i class="fas fa-save me-2"></i> Guardar Cliente
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
    const formulario = document.getElementById('clienteForm');
    
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
        
        if (!valid) {
            e.preventDefault();
        }
    });
    
    // Formatear número de documento según el tipo
    const tipoDocumento = document.getElementById('tipo_documento');
    const numeroDocumento = document.getElementById('numero_documento');
    
    tipoDocumento.addEventListener('change', function() {
        if (this.value === 'dui') {
            numeroDocumento.placeholder = 'Ej: 171234567';
            numeroDocumento.pattern = '[0-9]{9}';
        } else if (this.value === 'pasaporte') {
            numeroDocumento.placeholder = 'Ej: AB123456';
            numeroDocumento.pattern = '[A-Za-z0-9]{6,12}';
        } else if (this.value === 'nit') {
            numeroDocumento.placeholder = 'Ej: 12345678900015';
            numeroDocumento.pattern = '[0-9]{14}';
        } else {
            numeroDocumento.placeholder = 'Número de documento';
            numeroDocumento.pattern = null;
        }
    });
});
</script>