<?php
include 'php/database.php';

// Probar conexión
if ($conn->connect_error) {
    echo "Error de conexión: " . $conn->connect_error;
} else {
    echo "¡Conexión exitosa a la base de datos!<br>";
    
    // Probar consulta
    $sql = "SELECT COUNT(*) as total FROM clientes";
    $result = $conn->query($sql);
    
    if ($result) {
        $row = $result->fetch_assoc();
        echo "Número de clientes en la base de datos: " . $row['total'];
    } else {
        echo "Error en la consulta: " . $conn->error;
    }
}

$conn->close();
?>