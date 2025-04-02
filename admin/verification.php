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

// Check and add required columns if they don't exist
$columns = [
    'verification_status' => "ALTER TABLE products MODIFY COLUMN verification_status ENUM('menunggu', 'terverifikasi', 'ditolak') DEFAULT 'menunggu'",
    'reject_reason' => "ALTER TABLE products ADD COLUMN reject_reason TEXT DEFAULT NULL"
];

foreach ($columns as $column => $sql) {
    $result = $conn->query("SHOW COLUMNS FROM products LIKE '$column'");
    if ($result->num_rows == 0) {
        if (!$conn->query($sql)) {
            die("Error adding column $column: " . $conn->error);
        }
    }
}

// Handle verification status change
if (isset($_POST['id_produk']) && isset($_POST['verification_status'])) {
    $product_id = $_POST['id_produk'];
    $status = $_POST['verification_status'];
    $reject_reason = isset($_POST['reject_reason']) ? $_POST['reject_reason'] : '';

    // Validate status
    $valid_statuses = ['menunggu', 'terverifikasi', 'ditolak'];
    if (!in_array($status, $valid_statuses)) {
        $error_message = "Status verifikasi tidak valid";
    } else {
        // Update the product verification status
        if ($status == 'ditolak') {
            $stmt = $conn->prepare("UPDATE products SET verification_status = ?, reject_reason = ? WHERE id_produk = ?");
            $stmt->bind_param("ssi", $status, $reject_reason, $product_id);
        } else {
            $stmt = $conn->prepare("UPDATE products SET verification_status = ?, reject_reason = NULL WHERE id_produk = ?");
            $stmt->bind_param("si", $status, $product_id);
        }

        if ($stmt->execute()) {
            $success_message = "Status verifikasi produk berhasil diperbarui!";
        } else {
            $error_message = "Gagal memperbarui status verifikasi: " . $stmt->error;
        }
    }
}

// Get products by verification status
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'menunggu';
$valid_statuses = ['menunggu', 'terverifikasi', 'ditolak', 'all'];
if (!in_array($status_filter, $valid_statuses)) {
    $status_filter = 'menunggu';
}

$products = [];
$query = "SELECT p.*, u.nama as seller_name 
          FROM products p 
          INNER JOIN users u ON p.id_user = u.id_user 
          WHERE 1=1";

if ($status_filter != 'all') {
    $query .= " AND p.verification_status = ?";
}

$query .= " ORDER BY p.id_produk DESC";

$stmt = $conn->prepare($query);

if ($status_filter != 'all') {
    $stmt->bind_param("s", $status_filter);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Count products by status
$statusCounts = [
    'menunggu' => 0,
    'terverifikasi' => 0,
    'ditolak' => 0,
    'all' => 0
];

$query = "SELECT verification_status, COUNT(*) as count FROM products GROUP BY verification_status";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $status = $row['verification_status'];
        $statusCounts[$status] = $row['count'];
        $statusCounts['all'] += $row['count'];
    }
}

// Include header
include 'templates/header.php';
?>

<!-- Page content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="page-heading">
        <h1>Verifikasi Produk</h1>
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

    <!-- Filter Tabs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold">Filter berdasarkan Status</h6>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills mb-3">
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'menunggu' ? 'active' : ''; ?>" href="?status=menunggu">
                        Menunggu <span class="badge bg-warning text-dark"><?php echo $statusCounts['menunggu']; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'terverifikasi' ? 'active' : ''; ?>" href="?status=terverifikasi">
                        Terverifikasi <span class="badge bg-success"><?php echo $statusCounts['terverifikasi']; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'ditolak' ? 'active' : ''; ?>" href="?status=ditolak">
                        Ditolak <span class="badge bg-danger"><?php echo $statusCounts['ditolak']; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $status_filter == 'all' ? 'active' : ''; ?>" href="?status=all">
                        Semua Produk <span class="badge bg-primary"><?php echo $statusCounts['all']; ?></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Products Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">
                <?php
                switch ($status_filter) {
                    case 'menunggu':
                        echo 'Produk Menunggu Verifikasi';
                        break;
                    case 'terverifikasi':
                        echo 'Produk Terverifikasi';
                        break;
                    case 'ditolak':
                        echo 'Produk Ditolak';
                        break;
                    default:
                        echo 'Semua Produk';
                        break;
                }
                ?>
            </h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered data-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gambar</th>
                            <th>Nama Produk</th>
                            <th>Harga</th>
                            <th>Penjual</th>
                            <th>Kategori</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                            <tr>
                                <td><?php echo $product['id_produk']; ?></td>
                                <td>
                                    <?php if (!empty($product['gambar'])): ?>
                                        <img src="../uploads/products/<?php echo htmlspecialchars($product['gambar']); ?>"
                                            alt="<?php echo htmlspecialchars($product['nama_produk']); ?>"
                                            class="img-thumbnail"
                                            style="max-width: 100px;">
                                    <?php else: ?>
                                        <span class="text-muted">No image</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($product['nama_produk']); ?></td>
                                <td>Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></td>
                                <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
                                <td><?php echo htmlspecialchars($product['id_kategori']); ?></td>
                                <td>
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    switch ($product['verification_status']) {
                                        case 'menunggu':
                                            $status_class = 'bg-warning text-dark';
                                            $status_text = 'Menunggu';
                                            break;
                                        case 'terverifikasi':
                                            $status_class = 'bg-success';
                                            $status_text = 'Terverifikasi';
                                            break;
                                        case 'ditolak':
                                            $status_class = 'bg-danger';
                                            $status_text = 'Ditolak';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </td>
                                <td>
                                    <form action="" method="POST" class="d-inline">
                                        <input type="hidden" name="id_produk" value="<?php echo $product['id_produk']; ?>">
                                        <input type="hidden" name="verification_status" value="terverifikasi">
                                        <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Apakah Anda yakin ingin memverifikasi produk ini?')">
                                            <i class="fas fa-check"></i> Verifikasi
                                        </button>
                                    </form>
                                    <button type="button" class="btn btn-danger btn-sm" data-toggle="modal" data-target="#rejectModal<?php echo $product['id_produk']; ?>">
                                        <i class="fas fa-times"></i> Tolak
                                    </button>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal<?php echo $product['id_produk']; ?>" tabindex="-1" role="dialog">
                                        <div class="modal-dialog" role="document">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Tolak Produk</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <form action="" method="POST">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="id_produk" value="<?php echo $product['id_produk']; ?>">
                                                        <input type="hidden" name="verification_status" value="ditolak">
                                                        <div class="form-group">
                                                            <label>Alasan Penolakan</label>
                                                            <textarea name="reject_reason" class="form-control" rows="3" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-danger">Tolak</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($products)): ?>
                            <tr>
                                <td colspan="8" class="text-center">Tidak ada produk yang ditemukan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.data-table').DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
            }
        });
    });
</script>

<?php
// Include footer
include 'templates/footer.php';
?>