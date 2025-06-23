<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'koneksi.php';

// Cek jika user belum login
if (!isset($_SESSION['id_user'])) {
    header("Location: login2.php?error=login_required&message=Silakan+login+terlebih+dahulu");
    exit();
}

$seller_id = $_SESSION['id_user'];

// Handle refund response
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $refund_id = $_POST['refund_id'];
    $action = $_POST['action'];
    $catatan = $_POST['catatan'] ?? '';

    // Validasi bahwa refund request adalah untuk produk penjual ini
    $check_query = "SELECT r.*, p.id_user as id_penjual 
                    FROM refund r 
                    JOIN orders o ON r.id_order = o.id_order 
                    JOIN order_details od ON o.id_order = od.id_order 
                    JOIN products p ON od.id_produk = p.id_produk 
                    WHERE r.id_refund = ? AND p.id_user = ? 
                    GROUP BY r.id_refund";
    $check_stmt = $conn->prepare($check_query);
    $check_stmt->bind_param("ii", $refund_id, $seller_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();

    if ($check_result->num_rows > 0) {
        $refund = $check_result->fetch_assoc();

        // Update refund status
        $update_query = "UPDATE refund 
                        SET status = ?, 
                            catatan_penjual = ?, 
                            tanggal_respon = CURRENT_TIMESTAMP 
                        WHERE id_refund = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("ssi", $action, $catatan, $refund_id);

        if ($update_stmt->execute()) {
            // Update order status
            $order_status = $action === 'diterima' ? 'refunded' : 'selesai';
            $update_order = "UPDATE orders SET status_pesanan = ? WHERE id_order = ?";
            $update_order_stmt = $conn->prepare($update_order);
            $update_order_stmt->bind_param("si", $order_status, $refund['id_order']);
            $update_order_stmt->execute();

            $_SESSION['success_message'] = "Status refund berhasil diperbarui";
        } else {
            $_SESSION['error_message'] = "Gagal memperbarui status refund";
        }
    } else {
        $_SESSION['error_message'] = "Refund request tidak ditemukan";
    }

    header("Location: manage_refunds.php");
    exit();
}

// Get all refund requests for seller's products
$query = "SELECT r.*, o.tanggal_pemesanan, o.total_harga, u.nama as nama_pembeli,
          GROUP_CONCAT(p.nama_produk SEPARATOR ', ') as products
          FROM refund r 
          JOIN orders o ON r.id_order = o.id_order 
          JOIN order_details od ON o.id_order = od.id_order 
          JOIN products p ON od.id_produk = p.id_produk 
          JOIN users u ON r.id_user = u.id_user
          WHERE p.id_user = ? 
          GROUP BY r.id_refund 
          ORDER BY r.tanggal_refund DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $seller_id);
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Refund - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .refund-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 20px;
        }

        .page-title {
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #333;
        }

        .refund-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .refund-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .refund-status {
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-menunggu {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .status-diterima {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .status-ditolak {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: none;
            background: none;
            color: #333;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .back-button:hover {
            color: #000;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
            margin-left: 10px;
        }

        .btn-accept {
            background-color: #4caf50;
            color: white;
            border: none;
        }

        .btn-reject {
            background-color: #f44336;
            color: white;
            border: none;
        }

        .btn-accept:hover {
            background-color: #388e3c;
        }

        .btn-reject:hover {
            background-color: #d32f2f;
        }
    </style>
</head>

<body>
    <?php include 'hf/header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Kelola Refund</h2>
            <button class="btn btn-secondary" onclick="window.location.href='my_products.php'">
                <i class="fas fa-arrow-left"></i> Kembali ke Produk Saya
            </button>
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

        <?php if ($result->num_rows === 0): ?>
            <div class="text-center py-5">
                <p class="mb-0">Belum ada permintaan refund</p>
            </div>
        <?php else: ?>
            <?php while ($refund = $result->fetch_assoc()): ?>
                <div class="refund-card mb-4">
                    <div class="refund-header">
                        <div>
                            <h5 class="mb-0">Refund Request #<?php echo $refund['id_refund']; ?></h5>
                            <p class="mb-0 text-muted">
                                Order #<?php echo $refund['id_order']; ?> -
                                <?php echo date('d/m/Y H:i', strtotime($refund['tanggal_refund'])); ?>
                            </p>
                        </div>
                        <span class="refund-status status-<?php echo $refund['status']; ?>">
                            <?php echo ucfirst($refund['status']); ?>
                        </span>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Pembeli:</strong> <?php echo htmlspecialchars($refund['nama_pembeli']); ?></p>
                            <p class="mb-1"><strong>Produk:</strong> <?php echo htmlspecialchars($refund['products']); ?></p>
                            <p class="mb-1"><strong>Total Refund:</strong> Rp<?php echo number_format($refund['total_refund'], 0, ',', '.'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1"><strong>Alasan Refund:</strong></p>
                            <p class="mb-1"><?php echo nl2br(htmlspecialchars($refund['alasan'])); ?></p>
                            <?php if ($refund['bukti_refund']): ?>
                                <p class="mb-1">
                                    <a href="<?php echo htmlspecialchars($refund['bukti_refund']); ?>" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-file"></i> Lihat Bukti
                                    </a>
                                </p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if ($refund['status'] === 'menunggu'): ?>
                        <form method="POST" class="mt-3">
                            <input type="hidden" name="refund_id" value="<?php echo $refund['id_refund']; ?>">
                            <div class="mb-3">
                                <label for="catatan" class="form-label">Catatan untuk Pembeli</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="2"
                                    placeholder="Tambahkan catatan (opsional)"></textarea>
                            </div>
                            <div class="d-flex justify-content-end">
                                <button type="submit" name="action" value="ditolak" class="btn-action btn-reject">
                                    Tolak Refund
                                </button>
                                <button type="submit" name="action" value="diterima" class="btn-action btn-accept">
                                    Terima Refund
                                </button>
                            </div>
                        </form>
                    <?php elseif ($refund['catatan_penjual']): ?>
                        <div class="alert alert-info mt-3">
                            <strong>Catatan Penjual:</strong><br>
                            <?php echo nl2br(htmlspecialchars($refund['catatan_penjual'])); ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php endif; ?>
    </div>

    <?php include 'hf/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>