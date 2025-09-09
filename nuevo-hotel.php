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

// Verificar permisos
verificarAutenticacion();
verificarPermisos(ROLES_VENDEDOR);

$page_title = "Nuevo Hotel";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Procesar formulario
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $cadena_hotelera = trim($_POST['cadena_hotelera']);
    $direccion = trim($_POST['direccion']);
    $ciudad = trim($_POST['ciudad']);
    $pais = trim($_POST['pais']);
    $moneda = trim($_POST['moneda']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $sitio_web = trim($_POST['sitio_web']);
    $categoria = intval($_POST['categoria']);
    $descripcion = trim($_POST['descripcion']);
    $check_in = trim($_POST['check_in']);
    $check_out = trim($_POST['check_out']);
    $activo = isset($_POST['activo']) ? 1 : 0;
    
    // Validaciones (sin precio)
    if (empty($nombre) || empty($ciudad) || empty($pais) || empty($moneda) || $categoria < 1 || $categoria > 5) {
        $error = "Por favor complete todos los campos requeridos correctamente";
    } else {
        // Insertar sin precio
        if ($stmt = $conn->prepare("INSERT INTO hoteles (nombre, cadena_hotelera, direccion, ciudad, pais, moneda, telefono, email, sitio_web, categoria, descripcion, check_in, check_out, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)")) {
            $stmt->bind_param("sssssssssisssi", $nombre, $cadena_hotelera, $direccion, $ciudad, $pais, $moneda, $telefono, $email, $sitio_web, $categoria, $descripcion, $check_in, $check_out, $activo);
            
            if ($stmt->execute()) {
                $hotel_id = $stmt->insert_id;
                $success = "Hotel registrado exitosamente! Ahora puede agregar las habitaciones.";
                
                // Redirigir a gestión de habitaciones
                echo "<script>setTimeout(function() { window.location.href = 'habitaciones.php?hotel_id=" . $hotel_id . "'; }, 2000);</script>";
            } else {
                $error = "Error al registrar el hotel: " . $stmt->error;
            }
            $stmt->close();
        }
    }
}

// Lista de países del mundo (lista abreviada)
$paises = [
    'Alemania', 'Argentina', 'Australia', 'Brasil', 'Canadá', 'Chile', 'China',
    'Colombia', 'Corea del Sur', 'Costa Rica', 'Egipto', 'El Salvador', 'Emiratos Árabes Unidos',
    'España', 'Estados Unidos', 'Filipinas', 'Francia', 'Grecia', 'Guatemala',
    'India', 'Indonesia', 'Italia', 'Japón', 'Malasia', 'México', 'Noruega',
    'Nueva Zelanda', 'Países Bajos', 'Panamá', 'Perú', 'Portugal', 'Reino Unido',
    'República Dominicana', 'Rusia', 'Singapur', 'Sudáfrica', 'Suecia', 'Suiza',
    'Tailandia', 'Turquía', 'Uruguay', 'Venezuela'
];
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Nuevo Hotel</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="hoteles.php" class="btn btn-sm btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Volver a Hoteles
        </a>
    </div>
</div>

<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?php echo $error; ?></div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<form method="POST" id="hotelForm">
    <div class="row">
        <div class="col-md-8">
            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Información del Hotel</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-8">
                            <label for="nombre" class="form-label">Nombre del Hotel *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required
                                   value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="cadena_hotelera" class="form-label">Cadena Hotelera</label>
                            <input type="text" class="form-control" id="cadena_hotelera" name="cadena_hotelera"
                                   value="<?php echo isset($_POST['cadena_hotelera']) ? htmlspecialchars($_POST['cadena_hotelera']) : ''; ?>">
                        </div>
                    </div>

<div class="row mb-3">
    <div class="col-md-6">
        <label for="ciudad" class="form-label">Ciudad *</label>
        <input type="text" class="form-control" id="ciudad" name="ciudad" required
               value="<?php echo isset($_POST['ciudad']) ? htmlspecialchars($_POST['ciudad']) : ''; ?>"
               placeholder="Ej: París, Tokio, Nueva York">
    </div>
    <div class="col-md-6">
        <label for="pais" class="form-label">País *</label>
        <select class="form-select" id="pais" name="pais" required>
            <option value="">Seleccionar país...</option>
            <?php foreach ($paises as $pais_option): ?>
                <option value="<?php echo $pais_option; ?>" <?php echo (isset($_POST['pais']) && $_POST['pais'] == $pais_option) ? 'selected' : ''; ?>>
                    <?php echo $pais_option; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
</div>
    <!-- En el formulario de nuevo hotel, añadir: -->
