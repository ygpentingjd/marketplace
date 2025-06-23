<?php
session_start();
include 'koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['id_user'])) {
    header("Location: login2.php");
    exit();
}

// Check if order ID is provided
if (!isset($_GET['id_order'])) {
    header("Location: orders.php");
    exit();
}

$order_id = $_GET['id_order'];
$user_id = $_SESSION['id_user'];

// Get order details
$query = "SELECT o.id_order, o.id_user as pembeli_id, o.tanggal_pemesanan, o.status_pesanan, o.total_harga,
          GROUP_CONCAT(p.nama_produk SEPARATOR ', ') as products,
          GROUP_CONCAT(p.gambar SEPARATOR ', ') as product_images,
          GROUP_CONCAT(od.jumlah SEPARATOR ', ') as quantities,
          GROUP_CONCAT(od.subtotal SEPARATOR ', ') as subtotals,
          py.status_pembayaran, py.metode as metode_pembayaran,
          t.status_pembayaran as status_transaksi, t.metode_pembayaran as metode_transaksi,
          u_pembeli.nama as nama_pembeli, u_pembeli.alamat,
          u_penjual.nama as nama_penjual
          FROM orders o 
          JOIN order_details od ON o.id_order = od.id_order 
          JOIN products p ON od.id_produk = p.id_produk 
          LEFT JOIN payment py ON o.id_order = py.id_order
          LEFT JOIN transactions t ON o.id_order = t.id_order
          JOIN users u_pembeli ON o.id_user = u_pembeli.id_user
          JOIN users u_penjual ON p.id_user = u_penjual.id_user
          WHERE o.id_order = ? AND o.id_user = ?
          GROUP BY o.id_order";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: orders.php");
    exit();
}

$order = $result->fetch_assoc();
$products = explode(', ', $order['products']);
$product_images = explode(', ', $order['product_images']);
$quantities = explode(', ', $order['quantities']);
$subtotals = explode(', ', $order['subtotals']);

// Handle payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pay_now'])) {
    $payment_method = $_POST['payment_method'];
    
    // Validate payment method
    if (!in_array($payment_method, ['e_wallet', 'transfer_bank'])) {
        $_SESSION['error'] = "Metode pembayaran tidak valid.";
        header("Location: detail_pesanan.php?id=" . $order_id);
        exit();
    }

    // Update payment status in transactions table
    $update_transaction = "UPDATE transactions 
                          SET status_pembayaran = 'sukses', 
                              metode_pembayaran = ? 
                          WHERE id_order = ?";
    $stmt = $conn->prepare($update_transaction);
    $stmt->bind_param("si", $payment_method, $order_id);
    
    if ($stmt->execute()) {
        // Update payment status in payment table
        $update_payment = "UPDATE payment 
                          SET status_pembayaran = 'sukses', 
                              metode = ? 
                          WHERE id_order = ?";
        $stmt = $conn->prepare($update_payment);
        $stmt->bind_param("si", $payment_method, $order_id);
        $stmt->execute();
        
        $_SESSION['success'] = "Pembayaran berhasil diproses.";
    } else {
        $_SESSION['error'] = "Gagal memproses pembayaran.";
    }
    
    header("Location: detail_pesanan.php?id=" . $order_id);
    exit();
}

