<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "aurora_store";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Fallo de conexión a MySQL"]);
    exit();
}
$conn->set_charset("utf8mb4");
?>