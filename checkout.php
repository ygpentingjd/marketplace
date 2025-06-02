<?php
session_start();
include 'koneksi.php';

$id_user = $_SESSION['user_id'] ?? 1;

// Ambil semua produk di cart user
$stmt = $conn->prepare("SELECT c.id_cart, c.id_produk, c.qty, p.harga FROM cart c JOIN products p ON c.id_produk = p.id_produk WHERE c.id_user = ?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}

if (count($cart_items) === 0) {
    echo "<h3>Tidak ada produk di keranjang untuk di-checkout.</h3>";
    echo "<a href='cart_view.php'>Kembali ke Keranjang</a>";
    exit();
}

$success = true;
foreach ($cart_items as $item) {
    $total = $item['harga'] * $item['qty'];
    $stmt = $conn->prepare("INSERT INTO orders (id_user, id_produk, qty, total, status, created_at) VALUES (?, ?, ?, ?, 'pending', NOW())");
    $stmt->bind_param("iiii", $id_user, $item['id_produk'], $item['qty'], $total);
    if (!$stmt->execute()) {
        $success = false;
        break;
    }
}

if ($success) {
    // Hapus isi cart user
    $del = $conn->prepare("DELETE FROM cart WHERE id_user = ?");
    $del->bind_param("i", $id_user);
    $del->execute();
    header("Location: orders_view.php?success=1");
    exit();
} else {
    echo "<h3>Gagal melakukan checkout. Silakan coba lagi.</h3>";
    echo "<a href='cart_view.php'>Kembali ke Keranjang</a>";
    exit();
}
