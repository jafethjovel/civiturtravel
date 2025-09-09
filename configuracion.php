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

// Verificar permisos (solo admin y superadmin)
verificarAutenticacion();
verificarPermisos(ROLES_ADMIN);

$page_title = "Configuración del Sistema";
require_once 'includes/header.php';
require_once 'includes/sidebar.php';

// Procesar formularios de configuración
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $seccion = $_POST['seccion'] ?? '';
    
    try {
        switch ($seccion) {
            case 'general':
                $configuraciones_general = [
                    'nombre_sistema' => $_POST['nombre_sistema'],
                    'moneda' => $_POST['moneda'],
                    'formato_fecha' => $_POST['formato_fecha'],
                    'timezone' => $_POST['timezone'],
                    'idioma' => $_POST['idioma'],
                    'registros_pagina' => $_POST['registros_pagina']
                ];
                
                $tipos = [
                    'registros_pagina' => 'number'
                ];
                
                if (guardarConfiguraciones('general', $configuraciones_general, $tipos)) {
                    $mensaje = "Configuración general actualizada correctamente";
                    $tipo_mensaje = 'success';
                    // Re-aplicar configuraciones
                    aplicarConfiguracionesSistema();
                } else {
                    throw new Exception("Error al guardar la configuración general");
                }
                break;
                
            case 'empresa':
                $configuraciones_empresa = [
                    'nombre' => $_POST['empresa_nombre'],
                    'nrc' => $_POST['empresa_nrc'],
                    'telefono' => $_POST['empresa_telefono'],
                    'email' => $_POST['empresa_email'],
                    'direccion' => $_POST['empresa_direccion'],
                    'website' => $_POST['empresa_website']
                ];
                
                if (guardarConfiguraciones('empresa', $configuraciones_empresa)) {
                    $mensaje = "Información de la empresa actualizada correctamente";
                    $tipo_mensaje = 'success';
                } else {
                    throw new Exception("Error al guardar la información de la empresa");
                }
                break;
                
            case 'correo':
                $configuraciones_correo = [
                    'smtp_host' => $_POST['smtp_host'],
                    'smtp_puerto' => $_POST['smtp_puerto'],
                    'smtp_seguridad' => $_POST['smtp_seguridad'],
                    'smtp_usuario' => $_POST['smtp_usuario']
                ];
                
                $tipos = [
                    'smtp_puerto' => 'number'
                ];
                
                // Solo actualizar contraseña si se proporcionó
                if (!empty($_POST['smtp_password'])) {
                    $configuraciones_correo['smtp_password'] = $_POST['smtp_password'];
                }
                
                if (guardarConfiguraciones('correo', $configuraciones_correo, $tipos)) {
                    $mensaje = "Configuración de correo actualizada correctamente";
                    $tipo_mensaje = 'success';
                } else {
                    throw new Exception("Error al guardar la configuración de correo");
                }
                break;
        }
    } catch (Exception $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_mensaje = 'danger';
    }
    
    // Recargar configuraciones después de guardar
    $configuraciones = [
        'general' => obtenerConfiguraciones('general'),
        'empresa' => obtenerConfiguraciones('empresa'),
        'correo' => obtenerConfiguraciones('correo')
    ];
}

// Obtener configuraciones actuales (esto es un ejemplo, en una app real vendría de la BD)
require_once __DIR__ . '/php/config.php';

// Obtener configuraciones actuales desde la base de datos
$configuraciones = [
    'general' => obtenerConfiguraciones('general'),
    'empresa' => obtenerConfiguraciones('empresa'),
    'correo' => obtenerConfiguraciones('correo')
];

// Aplicar configuraciones al sistema
aplicarConfiguracionesSistema();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
    <h1 class="h2">Configuración del Sistema</h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <button class="btn btn-sm btn-outline-secondary me-2" onclick="realizarBackup()">
            <i class="fas fa-database me-1"></i> Backup Ahora
        </button>
        <button class="btn btn-sm btn-civit-primary" onclick="probarConfiguracion()">
            <i class="fas fa-test me-1"></i> Probar Configuración
        </button>
    </div>
