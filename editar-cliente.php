<?php
// Iniciar sesión primero
session_start();

// Incluir la base de datos con ruta absoluta
require_once __DIR__ . '/php/database.php';

// Verificar si la conexión se estableció
if (!isset($conn)) {
    die("Error: No se pudo conectar a la base de datos");
}

$page_title = "Editar Cliente";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Obtener ID del cliente a editar
$cliente_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$cliente = null;

if ($cliente_id > 0) {
    // Obtener información del cliente
    if ($stmt = $conn->prepare("SELECT * FROM clientes WHERE id = ?")) {
        $stmt->bind_param("i", $cliente_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $cliente = $result->fetch_assoc();
        $stmt->close();
    }
}

// Si no se encuentra el cliente, redirigir
if (!$cliente) {
    header("Location: clientes.php");
    exit();
}

// Procesar formulario de actualización
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
        // Verificar si el email ya existe para otro cliente
        if ($stmt = $conn->prepare("SELECT id FROM clientes WHERE email = ? AND id != ?")) {
            $stmt->bind_param("si", $email, $cliente_id);
            $stmt->execute();
            $stmt->store_result();
            
            if ($stmt->num_rows > 0) {
                $error = "El email ya está registrado para otro cliente";
            } else {
                $stmt->close();
                
                // Actualizar cliente
                if ($stmt = $conn->prepare("UPDATE clientes SET nombre = ?, email = ?, telefono = ?, direccion = ?, tipo_documento = ?, numero_documento = ?, fecha_nacimiento = ?, nacionalidad = ?, notas = ?, fecha_actualizacion = NOW() WHERE id = ?")) {
                    $stmt->bind_param("sssssssssi", $nombre, $email, $telefono, $direccion, $tipo_documento, $numero_documento, $fecha_nacimiento, $nacionalidad, $notas, $cliente_id);
                    
                    if ($stmt->execute()) {
                        $success = "Cliente actualizado exitosamente!";
                        
                        // Actualizar datos locales
                        $cliente['nombre'] = $nombre;
                        $cliente['email'] = $email;
                        $cliente['telefono'] = $telefono;
                        $cliente['direccion'] = $direccion;
                        $cliente['tipo_documento'] = $tipo_documento;
                        $cliente['numero_documento'] = $numero_documento;
                        $cliente['fecha_nacimiento'] = $fecha_nacimiento;
                        $cliente['nacionalidad'] = $nacionalidad;
                        $cliente['notas'] = $notas;
                        
                    } else {
                        $error = "Error al actualizar el cliente: " . $stmt->error;
                    }
                    $stmt->close();
                }
            }
        }
    }
}
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Editar Cliente</h1>
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
                                   value="<?php echo htmlspecialchars($cliente['nombre']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required
                                   value="<?php echo htmlspecialchars($cliente['email']); ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono"
                                   value="<?php echo htmlspecialchars($cliente['telefono']); ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="nacionalidad" class="form-label">Nacionalidad</label>
                            <input type="text" class="form-control" id="nacionalidad" name="nacionalidad"
                                   value="<?php echo htmlspecialchars($cliente['nacionalidad']); ?>">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="fecha_nacimiento" class="form-label">Fecha de Nacimiento</label>
                            <input type="date" class="form-control" id="fecha_nacimiento" name="fecha_nacimiento"
                                   value="<?php echo $cliente['fecha_nacimiento']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="direccion" class="form-label">Dirección</label>
                            <textarea class="form-control" id="direccion" name="direccion" rows="1"><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
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
                            <option value="dui" <?php echo $cliente['tipo_documento'] == 'dui' ? 'selected' : ''; ?>>DUI</option>
                            <option value="pasaporte" <?php echo $cliente['tipo_documento'] == 'pasaporte' ? 'selected' : ''; ?>>Pasaporte</option>
                            <option value="nit" <?php echo $cliente['tipo_documento'] == 'nit' ? 'selected' : ''; ?>>NIT</option>
                            <option value="otros" <?php echo $cliente['tipo_documento'] == 'otros' ? 'selected' : ''; ?>>Otros</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="numero_documento" class="form-label">Número de Documento *</label>
                        <input type="text" class="form-control" id="numero_documento" name="numero_documento" required
                               value="<?php echo htmlspecialchars($cliente['numero_documento']); ?>">
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
                                  placeholder="Información adicional sobre el cliente..."><?php echo htmlspecialchars($cliente['notas']); ?></textarea>
                    </div>
                </div>
            </div>

            <div class="card card-civit">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-civit-primary btn-lg w-100 mb-2">
                        <i class="fas fa-save me-2"></i> Actualizar Cliente
                    </button>
                    <a href="clientes.php" class="btn btn-outline-secondary btn-sm w-100">
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
    
    // Inicializar placeholder según el tipo actual
    if (tipoDocumento.value === 'dui') {
        numeroDocumento.placeholder = 'Ej: 171234567';
        numeroDocumento.pattern = '[0-9]{9}';
    } else if (tipoDocumento.value === 'pasaporte') {
        numeroDocumento.placeholder = 'Ej: AB123456';
        numeroDocumento.pattern = '[A-Za-z0-9]{6,12}';
    } else if (tipoDocumento.value === 'nit') {
        numeroDocumento.placeholder = 'Ej: 12345678900015';
        numeroDocumento.pattern = '[0-9]{14}';
    }
});
</script>