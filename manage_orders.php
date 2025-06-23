<?php
session_start();
include 'koneksi.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login2.php");
    exit();
}

$seller_id = $_SESSION['id_user'];

// Handle order status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_order']) && isset($_POST['status_pesanan'])) {
    $order_id = $_POST['id_order'];
    $new_status = $_POST['status_pesanan'];
    
    // Start transaction
    $conn->begin_transaction();
    
    try {
        // Update order status
        $update_order = "UPDATE orders SET status_pesanan = ? WHERE id_order = ?";
        $stmt_order = $conn->prepare($update_order);
        $stmt_order->bind_param("si", $new_status, $order_id);
        $stmt_order->execute();

        // If status is 'selesai', check payment method and update payment status
        if ($new_status === 'selesai') {
            // Check payment method
            $check_payment = "SELECT p.metode, t.metode_pembayaran 
                            FROM payment p 
                            LEFT JOIN transactions t ON p.id_order = t.id_order 
                            WHERE p.id_order = ?";
            $stmt_check = $conn->prepare($check_payment);
            $stmt_check->bind_param("i", $order_id);
            $stmt_check->execute();
            $payment_result = $stmt_check->get_result();
            
            if ($payment_result->num_rows > 0) {
                $payment_data = $payment_result->fetch_assoc();
                
                // If payment method is COD, update both payment and transactions
                if ($payment_data['metode'] === 'COD') {
                    // Update payment status
                    $update_payment = "UPDATE payment SET status_pembayaran = 'berhasil' WHERE id_order = ?";
                    $stmt_payment = $conn->prepare($update_payment);
                    $stmt_payment->bind_param("i", $order_id);
                    $stmt_payment->execute();

                    // Update transactions status
                    $update_transaction = "UPDATE transactions SET status_pembayaran = 'sukses' WHERE id_order = ?";
                    $stmt_transaction = $conn->prepare($update_transaction);
                    $stmt_transaction->bind_param("i", $order_id);
                    $stmt_transaction->execute();
                }
            }
        }

        $conn->commit();
        $_SESSION['success_message'] = "Status pesanan berhasil diperbarui";
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Gagal memperbarui status pesanan: " . $e->getMessage();
    }
    
    header("Location: manage_orders.php");
    exit();
}

// Get all orders for seller's products
$query = "SELECT o.id_order, o.status_pesanan, o.tanggal_pemesanan, o.total_harga,
          u.nama as buyer_name,
          GROUP_CONCAT(p.nama_produk SEPARATOR ', ') as products,
          py.metode as metode_pembayaran,
          py.status_pembayaran,
          t.status_pembayaran as status_transaksi
          FROM orders o 
          JOIN order_details od ON o.id_order = od.id_order 
          JOIN products p ON od.id_produk = p.id_produk 
          JOIN users u ON o.id_user = u.id_user 
          LEFT JOIN payment py ON o.id_order = py.id_order
          LEFT JOIN transactions t ON o.id_order = t.id_order
          WHERE p.id_user = ? 
          GROUP BY o.id_order 
          ORDER BY o.tanggal_pemesanan DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'hf/header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola Pesanan</h2>
            <a href="my_products.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Produk
            </a>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($orders)): ?>
            <div class="alert alert-info">
                Belum ada pesanan untuk produk Anda.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID Pesanan</th>
                            <th>Tanggal</th>
                            <th>Pembeli</th>
                            <th>Produk</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td>#<?php echo $order['id_order']; ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($order['tanggal_pemesanan'])); ?></td>
                                <td><?php echo htmlspecialchars($order['buyer_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['products']); ?></td>
                                <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="badge bg-<?php 
                                        echo match($order['status_pesanan']) {
                                            'diproses' => 'warning',
                                            'dikirim' => 'info',
                                            'selesai' => 'success',
                                            'dibatalkan' => 'danger',
                                            default => 'secondary'
                                        };
                                    ?>">
                                        <?php echo ucfirst($order['status_pesanan']); ?>
                                    </span>
                                    <?php if ($order['metode_pembayaran'] === 'COD'): ?>
                                        <br>
                                        <small class="text-muted">
                                            Pembayaran: <?php echo ucfirst($order['status_pembayaran']); ?>
                                        </small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id_order" value="<?php echo $order['id_order']; ?>">
                                        <select name="status_pesanan" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                            <option value="diproses" <?php echo $order['status_pesanan'] === 'diproses' ? 'selected' : ''; ?>>Diproses</option>
                                            <option value="dikirim" <?php echo $order['status_pesanan'] === 'dikirim' ? 'selected' : ''; ?>>Dikirim</option>
                                            <option value="selesai" <?php echo $order['status_pesanan'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                            <option value="dibatalkan" <?php echo $order['status_pesanan'] === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'hf/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 