</div>

<?php if ($mensaje): ?>
<div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show" role="alert">
    <?php echo $mensaje; ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>
<?php endif; ?>

<div class="row">
    <!-- Navegación de Configuración -->
    <div class="col-md-3">
        <div class="card card-civit">
            <div class="card-header card-header-civit">
                <h6 class="mb-0">Secciones de Configuración</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="#general" class="list-group-item list-group-item-action active" data-bs-toggle="list">
                    <i class="fas fa-cog me-2"></i> General
                </a>
                <a href="#empresa" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-building me-2"></i> Información Empresa
                </a>
                <a href="#correo" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-envelope me-2"></i> Configuración Correo
                </a>
                <a href="#backup" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-database me-2"></i> Backup & Restore
                </a>
                <a href="#seguridad" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-shield-alt me-2"></i> Seguridad
                </a>
                <a href="#api" class="list-group-item list-group-item-action" data-bs-toggle="list">
                    <i class="fas fa-code me-2"></i> APIs
                </a>
            </div>
        </div>
        
        <div class="card card-civit mt-3">
            <div class="card-body text-center">
                <div class="mb-3">
                    <i class="fas fa-info-circle fa-2x text-info"></i>
                </div>
                <h6>Estado del Sistema</h6>
                <div class="d-flex justify-content-between small mb-1">
                    <span>PHP Version:</span>
                    <span class="text-success"><?php echo PHP_VERSION; ?></span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span>Base de Datos:</span>
                    <span class="text-success">MySQL</span>
                </div>
                <div class="d-flex justify-content-between small mb-1">
                    <span>Espacio Libre:</span>
                    <span class="text-success"><?php echo round(disk_free_space("/") / (1024 * 1024 * 1024), 2); ?> GB</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Contenido de Configuración -->
    <div class="col-md-9">
        <div class="tab-content">
            <!-- Sección General -->
            <div class="tab-pane fade show active" id="general">
                <div class="card card-civit">
                    <div class="card-header card-header-civit">
                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Configuración General</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="seccion" value="general">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nombre_sistema" class="form-label">Nombre del Sistema</label>
                                    <input type="text" class="form-control" id="nombre_sistema" name="nombre_sistema" 
                                           value="<?php echo $configuraciones['general']['nombre_sistema']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="moneda" class="form-label">Moneda Principal</label>
                                    <select class="form-select" id="moneda" name="moneda" required>
                                        <option value="USD" <?php echo $configuraciones['general']['moneda'] == 'USD' ? 'selected' : ''; ?>>Dólar Americano (USD)</option>
                                        <option value="EUR" <?php echo $configuraciones['general']['moneda'] == 'EUR' ? 'selected' : ''; ?>>Euro (EUR)</option>
