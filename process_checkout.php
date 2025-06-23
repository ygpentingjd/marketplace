<?php
session_start();
include 'koneksi.php';

// Cek jika user belum login
if (!isset($_SESSION['id_user'])) {
    echo json_encode(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
    exit();
}

// Terima data dari form
$items = json_decode($_POST['items'], true);
$payment_method = $_POST['payment_method'];
$note = $_POST['note'];
$user_id = $_SESSION['id_user'];

try {
    // Mulai transaksi
    $conn->begin_transaction();

    // Hitung total harga
    $total_harga = 0;
    foreach ($items as $item) {
        $total_harga += ($item['harga'] * $item['quantity']);
    }

    // Insert ke tabel orders
    $order_query = "INSERT INTO orders (id_user, total_harga, status_pesanan, tanggal_pemesanan) 
                    VALUES (?, ?, 'diproses', NOW())";
    $stmt = $conn->prepare($order_query);
    $stmt->bind_param("id", $user_id, $total_harga);
    $stmt->execute();
    $order_id = $conn->insert_id;

    // Insert ke tabel order_details
    $detail_query = "INSERT INTO order_details (id_order, id_produk, jumlah, subtotal) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($detail_query);

    foreach ($items as $item) {
        $subtotal = $item['harga'] * $item['quantity'];
        $stmt->bind_param("iiid", $order_id, $item['id_produk'], $item['quantity'], $subtotal);
        $stmt->execute();

        // Update stok produk
        $update_stok = "UPDATE products SET stok = stok - ? WHERE id_produk = ?";
        $stmt_stok = $conn->prepare($update_stok);
        $stmt_stok->bind_param("ii", $item['quantity'], $item['id_produk']);
        $stmt_stok->execute();
    }

    // Mapping payment method
    $metode_db = 'Cashless';
    if (strtolower($payment_method) === 'cod') {
        $metode_db = 'COD';
    }
    $payment_query = "INSERT INTO payment (id_order, metode, status_pembayaran) VALUES (?, ?, 'pending')";
    $stmt = $conn->prepare($payment_query);
    $stmt->bind_param("is", $order_id, $metode_db);
    $stmt->execute();

    // Insert ke tabel transactions
    $transaction_query = "INSERT INTO transactions (id_order, metode_pembayaran, status_pembayaran) 
                         VALUES (?, ?, 'pending')";
    $stmt = $conn->prepare($transaction_query);
    $stmt->bind_param("is", $order_id, $metode_db);
    $stmt->execute();

    // Hapus item dari cart
    $delete_cart = "DELETE FROM cart WHERE id_user = ?";
    $stmt = $conn->prepare($delete_cart);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    // Commit transaksi
    $conn->commit();

    echo json_encode(['success' => true, 'message' => 'Pesanan berhasil dibuat']);
} catch (Exception $e) {
    // Rollback jika terjadi error
    $conn->rollback();
    echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan: ' . $e->getMessage()]);
}
