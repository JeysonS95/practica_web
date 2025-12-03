<?php
// procesar_crud.php
// Script de Backend para manejar las operaciones CRUD

header('Content-Type: application/json');
$data = json_decode(file_get_contents('php://input'), true);
$action = $data['action'] ?? null;
$response = ['success' => false, 'message' => 'Acción no reconocida.'];

// =======================================================
// CONEXIÓN A LA BASE DE DATOS (MySQL/XAMPP)
// =======================================================
// Asegúrese de que estos parámetros coincidan con su configuración de XAMPP.
$host = 'localhost';
$db   = 'mi_base_datos_crud'; // Nombre de la BD creada
$user = 'root';              // Usuario por defecto en XAMPP
$pass = '';                  // Contraseña por defecto en XAMPP (debe estar vacía)

$dsn = "mysql:host=$host;dbname=$db;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     http_response_code(500); 
     echo json_encode(['success' => false, 'message' => 'Error de conexión con la BD. Revise credenciales: ' . $e->getMessage()]);
     exit();
}

// =======================================================
// LÓGICA DE OPERACIONES CRUD
// =======================================================

try {
    $id = $data['id'] ?? null;
    $nombre = $data['nombre'] ?? null;
    $matricula = $data['matricula'] ?? null;
    $carrera = $data['carrera'] ?? null;

    switch ($action) {
        // --- 1. INSERT (Crear) -------------------------
        case 'INSERT':
            $sql = "INSERT INTO estudiantes (nombre, matricula, carrera) VALUES (?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $matricula, $carrera]);
            $response = ['success' => true, 'message' => "Registro de $nombre insertado correctamente."];
            break;

        // --- 2. READ (Consultar) -------------------------
        case 'READ':
            $sql = "SELECT id, nombre, matricula, carrera FROM estudiantes ORDER BY id DESC";
            $stmt = $pdo->query($sql);
            $records = $stmt->fetchAll();
            
            $response = ['success' => true, 'records' => $records, 'message' => 'Registros consultados exitosamente.'];
            break;
            
        // --- 3. UPDATE (Modificar) -------------------------
        case 'UPDATE':
            $sql = "UPDATE estudiantes SET nombre=?, matricula=?, carrera=? WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nombre, $matricula, $carrera, $id]);
            
            if ($stmt->rowCount() > 0) {
                 $response = ['success' => true, 'message' => "Registro con ID $id modificado correctamente."];
            } else {
                 $response = ['success' => false, 'message' => "No se encontró registro con ID $id."];
            }
            break;
            
        // --- 4. DELETE (Borrar) -------------------------
        case 'DELETE':
            $sql = "DELETE FROM estudiantes WHERE id=?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$id]);
            
            if ($stmt->rowCount() > 0) {
                 $response = ['success' => true, 'message' => "Registro con ID $id borrado correctamente."];
            } else {
                 $response = ['success' => false, 'message' => "No se encontró registro con ID $id para borrar."];
            }
            break;
            
        default:
            $response['message'] = "Acción '$action' no válida.";
            break;
    }
} catch (\PDOException $e) {
    $response['message'] = "Error de BD: " . $e->getMessage();
}

echo json_encode($response);
?>
