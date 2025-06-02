<?php
session_start();
include 'koneksi.php';

$id_user = $_SESSION['user_id'] ?? 1;
$id_produk = $_POST['id_produk'] ?? 0;
$qty = $_POST['qty'] ?? 1;

if ($id_user && $id_produk && $qty) {
    // Cek apakah produk sudah ada di cart
    $stmt = $conn->prepare("SELECT id_cart, qty FROM cart WHERE id_user = ? AND id_produk = ?");
    $stmt->bind_param("ii", $id_user, $id_produk);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        // Update qty
        $new_qty = $row['qty'] + $qty;
        $update = $conn->prepare("UPDATE cart SET qty = ? WHERE id_cart = ?");
        $update->bind_param("ii", $new_qty, $row['id_cart']);
        $update->execute();
    } else {
        // Insert baru
        $insert = $conn->prepare("INSERT INTO cart (id_user, id_produk, qty, created_at) VALUES (?, ?, ?, NOW())");
        $insert->bind_param("iii", $id_user, $id_produk, $qty);
        $insert->execute();
    }
    header("Location: cart_view.php?success=1");
    exit();
} else {
    echo "<h3>Gagal menambah ke keranjang. Data tidak lengkap.</h3>";
    echo "<a href='index.php'>Kembali ke Home</a>";
    exit();
}
