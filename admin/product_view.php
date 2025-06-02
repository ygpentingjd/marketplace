product_view.php

<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in as admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit();
}

try {
    include '../koneksi.php';
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Get product ID from URL
$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details
$product = null;
if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id_produk = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

// Include header
include 'templates/header.php';
?>

<!-- Page content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="page-heading">
        <h1>Product Details</h1>
        <a href="products.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Products
        </a>
    </div>

    <?php if ($product): ?>
        <div class="card shadow mb-4">
            <div class="card-header py-3">
                <h6 class="m-0 font-weight-bold"><?php echo htmlspecialchars($product['nama_produk']); ?></h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <?php if (!empty($product['gambar'])): ?>
                            <img src="<?php echo '../' . $product['gambar']; ?>" alt="<?php echo htmlspecialchars($product['nama_produk']); ?>" class="img-fluid">
                        <?php else: ?>
                            <span class="text-muted">No image</span>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-8">
                        <p><strong>Price:</strong> Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                        <p><strong>Category:</strong> <?php echo htmlspecialchars($product['id_kategori']); ?></p>
                        <p><strong>Status:</strong>
                            <?php
                            $status_class = '';
                            $status = isset($product['verification_status']) ? $product['verification_status'] : 'pending';

                            switch ($status) {
                                case 'terverifikasi':
                                    $status_class = 'badge-success';
                                    break;
                                case 'menunggu':
                                    $status_class = 'badge-warning';
                                    break;
                                case 'ditolak':
                                    $status_class = 'badge-danger';
                                    break;
                            }
                            ?>
                            <span class="badge <?php echo $status_class; ?>"><?php echo ucfirst($status); ?></span>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="alert alert-danger">
            Product not found.
        </div>
    <?php endif; ?>
</div>

<?php
// Include footer
include 'templates/footer.php';
?>