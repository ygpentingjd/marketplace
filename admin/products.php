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

// Handle delete product
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $product_id = $_GET['delete'];

    // Get product image to delete it too
    $stmt = $conn->prepare("SELECT gambar FROM products WHERE id_produk = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $image_path = "../" . $row['gambar'];
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }

    // Delete the product
    $stmt = $conn->prepare("DELETE FROM products WHERE id_produk = ?");
    $stmt->bind_param("i", $product_id);

    if ($stmt->execute()) {
        // Set success message
        $success_message = "Product deleted successfully!";
    } else {
        // Set error message
        $error_message = "Failed to delete product: " . $stmt->error;
    }
}

// Get all products with category names
$products = [];
$query = "SELECT p.*, c.nama_kategori 
          FROM products p 
          LEFT JOIN categories c ON p.id_kategori = c.id_kategori 
          ORDER BY p.id_produk DESC";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Include header
include 'templates/header.php';
?>

<!-- Page content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="page-heading">
        <h1>Products Management</h1>
        <a href="product_form.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New Product
        </a>
    </div>

    <?php if (isset($success_message)): ?>
        <div class="alert alert-success">
            <?php echo $success_message; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger">
            <?php echo $error_message; ?>
        </div>
    <?php endif; ?>

    <!-- Products Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">All Products</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered data-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Image</th>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Condition</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id_produk']; ?></td>
                                <td>
                                    <?php if (!empty($product['gambar'])): ?>
                                        <img src="../<?php echo htmlspecialchars($product['gambar']); ?>"
                                            alt="<?php echo htmlspecialchars($product['nama_produk']); ?>"
                                            width="50"
                                            class="img-thumbnail">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['nama_produk']); ?></td>
                                <td>Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($product['nama_kategori'] ?? 'Unknown'); ?></td>
                                <td><?php echo $product['stok']; ?> unit</td>
                                <td><?php echo ucfirst($product['kondisi']); ?></td>
                                <td>
                                    <span class="badge <?php echo $product['verification_status'] == 'terverifikasi' ? 'bg-success' : ($product['verification_status'] == 'ditolak' ? 'bg-danger' : 'bg-warning'); ?>">
                                        <?php echo ucfirst($product['verification_status']); ?>
                                    </span>
                                </td>
                                <td class="action-buttons">
                                    <a href="product_form.php?edit=<?php echo $product['id_produk']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="product_view.php?id=<?php echo $product['id_produk']; ?>" class="btn btn-sm btn-secondary" data-bs-toggle="tooltip" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="products.php?delete=<?php echo $product['id_produk']; ?>" class="btn btn-sm btn-danger delete-confirm" data-bs-toggle="tooltip" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="7" class="text-center">No products found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'templates/footer.php';
?>