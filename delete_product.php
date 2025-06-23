<?php
session_start();
include 'koneksi.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login2.php");
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header("Location: my_products.php");
    exit();
}

$product_id = $_GET['id'];
$user_id = $_SESSION['id_user'];

// First verify that the product belongs to the user
$check_query = "SELECT gambar FROM products WHERE id_produk = ? AND id_user = ?";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $product_id, $user_id);
$check_stmt->execute();
$result = $check_stmt->get_result();

if ($result->num_rows === 0) {
    // Product doesn't exist or doesn't belong to user
    header("Location: my_products.php");
    exit();
}

$product = $result->fetch_assoc();

// Delete the product
$delete_query = "DELETE FROM products WHERE id_produk = ? AND id_user = ?";
$delete_stmt = $conn->prepare($delete_query);
$delete_stmt->bind_param("ii", $product_id, $user_id);

if ($delete_stmt->execute()) {
    // If product was successfully deleted, also delete the image file
    if (!empty($product['gambar']) && file_exists($product['gambar'])) {
        unlink($product['gambar']);
    }
    $_SESSION['success_message'] = "Produk berhasil dihapus";
} else {
    $_SESSION['error_message'] = "Gagal menghapus produk";
}

header("Location: my_products.php");
exit();
?> 