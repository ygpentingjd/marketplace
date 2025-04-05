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

// Initialize variables
$user = [
    'id_user' => '',
    'nama' => '',
    'email' => '',
    'password' => '',
    'nomor_telepon' => '',
    'alamat' => '',
    'role' => 'user'
];

$is_edit = false;
$page_title = "Add New User";

// Check if editing existing user
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $user_id = $_GET['edit'];
    $is_edit = true;
    $page_title = "Edit User";

    // Get user data
    $stmt = $conn->prepare("SELECT * FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $user = $row;
        // Add role field if it doesn't exist
        if (!isset($user['role'])) {
            $user['role'] = 'user'; // Default role
        }
    } else {
        // Redirect if user not found
        header("Location: users.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $user['nama'] = $_POST['nama'];
    $user['email'] = $_POST['email'];
    $user['nomor_telepon'] = $_POST['nomor_telepon'];
    $user['alamat'] = $_POST['alamat'];
    $user['role'] = $_POST['role'];

    // Password handling
    $password_changed = false;
    if (!empty($_POST['password'])) {
        $user['password'] = $_POST['password'];
        $password_changed = true;
    }

    // Validate email
    if (!filter_var($user['email'], FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format!";
    } else {
        // Check if email already exists (for new users or email change)
        $check_query = "SELECT id_user FROM users WHERE email = ? AND id_user != ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("si", $user['email'], $user['id_user']);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error_message = "Email already exists!";
        } else {
            // Perform database operation (insert or update)
            if ($is_edit) {
                // Update existing user
                if ($password_changed) {
                    $query = "UPDATE users SET 
                              nama = ?, 
                              email = ?, 
                              password = ?, 
                              nomor_telepon = ?, 
                              alamat = ?,
                              role = ?
                              WHERE id_user = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param(
                        "ssssssi",
                        $user['nama'],
                        $user['email'],
                        $user['password'],
                        $user['nomor_telepon'],
                        $user['alamat'],
                        $user['role'],
                        $user['id_user']
                    );
                } else {
                    $query = "UPDATE users SET 
                              nama = ?, 
                              email = ?, 
                              nomor_telepon = ?, 
                              alamat = ?,
                              role = ?
                              WHERE id_user = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param(
                        "sssssi",
                        $user['nama'],
                        $user['email'],
                        $user['nomor_telepon'],
                        $user['alamat'],
                        $user['role'],
                        $user['id_user']
                    );
                }

                if ($stmt->execute()) {
                    $success_message = "User updated successfully!";
                } else {
                    $error_message = "Error updating user: " . $stmt->error;
                }
            } else {
                // Insert new user
                $query = "INSERT INTO users (nama, email, password, nomor_telepon, alamat, role) 
                         VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param(
                    "ssssss",
                    $user['nama'],
                    $user['email'],
                    $user['password'],
                    $user['nomor_telepon'],
                    $user['alamat'],
                    $user['role']
                );

                if ($stmt->execute()) {
                    $success_message = "User added successfully!";
                    // Reset form for new entry
                    $user = [
                        'id_user' => '',
                        'nama' => '',
                        'email' => '',
                        'password' => '',
                        'nomor_telepon' => '',
                        'alamat' => '',
                        'role' => 'user'
                    ];
                } else {
                    $error_message = "Error adding user: " . $stmt->error;
                }
            }
        }
    }
}

// Include header
include 'templates/header.php';
?>

<!-- Page content -->
<div class="container-fluid">

    <!-- Page Heading -->
    <div class="page-heading">
        <h1><?php echo $page_title; ?></h1>
        <a href="users.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Users
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

    <!-- User Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">User Information</h6>
        </div>
        <div class="card-body">
            <form action="<?php echo $_SERVER['PHP_SELF'] . ($is_edit ? '?edit=' . $user['id_user'] : ''); ?>" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nama">Full Name</label>
                            <input type="text" class="form-control" id="nama" name="nama" value="<?php echo htmlspecialchars($user['nama']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="password">Password <?php echo $is_edit ? '(Leave blank to keep current)' : ''; ?></label>
                            <input type="password" class="form-control" id="password" name="password" <?php echo !$is_edit ? 'required' : ''; ?>>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nomor_telepon">Phone Number</label>
                            <input type="text" class="form-control" id="nomor_telepon" name="nomor_telepon" value="<?php echo htmlspecialchars($user['nomor_telepon']); ?>">
                        </div>

                        <div class="form-group">
                            <label for="alamat">Address</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="role">Role</label>
                            <select class="form-control" id="role" name="role">
                                <option value="pembeli" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>Pembeli</option>
                                <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                <option value="penjual" <?php echo $user['role'] == 'seller' ? 'selected' : ''; ?>>Penjual</option>
                            </select>
                        </div>
                    </div>
                </div>  

                <div class="form-group mt-4">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?php echo $is_edit ? 'Update User' : 'Add User'; ?>
                    </button>
                    <a href="users.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Include footer
include 'templates/footer.php';
?>