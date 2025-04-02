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

// Check if the users table has the role column
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'role'");
if ($result->num_rows == 0) {
    // Add role column if it doesn't exist
    $conn->query("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'");

    // Set current admin role
    $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id_user = ?");
    $stmt->bind_param("i", $_SESSION['admin_id']);
    $stmt->execute();
}

// Handle role assignment
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['bulk_update'])) {
    $success = true;
    $updated = 0;

    foreach ($_POST['roles'] as $user_id => $role) {
        // Skip current admin
        if ($user_id == $_SESSION['admin_id']) {
            continue;
        }

        // Validate role value
        $valid_roles = ['pembeli', 'penjual', 'admin'];
        if (!in_array($role, $valid_roles)) {
            $success = false;
            $error_message = "Invalid role value: $role. Must be one of: " . implode(', ', $valid_roles);
            break;
        }

        $query = "UPDATE users SET role = ? WHERE id_user = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("si", $role, $user_id);

        if ($stmt->execute()) {
            $updated++;
        } else {
            $success = false;
            $error_message = "Failed to update some roles: " . $stmt->error;
            break;
        }
    }

    if ($success) {
        $success_message = "Successfully updated roles for {$updated} users!";
    }
}

// Get users by role
$role_filter = isset($_GET['role']) ? $_GET['role'] : 'all';
$valid_roles = ['pembeli', 'penjual', 'admin', 'all'];
if (!in_array($role_filter, $valid_roles)) {
    $role_filter = 'all';
}

$users = [];
if ($role_filter == 'all') {
    $query = "SELECT * FROM users ORDER BY id_user DESC";
} else {
    $query = "SELECT * FROM users WHERE role = ? ORDER BY id_user DESC";
}

$stmt = $conn->prepare($query);
if ($role_filter != 'all') {
    $stmt->bind_param("s", $role_filter);
}
$stmt->execute();
$result = $stmt->get_result();

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Add role field if it doesn't exist
        if (!isset($row['role'])) {
            $row['role'] = 'user'; // Default role
        }
        $users[] = $row;
    }
}

// Count users by role
$roleCounts = [
    'pembeli' => 0,
    'penjual' => 0,
    'admin' => 0,
    'all' => 0
];

$query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $role = $row['role'];
        $roleCounts[$role] = $row['count'];
        $roleCounts['all'] += $row['count'];
    }
}

// Include header
include 'templates/header.php';
?>

<!-- Page content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="page-heading">
        <h1>Role Management</h1>
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

    <!-- Role Information Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Role Information</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-user text-primary"></i> Pembeli</h5>
                            <p class="card-text">Pengguna yang dapat membeli produk di marketplace.</p>
                            <div class="text-right">
                                <span class="badge bg-primary"><?php echo $roleCounts['pembeli']; ?> Users</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-user-tie text-danger"></i> Admin</h5>
                            <p class="card-text">Administrator yang dapat mengelola semua aspek marketplace.</p>
                            <div class="text-right">
                                <span class="badge bg-danger"><?php echo $roleCounts['admin']; ?> Admins</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card mb-4">
                        <div class="card-body">
                            <h5 class="card-title"><i class="fas fa-store text-success"></i> Penjual</h5>
                            <p class="card-text">Penjual yang dapat menjual produk di marketplace.</p>
                            <div class="text-right">
                                <span class="badge bg-success"><?php echo $roleCounts['penjual']; ?> Sellers</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold">Filter by Role</h6>
        </div>
        <div class="card-body">
            <ul class="nav nav-pills mb-3">
                <li class="nav-item">
                    <a class="nav-link <?php echo $role_filter == 'all' ? 'active' : ''; ?>" href="?role=all">
                        All Users <span class="badge bg-primary"><?php echo $roleCounts['all']; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $role_filter == 'pembeli' ? 'active' : ''; ?>" href="?role=pembeli">
                        Pembeli <span class="badge bg-info"><?php echo $roleCounts['pembeli']; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $role_filter == 'admin' ? 'active' : ''; ?>" href="?role=admin">
                        Admin <span class="badge bg-danger"><?php echo $roleCounts['admin']; ?></span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $role_filter == 'penjual' ? 'active' : ''; ?>" href="?role=penjual">
                        Penjual <span class="badge bg-success"><?php echo $roleCounts['penjual']; ?></span>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <!-- Users Role Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold">
                <?php echo ucfirst($role_filter) . ' Users'; ?>
            </h6>
            <div>
                <button type="button" class="btn btn-primary" id="saveChangesBtn">
                    <i class="fas fa-save"></i> Save Role Changes
                </button>
            </div>
        </div>
        <div class="card-body">
            <form action="" method="POST" id="rolesForm">
                <input type="hidden" name="bulk_update" value="1">

                <div class="table-responsive">
                    <table class="table table-bordered" width="100%" cellspacing="0">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Role</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id_user']; ?></td>
                                    <td><?php echo htmlspecialchars($user['nama']); ?></td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <select name="roles[<?php echo $user['id_user']; ?>]" class="form-control form-control-sm" <?php echo $user['id_user'] == $_SESSION['admin_id'] ? 'disabled' : ''; ?>>
                                            <option value="pembeli" <?php echo $user['role'] == 'pembeli' ? 'selected' : ''; ?>>Pembeli</option>
                                            <option value="penjual" <?php echo $user['role'] == 'penjual' ? 'selected' : ''; ?>>Penjual</option>
                                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                        <?php if ($user['id_user'] == $_SESSION['admin_id']): ?>
                                            <small class="text-muted d-block">You cannot change your own role</small>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>

                            <?php if (empty($users)): ?>
                                <tr>
                                    <td colspan="4" class="text-center">No users found</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Role Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Submit form when save button is clicked
        document.getElementById('saveChangesBtn').addEventListener('click', function() {
            document.getElementById('rolesForm').submit();
        });
    });
</script>

<?php
// Include footer
include 'templates/footer.php';
?>