<div class="row mb-3">
    <div class="col-md-6">
        <label for="moneda" class="form-label">Moneda Principal *</label>
        <select class="form-select" id="moneda" name="moneda" required>
            <option value="">Seleccionar moneda...</option>
            <option value="USD" <?php echo (isset($_POST['moneda']) && $_POST['moneda'] == 'USD') ? 'selected' : ''; ?>>USD - Dólar Americano</option>
            <option value="EUR" <?php echo (isset($_POST['moneda']) && $_POST['moneda'] == 'EUR') ? 'selected' : ''; ?>>EUR - Euro</option>
            <option value="GBP" <?php echo (isset($_POST['moneda']) && $_POST['moneda'] == 'GBP') ? 'selected' : ''; ?>>GBP - Libra Esterlina</option>
            <option value="CAD" <?php echo (isset($_POST['moneda']) && $_POST['moneda'] == 'CAD') ? 'selected' : ''; ?>>CAD - Dólar Canadiense</option>
            <option value="MXN" <?php echo (isset($_POST['moneda']) && $_POST['moneda'] == 'MXN') ? 'selected' : ''; ?>>MXN - Peso Mexicano</option>
            <option value="BRL" <?php echo (isset($_POST['moneda']) && $_POST['moneda'] == 'BRL') ? 'selected' : ''; ?>>BRL - Real Brasileño</option>
            <option value="COP" <?php echo (isset($_POST['moneda']) && $_POST['moneda'] == 'COP') ? 'selected' : ''; ?>>COP - Peso Colombiano</option>
            <option value="PEN" <?php echo (isset($_POST['moneda']) && $_POST['moneda'] == 'PEN') ? 'selected' : ''; ?>>PEN - Sol Peruano</option>
            <option value="CLP" <?php echo (isset($_POST['moneda']) && $_POST['moneda'] == 'CLP') ? 'selected' : ''; ?>>CLP - Peso Chileno</option>
            <option value="ARS" <?php echo (isset($_POST['moneda']) && $_POST['moneda'] == 'ARS') ? 'selected' : ''; ?>>ARS - Peso Argentino</option>
        </select>
    </div>
</div>

<div class="mb-3">
                        <label for="direccion" class="form-label">Dirección</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="2"><?php echo isset($_POST['direccion']) ? htmlspecialchars($_POST['direccion']) : ''; ?></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label for="categoria" class="form-label">Categoría (Estrellas) *</label>
                            <select class="form-select" id="categoria" name="categoria" required>
                                <option value="">Seleccionar...</option>
                                <option value="1" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 1) ? 'selected' : ''; ?>>1 Estrella</option>
                                <option value="2" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 2) ? 'selected' : ''; ?>>2 Estrellas</option>
                                <option value="3" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 3) ? 'selected' : ''; ?>>3 Estrellas</option>
                                <option value="4" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 4) ? 'selected' : ''; ?>>4 Estrellas</option>
                                <option value="5" <?php echo (isset($_POST['categoria']) && $_POST['categoria'] == 5) ? 'selected' : ''; ?>>5 Estrellas</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="check_in" class="form-label">Check-in</label>
                            <input type="time" class="form-control" id="check_in" name="check_in"
                                   value="<?php echo isset($_POST['check_in']) ? $_POST['check_in'] : '14:00'; ?>">
                        </div>
                        <div class="col-md-4">
                            <label for="check_out" class="form-label">Check-out</label>
                            <input type="time" class="form-control" id="check_out" name="check_out"
                                   value="<?php echo isset($_POST['check_out']) ? $_POST['check_out'] : '12:00'; ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Información de Contacto</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono"
                                   value="<?php echo isset($_POST['telefono']) ? htmlspecialchars($_POST['telefono']) : ''; ?>">
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="sitio_web" class="form-label">Sitio Web</label>
                        <input type="url" class="form-control" id="sitio_web" name="sitio_web"
                               value="<?php echo isset($_POST['sitio_web']) ? htmlspecialchars($_POST['sitio_web']) : ''; ?>" placeholder="https://">
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card card-civit mb-4">
                <div class="card-header card-header-civit">
                    <h5 class="mb-0">Configuración Adicional</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label for="descripcion" class="form-label">Descripción del Hotel</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="5" 
                                  placeholder="Describa las características y servicios del hotel..."><?php echo isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : ''; ?></textarea>
                    </div>

                    <div class="form-check form-switch mb-3">
                        <input class="form-check-input" type="checkbox" id="activo" name="activo" value="1" 
                            <?php echo (!isset($_POST['activo']) || $_POST['activo']) ? 'checked' : ''; ?>>
                        <label class="form-check-label" for="activo">Hotel activo</label>
                    </div>
                </div>
            </div>

            <div class="card card-civit">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-civit-primary btn-lg w-100 mb-2">
                        <i class="fas fa-save me-2"></i> Guardar Hotel
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
    const formulario = document.getElementById('hotelForm');
    
    formulario.addEventListener('submit', function(e) {
        let valid = true;
        
        // Validar categoría
        const categoria = document.getElementById('categoria');
        if (categoria.value === '') {
            alert('Por favor seleccione una categoría de hotel');
            categoria.focus();
            valid = false;
        }
        
        // Validar moneda
        const moneda = document.getElementById('moneda');
        if (moneda.value === '') {
            alert('Por favor seleccione una moneda');
            moneda.focus();
            valid = false;
        }
        
        // Validar email si se proporciona
        const email = document.getElementById('email');
        if (email.value && !email.value.includes('@')) {
            alert('Por favor ingrese un email válido');
            email.focus();
            valid = false;
        }
        
        if (!valid) {
            e.preventDefault();
        }
    });
});
</script>