// Handle order status update for COD
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'];
    
    // Update order status
    $update_order = "UPDATE orders SET status_pesanan = ? WHERE id_order = ?";
    $stmt = $conn->prepare($update_order);
    $stmt->bind_param("si", $new_status, $order_id);
    
    if ($stmt->execute()) {
        // If status is 'selesai' and payment method is COD, update payment status
        if ($new_status === 'selesai') {
            $check_cod = "SELECT t.metode_pembayaran 
                         FROM transactions t 
                         WHERE t.id_order = ? AND t.metode_pembayaran = 'COD'";
            $stmt = $conn->prepare($check_cod);
            $stmt->bind_param("i", $order_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                // Update payment status to 'sukses' for COD
                $update_payment = "UPDATE payment 
                                 SET status_pembayaran = 'sukses' 
                                 WHERE id_order = ?";
                $stmt = $conn->prepare($update_payment);
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
                
                // Update transaction status to 'sukses' for COD
                $update_transaction = "UPDATE transactions 
                                     SET status_pembayaran = 'sukses' 
                                     WHERE id_order = ?";
                $stmt = $conn->prepare($update_transaction);
                $stmt->bind_param("i", $order_id);
                $stmt->execute();
            }
        }
        
        $_SESSION['success'] = "Status pesanan berhasil diperbarui.";
    } else {
        $_SESSION['error'] = "Gagal memperbarui status pesanan.";
    }
    
    header("Location: detail_pesanan.php?id=" . $order_id);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo $order_id; ?> - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'hf/header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Detail Pesanan #<?php echo $order_id; ?></h2>
            <a href="orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Pesanan
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

        <div class="row">
            <!-- Order Details -->
            <div class="col-md-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Pesanan</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>ID Pesanan:</strong> <?php echo $order['id_order']; ?>
                        </div>
                        <div class="mb-3">
                            <strong>Pembeli:</strong> <?php echo htmlspecialchars($order['nama_pembeli']); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Penjual:</strong> <?php echo htmlspecialchars($order['nama_penjual']); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Status Pesanan:</strong>
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
                        </div>
                        <div class="mb-3">
                            <strong>Tanggal Pesanan:</strong>
                            <?php echo date('d F Y H:i', strtotime($order['tanggal_pemesanan'])); ?>
                        </div>
                        <div class="mb-3">
                            <strong>Alamat Pengiriman:</strong><br>
                            <?php echo htmlspecialchars($order['alamat']); ?>
                        </div>
                    </div>
                </div>

                <!-- Products -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Produk</h5>
                    </div>
                    <div class="card-body">
                        <?php for ($i = 0; $i < count($products); $i++): ?>
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?php echo htmlspecialchars($product_images[$i]); ?>" 
                                     class="img-thumbnail me-3" 
                                     style="width: 100px; height: 100px; object-fit: cover;"
                                     alt="<?php echo htmlspecialchars($products[$i]); ?>"
                                     onerror="this.src='image/default.png';">
                                <div class="flex-grow-1">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($products[$i]); ?></h6>
                                    <div class="text-muted">
                                        <?php echo $quantities[$i]; ?> × Rp <?php echo number_format($subtotals[$i], 0, ',', '.'); ?>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <strong>Rp <?php echo number_format($subtotals[$i], 0, ',', '.'); ?></strong>
                                </div>
                            </div>
                        <?php endfor; ?>
                    </div>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="col-md-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Informasi Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <strong>Total Pembayaran:</strong><br>
                            <h4 class="text-primary mb-3">Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></h4>
                        </div>

                        <?php if ($order['status_pesanan'] === 'menunggu_pembayaran' && $order['status_pembayaran'] !== 'sukses'): ?>
                            <div class="card mb-4">
                                <div class="card-header">
                                    <h5 class="mb-0">Pembayaran</h5>
                                </div>
                                <div class="card-body">
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label class="form-label">Metode Pembayaran:</label>
                                            <select name="payment_method" class="form-select" required>
                                                <option value="">Pilih metode pembayaran</option>
                                                <option value="e_wallet">E-Wallet</option>
                                                <option value="transfer_bank">Transfer Bank</option>
                                            </select>
                                        </div>
                                        <button type="submit" name="pay_now" class="btn btn-primary">Bayar Sekarang</button>
                                    </form>
                                </div>
                            </div>
                        <?php elseif ($order['status_pembayaran'] === 'pending'): ?>
                            <div class="alert alert-info">
                                <strong>Status Pembayaran:</strong> <?php echo ucfirst($order['status_pembayaran']); ?><br>
                                <strong>Metode Pembayaran:</strong> <?php echo ucfirst($order['metode_pembayaran']); ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'hf/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 