<?php
session_start();
include 'koneksi.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login2.php");
    exit();
}

$seller_id = $_SESSION['id_user'];

// Get date range from request
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-01'); // Default to first day of current month
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d'); // Default to current date

// Get sales report
$query = "SELECT 
            DATE(o.tanggal_pemesanan) as tanggal,
            COUNT(DISTINCT o.id_order) as jumlah_pesanan,
            SUM(o.total_harga) as total_penjualan,
            GROUP_CONCAT(DISTINCT p.nama_produk SEPARATOR ', ') as produk_terjual
          FROM orders o 
          JOIN order_details od ON o.id_order = od.id_order 
          JOIN products p ON od.id_produk = p.id_produk 
          LEFT JOIN payment py ON o.id_order = py.id_order
          LEFT JOIN transactions t ON o.id_order = t.id_order
          WHERE p.id_user = ? 
          AND o.tanggal_pemesanan BETWEEN ? AND ?
          AND o.status_pesanan = 'selesai'
          AND (
              (py.metode = 'COD' AND py.status_pembayaran = 'berhasil')
              OR 
              (t.status_pembayaran = 'sukses')
          )
          GROUP BY DATE(o.tanggal_pemesanan)
          ORDER BY tanggal DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("iss", $seller_id, $start_date, $end_date);
$stmt->execute();
$sales = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate total sales
$total_sales = array_sum(array_column($sales, 'total_penjualan'));
$total_orders = array_sum(array_column($sales, 'jumlah_pesanan'));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'hf/header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Laporan Penjualan</h2>
            <a href="manage_orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Kelola Pesanan
            </a>
        </div>

        <!-- Filter Form -->
        <div class="card mb-4">
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Tanggal Mulai</label>
                        <input type="date" class="form-control" id="start_date" name="start_date" 
                               value="<?php echo $start_date; ?>">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">Tanggal Akhir</label>
                        <input type="date" class="form-control" id="end_date" name="end_date" 
                               value="<?php echo $end_date; ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary">Filter</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Summary Cards -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-primary text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Penjualan</h5>
                        <h3 class="card-text">Rp <?php echo number_format($total_sales, 0, ',', '.'); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-success text-white">
                    <div class="card-body">
                        <h5 class="card-title">Total Pesanan</h5>
                        <h3 class="card-text"><?php echo $total_orders; ?> Pesanan</h3>
                    </div>
                </div>
            </div>
        </div>

        <?php if (empty($sales)): ?>
            <div class="alert alert-info">
                Tidak ada data penjualan untuk periode yang dipilih.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Tanggal</th>
                            <th>Jumlah Pesanan</th>
                            <th>Total Penjualan</th>
                            <th>Produk Terjual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sales as $sale): ?>
                            <tr>
                                <td><?php echo date('d/m/Y', strtotime($sale['tanggal'])); ?></td>
                                <td><?php echo $sale['jumlah_pesanan']; ?></td>
                                <td>Rp <?php echo number_format($sale['total_penjualan'], 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($sale['produk_terjual']); ?></td>
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