</select>
                                </div>
                            </div>


                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="formato_fecha" class="form-label">Formato de Fecha</label>
                                    <select class="form-select" id="formato_fecha" name="formato_fecha" required>
                                        <option value="d/m/Y" <?php echo $configuraciones['general']['formato_fecha'] == 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/AAAA (31/12/2023)</option>
                                        <option value="m/d/Y" <?php echo $configuraciones['general']['formato_fecha'] == 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/AAAA (12/31/2023)</option>
                                        <option value="Y-m-d" <?php echo $configuraciones['general']['formato_fecha'] == 'Y-m-d' ? 'selected' : ''; ?>>AAAA-MM-DD (2023-12-31)</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="timezone" class="form-label">Zona Horaria</label>
                                    <select class="form-select" id="timezone" name="timezone" required>
                                        <option value="America/El_Salvador" <?php echo $configuraciones['general']['timezone'] == 'America/El_Salvador' ? 'selected' : ''; ?>>El Salvador (GMT-6)</option>
                                        <option value="America/Mexico_City" <?php echo $configuraciones['general']['timezone'] == 'America/Mexico_City' ? 'selected' : ''; ?>>México (GMT-6)</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="idioma" class="form-label">Idioma</label>
                                    <select class="form-select" id="idioma" name="idioma" required>
                                        <option value="es" <?php echo $configuraciones['general']['idioma'] == 'es' ? 'selected' : ''; ?>>Español</option>
                                        <option value="en" <?php echo $configuraciones['general']['idioma'] == 'en' ? 'selected' : ''; ?>>English</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="registros_pagina" class="form-label">Registros por Página</label>
                                    <select class="form-select" id="registros_pagina" name="registros_pagina" required>
                                        <option value="10">10 registros</option>
                                        <option value="25">25 registros</option>
                                        <option value="50">50 registros</option>
                                        <option value="100">100 registros</option>
                                    </select>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-civit-primary">
                                    <i class="fas fa-save me-1"></i> Guardar Configuración
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sección Información Empresa -->
            <div class="tab-pane fade" id="empresa">
                <div class="card card-civit">
                    <div class="card-header card-header-civit">
                        <h5 class="mb-0"><i class="fas fa-building me-2"></i>Información de la Empresa</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="seccion" value="empresa">
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="empresa_nombre" class="form-label">Nombre de la Empresa</label>
                                    <input type="text" class="form-control" id="empresa_nombre" name="empresa_nombre" 
                                           value="<?php echo $configuraciones['empresa']['nombre']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="empresa_nrc" class="form-label">NRC/Identificación</label>
                                    <input type="text" class="form-control" id="empresa_nrc" name="empresa_nrc" 
                                           value="<?php echo $configuraciones['empresa']['nrc']; ?>" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="empresa_telefono" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control" id="empresa_telefono" name="empresa_telefono" 
                                           value="<?php echo $configuraciones['empresa']['telefono']; ?>">
                                </div>
                                <div class="col-md-6">
                                    <label for="empresa_email" class="form-label">Email</label>
                                    <input type="email" class="form-control" id="empresa_email" name="empresa_email" 
                                           value="<?php echo $configuraciones['empresa']['email']; ?>" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="empresa_direccion" class="form-label">Dirección</label>
                                <textarea class="form-control" id="empresa_direccion" name="empresa_direccion" rows="2"><?php echo $configuraciones['empresa']['direccion']; ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="empresa_website" class="form-label">Sitio Web</label>
                                <input type="url" class="form-control" id="empresa_website" name="empresa_website" 
                                       value="<?php echo $configuraciones['empresa']['website']; ?>" placeholder="https://">
                            </div>

                            <div class="text-end">
                                <button type="submit" class="btn btn-civit-primary">
                                    <i class="fas fa-save me-1"></i> Guardar Información
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sección Configuración Correo -->
            <div class="tab-pane fade" id="correo">
                <div class="card card-civit">
                    <div class="card-header card-header-civit">
                        <h5 class="mb-0"><i class="fas fa-envelope me-2"></i>Configuración de Correo Electrónico</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="seccion" value="correo">
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>
                                Esta configuración se utiliza para el envío de notificaciones y correos automáticos del sistema.
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="smtp_host" class="form-label">Servidor SMTP</label>
                                    <input type="text" class="form-control" id="smtp_host" name="smtp_host" 
                                           value="<?php echo $configuraciones['correo']['smtp_host']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="smtp_puerto" class="form-label">Puerto SMTP</label>
                                    <input type="number" class="form-control" id="smtp_puerto" name="smtp_puerto" 
                                           value="<?php echo $configuraciones['correo']['smtp_puerto']; ?>" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="smtp_usuario" class="form-label">Usuario SMTP</label>
                                    <input type="text" class="form-control" id="smtp_usuario" name="smtp_usuario" 
                                           value="<?php echo $configuraciones['correo']['smtp_usuario']; ?>" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="smtp_seguridad" class="form-label">Seguridad</label>
                                    <select class="form-select" id="smtp_seguridad" name="smtp_seguridad" required>
                                        <option value="tls" <?php echo $configuraciones['correo']['smtp_seguridad'] == 'tls' ? 'selected' : ''; ?>>TLS</option>
                                        <option value="ssl" <?php echo $configuraciones['correo']['smtp_seguridad'] == 'ssl' ? 'selected' : ''; ?>>SSL</option>
                                        <option value="" <?php echo $configuraciones['correo']['smtp_seguridad'] == '' ? 'selected' : ''; ?>>Ninguna</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="smtp_password" class="form-label">Contraseña SMTP</label>
                                    <input type="password" class="form-control" id="smtp_password" name="smtp_password" 
                                           placeholder="Ingresar para cambiar">
                                </div>
                                <div class="col-md-6">
                                    <label for="email_from" class="form-label">Email Remitente</label>
                                    <input type="email" class="form-control" id="email_from" name="email_from" 
                                           value="<?php echo $configuraciones['correo']['smtp_usuario']; ?>" required>
                                </div>
                            </div>

                            <div class="text-end">
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="probarEmail()">
                                    <i class="fas fa-test me-1"></i> Probar Configuración
                                </button>
                                <button type="submit" class="btn btn-civit-primary">
                                    <i class="fas fa-save me-1"></i> Guardar Configuración
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Sección Backup & Restore -->
            <div class="tab-pane fade" id="backup">
                <div class="card card-civit">
                    <div class="card-header card-header-civit">
                        <h5 class="mb-0"><i class="fas fa-database me-2"></i>Backup & Restore</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="fas fa-download fa-3x text-primary mb-3"></i>
                                        <h5>Crear Backup</h5>
                                        <p class="text-muted">Genera una copia de seguridad de la base de datos</p>
                                        <button class="btn btn-civit-primary" onclick="realizarBackup()">
                                            <i class="fas fa-database me-1"></i> Generar Backup
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light">
                                    <div class="card-body text-center">
                                        <i class="fas fa-upload fa-3x text-warning mb-3"></i>
                                        <h5>Restaurar Backup</h5>
                                        <p class="text-muted">Restaura la base de datos desde un backup</p>
                                        <input type="file" class="d-none" id="backupFile" accept=".sql,.gz">
                                        <button class="btn btn-outline-warning" onclick="document.getElementById('backupFile').click()">
                                            <i class="fas fa-upload me-1"></i> Seleccionar Archivo
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4">
                            <h6>Backups Recientes</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Archivo</th>
                                            <th>Fecha</th>
                                            <th>Tamaño</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-3">
                                                No se encontraron backups
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
<div class="alert alert-info mt-3">
    <i class="fas fa-info-circle me-2"></i>
    Zona horaria actual del servidor: <strong><?php echo date_default_timezone_get(); ?></strong>
    <br>Hora actual del sistema: <strong><?php echo date('Y-m-d H:i:s'); ?></strong>
</div>
            <!-- Otras secciones (seguridad, APIs) se pueden implementar de manera similar -->
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

<script>
// Funciones para la configuración
function realizarBackup() {
    if (confirm('¿Estás seguro de que deseas generar un backup de la base de datos?')) {
        // Mostrar loading
        const btn = event.target;
        const originalText = btn.innerHTML;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Generando...';
        btn.disabled = true;
        
        // Simular proceso de backup (en una app real sería una llamada AJAX)
        setTimeout(() => {
            alert('Backup generado exitosamente');
            btn.innerHTML = originalText;
            btn.disabled = false;
        }, 2000);
    }
}

function probarConfiguracion() {
    alert('Función de prueba de configuración será implementada');
}

function probarEmail() {
    const email = prompt('Ingresa un email para enviar la prueba:');
    if (email) {
        alert(`Email de prueba será enviado a: ${email}`);
        // Aquí iría la llamada AJAX para probar el envío de correo
    }
}

// Inicializar tooltips
document.addEventListener('DOMContentLoaded', function() {
    // Activar la primera pestaña
    const firstTab = new bootstrap.Tab(document.querySelector('[data-bs-toggle="list"]'));
    firstTab.show();
    
    // Inicializar tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>