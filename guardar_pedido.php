<?php
header('Content-Type: application/json');
require_once 'conexion.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "Sin datos de orden"]);
    exit();
}

$tracking = $data['tracking'];
$id_usuario = isset($data['userId']) ? intval($data['userId']) : null;
$cliente = $data['clientName'];
$metodo = $data['paymentMethod'];
$subtotal = floatval($data['subtotal']);
$descuento = floatval($data['discount']);
$envio = floatval($data['shipping']);
$total = floatval($data['total']);
$items = $data['items'];

$stmt = $conn->prepare("INSERT INTO pedidos (codigo_rastreo, id_usuario, cliente_nombre, metodo_pago, subtotal, descuento, envio, total) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param("sisddddd", $tracking, $id_usuario, $cliente, $metodo, $subtotal, $descuento, $envio, $total);

if ($stmt->execute()) {
    $id_pedido = $stmt->insert_id;
    $item_stmt = $conn->prepare("INSERT INTO detalle_pedido (id_pedido, nombre_producto, talla, cantidad, precio_unitario) VALUES (?, ?, ?, ?, ?)");

    foreach ($items as $item) {
        $nombre = $item['name'];
        $talla = $item['selectedSize'];
        $cant = intval($item['qty']);
        $precio = floatval($item['price']);
        $item_stmt->bind_param("issid", $id_pedido, $nombre, $talla, $cant, $precio);
        $item_stmt->execute();
    }

    echo json_encode(["status" => "success", "message" => "Pedido guardado en MySQL", "id_pedido" => $id_pedido]);
} else {
    echo json_encode(["status" => "error", "message" => "No se pudo registrar el pedido"]);
}
?>