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

// Get database stats
$stats = [
    'products' => 0,
    'users' => 0,
    'orders' => 0,
    'pending_verification' => 0
];

// Count products
$query = "SELECT COUNT(*) as count FROM products";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['products'] = $row['count'];
}

// Count users
$query = "SELECT COUNT(*) as count FROM users";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['users'] = $row['count'];
}

// Count orders
$query = "SELECT COUNT(*) as count FROM orders";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['orders'] = $row['count'];
}

// Count pending verification products
$query = "SELECT COUNT(*) as count FROM products WHERE verification_status = 'pending'";
$result = $conn->query($query);
if ($result && $row = $result->fetch_assoc()) {
    $stats['pending_verification'] = $row['count'];
}

// Check maintenance mode status
$query = "SELECT * FROM site_settings WHERE setting_name = 'maintenance_mode'";
$result = $conn->query($query);
$maintenanceMode = false;
if ($result && $row = $result->fetch_assoc()) {
    $maintenanceMode = ($row['setting_value'] == '1');
}

// Toggle maintenance mode
if (isset($_POST['toggle_maintenance'])) {
    $newValue = $maintenanceMode ? '0' : '1';
    $stmt = $conn->prepare("UPDATE site_settings SET setting_value = ? WHERE setting_name = 'maintenance_mode'");
    $stmt->bind_param("s", $newValue);
    if ($stmt->execute()) {
        $maintenanceMode = !$maintenanceMode;
    }
}

// Include header
include 'templates/header.php';
?>

<!-- Page content -->
<div class="container-fluid">
    <?php if ($maintenanceMode): ?>
        <div class="maintenance-banner">
            <i class="fas fa-exclamation-triangle"></i> Website is currently in MAINTENANCE MODE - Users cannot access the site
        </div>
    <?php endif; ?>

    <!-- Page Heading -->
    <div class="page-heading">
        <h1>Dashboard</h1>
        <div>
            <form method="POST" action="" class="d-inline">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" id="maintenanceToggle" name="toggle_maintenance" <?php echo $maintenanceMode ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="maintenanceToggle">Maintenance Mode</label>
                </div>
            </form>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row">
        <!-- Products Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-card-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stat-title">Total Products</div>
                            <div class="stat-value"><?php echo $stats['products']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-bag stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-card-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stat-title">Total Users</div>
                            <div class="stat-value"><?php echo $stats['users']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-users stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-card-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stat-title">Total Orders</div>
                            <div class="stat-value"><?php echo $stats['orders']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shopping-cart stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Verification Card -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card stat-card stat-card-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="stat-title">Pending Verification</div>
                            <div class="stat-value"><?php echo $stats['pending_verification']; ?></div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-exclamation-circle stat-icon"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Products and Users -->
    <div class="row">
        <!-- Recent Products -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Recent Products</h6>
                    <a href="products.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM products ORDER BY id_produk DESC LIMIT 5";
                                $result = $conn->query($query);
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $status_class = '';
                                        switch ($row['verification_status']) {
                                            case 'verified':
                                                $status_class = 'badge-success';
                                                break;
                                            case 'pending':
                                                $status_class = 'badge-warning';
                                                break;
                                            case 'rejected':
                                                $status_class = 'badge-danger';
                                                break;
                                        }
                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($row['nama_produk']) . '</td>';
                                        echo '<td>Rp ' . number_format($row['harga'], 0, ',', '.') . '</td>';
                                        echo '<td><span class="badge ' . $status_class . '">' . ucfirst($row['verification_status']) . '</span></td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="3" class="text-center">No products found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Users -->
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Recent Users</h6>
                    <a href="users.php" class="btn btn-sm btn-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $query = "SELECT * FROM users ORDER BY id_user DESC LIMIT 5";
                                $result = $conn->query($query);
                                if ($result && $result->num_rows > 0) {
                                    while ($row = $result->fetch_assoc()) {
                                        $role = isset($row['role']) ? $row['role'] : 'user';
                                        $role_class = $role == 'admin' ? 'badge-danger' : 'badge-info';

                                        echo '<tr>';
                                        echo '<td>' . htmlspecialchars($row['nama']) . '</td>';
                                        echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                                        echo '<td><span class="badge ' . $role_class . '">' . ucfirst($role) . '</span></td>';
                                        echo '</tr>';
                                    }
                                } else {
                                    echo '<tr><td colspan="3" class="text-center">No users found</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Chart -->
    <div class="row">
        <div class="col-xl-12 col-lg-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold">Monthly Sales Overview</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area">
                        <canvas id="salesChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer
include 'templates/footer.php';
?>