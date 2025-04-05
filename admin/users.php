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

// Handle delete user
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $user_id = $_GET['delete'];

    // Don't allow deleting yourself
    if ($user_id == $_SESSION['admin_id']) {
        $error_message = "You cannot delete your own account!";
    } else {
        // Delete the user
        $stmt = $conn->prepare("DELETE FROM users WHERE id_user = ?");
        $stmt->bind_param("i", $user_id);

        if ($stmt->execute()) {
            // Set success message
            $success_message = "User deleted successfully!";
        } else {
            // Set error message
            $error_message = "Failed to delete user: " . $stmt->error;
        }
    }
}

// Handle role change
if (isset($_POST['change_role']) && isset($_POST['user_id']) && isset($_POST['role'])) {
    $user_id = $_POST['user_id'];
    $new_role = $_POST['role'];

    // Update the user role
    $stmt = $conn->prepare("UPDATE users SET role = ? WHERE id_user = ?");
    $stmt->bind_param("si", $new_role, $user_id);

    if ($stmt->execute()) {
        $success_message = "User role updated successfully!";
    } else {
        $error_message = "Failed to update user role: " . $stmt->error;
    }
}

// Get all users
$users = [];
$query = "SELECT * FROM users ORDER BY id_user DESC";
$result = $conn->query($query);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        // Add role field if it doesn't exist
        if (!isset($row['role'])) {
            $row['role'] = 'user'; // Default role
        }
        $users[] = $row;
    }
}

// Include header
include 'templates/header.php';
?>

<!-- Page content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="page-heading">
        <h1>Users Management</h1>
        <a href="user_form.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> Add New User
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

    <!-- Users Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">All Users</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered data-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo $user['id_user']; ?></td>
                                <td><?php echo htmlspecialchars($user['nama']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><?php echo isset($user['nomor_telepon']) ? htmlspecialchars($user['nomor_telepon']) : 'N/A'; ?></td>
                                <td>
                                    <form action="" method="POST" class="role-form">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id_user']; ?>">
                                        <input type="hidden" name="change_role" value="1">
                                        <select name="role" class="form-control form-control-sm role-select" <?php echo $user['id_user'] == $_SESSION['admin_id'] ? 'disabled' : ''; ?>>
                                            <option value="pembeli" <?php echo $user['role'] == 'pembeli' ? 'selected' : ''; ?>>Pembeli</option>
                                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                            <option value="penjual" <?php echo $user['role'] == 'penjual' ? 'selected' : ''; ?>>Penjual</option>
                                        </select>
                                    </form>
                                </td>
                                <td class="action-buttons">
                                    <a href="user_view.php?id=<?php echo $user['id_user']; ?>" class="btn btn-sm btn-info" data-bs-toggle="tooltip" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="user_form.php?edit=<?php echo $user['id_user']; ?>" class="btn btn-sm btn-primary" data-bs-toggle="tooltip" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($user['id_user'] != $_SESSION['admin_id']): ?>
                                        <a href="users.php?delete=<?php echo $user['id_user']; ?>" class="btn btn-sm btn-danger delete-confirm" data-bs-toggle="tooltip" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6" class="text-center">No users found</td>
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