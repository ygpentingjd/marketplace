<?php
session_start();
include 'koneksi.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login2.php");
    exit();
}

// Get user's products
$id_user = $_SESSION['id_user'];
$query = "SELECT * FROM products WHERE id_user = ? ORDER BY id_produk DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $id_user);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Produk Saya - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 8px;
        }

        .action-buttons .btn {
            margin: 0 2px;
        }
    </style>
</head>

<body>
    <?php include 'hf/header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Produk Saya</h2>
            <div>
                <a href="manage_orders.php" class="btn btn-info me-2">
                    <i class="fas fa-shopping-cart"></i> Kelola Pesanan
                </a>
                <a href="sales_report.php" class="btn btn-primary me-2">
                    <i class="fas fa-chart-line"></i> Laporan Penjualan
                </a>
                <a href="manage_refunds.php" class="btn" style="background-color: orange; color: white; margin-right: 8px;">
                    <i class="fas fa-undo-alt"></i> Kelola Refund
                </a>
                <a href="upload.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Tambah Produk Baru
                </a>
            </div>
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

        <?php if (empty($products)): ?>
            <div class="alert alert-info">
                Anda belum memiliki produk. <a href="upload.php">Upload produk pertama Anda</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td>
                                    <img src="<?php echo htmlspecialchars($product['gambar']); ?>"
                                        alt="<?php echo htmlspecialchars($product['nama_produk']); ?>"
                                        class="product-image">
                                </td>
                                <td><?php echo htmlspecialchars($product['nama_produk']); ?></td>
                                <td>Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></td>
                                <td><?php echo $product['stok']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $product['verification_status'] === 'terverifikasi' ? 'success' : 'warning'; ?>">
                                        <?php echo ucfirst($product['verification_status']); ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="edit_product.php?id=<?php echo $product['id_produk']; ?>"
                                        class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button onclick="deleteProduct(<?php echo $product['id_produk']; ?>)"
                                        class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
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
    <script>
        function deleteProduct(productId) {
            if (confirm('Apakah Anda yakin ingin menghapus produk ini?')) {
                window.location.href = 'delete_product.php?id=' + productId;
            }
        }
    </script>
</body>

</html>