<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['action'])) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit();
}

if ($data['action'] === 'signup') {
    $nombre = trim($data['name']);
    $email  = trim($data['email']);
    $pass   = trim($data['password']);

    // Verificar si el correo ya existe
    $stmt = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        echo json_encode(["status" => "error", "message" => "Este correo ya está registrado"]);
        exit();
    }

    $hash = password_hash($pass, PASSWORD_BCRYPT);
    $insert = $conn->prepare("INSERT INTO usuarios (nombre_completo, email, password_hash) VALUES (?, ?, ?)");
    $insert->bind_param("sss", $nombre, $email, $hash);

    if ($insert->execute()) {
        echo json_encode([
            "status" => "success",
            "message" => "Cuenta creada exitosamente",
            "user" => ["id" => $insert->insert_id, "name" => $nombre, "email" => $email]
        ]);
    } else {
        echo json_encode(["status" => "error", "message" => "Error al registrar usuario"]);
    }
}

if ($data['action'] === 'signin') {
    $email = trim($data['email']);
    $pass  = trim($data['password']);

    $stmt = $conn->prepare("SELECT id_usuario, nombre_completo, password_hash FROM usuarios WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        if (password_verify($pass, $row['password_hash'])) {
            echo json_encode([
                "status" => "success",
                "message" => "Sesión iniciada con éxito",
                "user" => ["id" => $row['id_usuario'], "name" => $row['nombre_completo'], "email" => $email]
            ]);
        } else {
            echo json_encode(["status" => "error", "message" => "Contraseña incorrecta"]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Correo no encontrado"]);
    }
}
?>