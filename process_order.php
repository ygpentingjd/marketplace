<?php
session_start();
require_once 'koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['id_user'])) {
    http_response_code(401);
    die(json_encode(['error' => 'User not logged in']));
}

// Get POST data
$data = json_decode(file_get_contents('php://input'), true);

// Validate required fields
if (empty($data['items']) || empty($data['payment'])) {
    http_response_code(400);
    die(json_encode(['error' => 'Invalid order data']));
}

try {
    // Begin transaction
    $conn->begin_transaction();

    // Insert order
    $stmt = $conn->prepare("INSERT INTO orders 
        (id_user, total_harga, status_pesanan, verification_status) 
        VALUES (?, ?, 'diproses', 'pending')");
    $stmt->bind_param("id", $_SESSION['id_user'], $data['payment']['total']);
    $stmt->execute();
    $orderId = $conn->insert_id;

    // Insert order items
    $itemStmt = $conn->prepare("INSERT INTO order_items 
        (id_order, id_product, quantity, price) 
        VALUES (?, ?, ?, ?)");

    foreach ($data['items'] as $item) {
        $itemStmt->bind_param(
            "iiid",
            $orderId,
            $item['id'],
            $item['quantity'],
            $item['price']
        );
        $itemStmt->execute();
    }

    // Commit transaction
    $conn->commit();

    echo json_encode(['success' => true, 'order_id' => $orderId]);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(500);
    die(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
}
