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

// Get categories from database
$categories = [];
$query = "SELECT * FROM categories ORDER BY nama_kategori";
$result = $conn->query($query);
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
}

// Initialize variables
$product = [
    'id_produk' => '',
    'nama_produk' => '',
    'deskripsi' => '',
    'harga' => '',
    'id_kategori' => '',
    'stok' => '0',
    'kondisi' => 'normal',
    'gambar' => '',
    'verification_status' => 'menunggu'
];

$is_edit = false;
$page_title = "Tambah Produk Baru";

// Check if editing existing product
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $product_id = $_GET['edit'];
    $is_edit = true;
    $page_title = "Edit Produk";

    // Get product data
    $stmt = $conn->prepare("SELECT * FROM products WHERE id_produk = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $product = $row;
    } else {
        // Redirect if product not found
        header("Location: products.php");
        exit();
    }
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $product['nama_produk'] = $_POST['nama_produk'];
    $product['deskripsi'] = $_POST['deskripsi'];
    $product['harga'] = $_POST['harga'];
    $product['id_kategori'] = $_POST['id_kategori'];
    $product['stok'] = $_POST['stok'];
    $product['kondisi'] = $_POST['kondisi'];

    // Validate verification status
    $valid_statuses = ['menunggu', 'terverifikasi', 'ditolak'];
    $status = $_POST['verification_status'];
    if (!in_array($status, $valid_statuses)) {
        $error_message = "Status verifikasi tidak valid.";
    } else {
        $product['verification_status'] = $status;
    }

    // Handle image upload
    $image_uploaded = false;
    $old_image = $product['gambar'];

    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $target_dir = "../uploads/";

        // Create directory if it doesn't exist
        if (!file_exists($target_dir)) {
            if (!mkdir($target_dir, 0777, true)) {
                $error_message = "Gagal membuat direktori upload.";
                return;
            }
        }

        // Validate file type
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
        $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_types)) {
            $error_message = "Hanya file JPG, JPEG, PNG & GIF yang diizinkan.";
            return;
        }

        // Generate unique filename
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        $relative_path = "uploads/" . $new_filename;

        // Try to upload file
        if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
            $product['gambar'] = $relative_path;
            $image_uploaded = true;
        } else {
            $error_message = "Gagal mengunggah gambar. Error: " . $_FILES["image"]["error"];
            return;
        }
    }

    // Perform database operation (insert or update)
    if (!isset($error_message)) {
        if ($is_edit) {
            // Update existing product
            $query = "UPDATE products SET 
                      nama_produk = ?, 
                      deskripsi = ?, 
                      harga = ?, 
                      id_kategori = ?, 
                      stok = ?, 
                      kondisi = ?, 
                      verification_status = ?";

            // Only update image if a new one was uploaded
            if ($image_uploaded) {
                $query .= ", gambar = ?";
                $stmt = $conn->prepare($query . " WHERE id_produk = ?");
                $stmt->bind_param(
                    "ssdssssi",
                    $product['nama_produk'],
                    $product['deskripsi'],
                    $product['harga'],
                    $product['id_kategori'],
                    $product['stok'],
                    $product['kondisi'],
                    $product['verification_status'],
                    $product['gambar'],
                    $product['id_produk']
                );
            } else {
                $stmt = $conn->prepare($query . " WHERE id_produk = ?");
                $stmt->bind_param(
                    "ssdsssi",
                    $product['nama_produk'],
                    $product['deskripsi'],
                    $product['harga'],
                    $product['id_kategori'],
                    $product['stok'],
                    $product['kondisi'],
                    $product['verification_status'],
                    $product['id_produk']
                );
            }

            if ($stmt->execute()) {
                // Delete old image if new one was uploaded
                if ($image_uploaded && !empty($old_image)) {
                    $old_image_path = "../" . $old_image;
                    if (file_exists($old_image_path)) {
                        unlink($old_image_path);
                    }
                }
                $success_message = "Produk berhasil diperbarui!";
            } else {
                $error_message = "Error memperbarui produk: " . $stmt->error;
            }
        } else {
            // Insert new product
            if (empty($product['gambar'])) {
                $error_message = "Gambar produk wajib diunggah.";
            } else {
                $query = "INSERT INTO products (nama_produk, deskripsi, harga, id_kategori, stok, kondisi, gambar, verification_status) 
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $conn->prepare($query);
                $stmt->bind_param(
                    "ssdsssss",
                    $product['nama_produk'],
                    $product['deskripsi'],
                    $product['harga'],
                    $product['id_kategori'],
                    $product['stok'],
                    $product['kondisi'],
                    $product['gambar'],
                    $product['verification_status']
                );

                if ($stmt->execute()) {
                    $success_message = "Produk berhasil ditambahkan!";
                    // Reset form for new entry
                    $product = [
                        'id_produk' => '',
                        'nama_produk' => '',
                        'deskripsi' => '',
                        'harga' => '',
                        'id_kategori' => '',
                        'stok' => '0',
                        'kondisi' => 'normal',
                        'gambar' => '',
                        'verification_status' => 'menunggu'
                    ];
                } else {
                    $error_message = "Error menambahkan produk: " . $stmt->error;
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

    <!-- Product Form -->
    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold">Form Produk</h6>
        </div>
        <div class="card-body">
            <form action="" method="POST" enctype="multipart/form-data" id="productForm" onsubmit="return validateForm()">
                <div class="form-group">
                    <label for="nama_produk">Nama Produk <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?php echo htmlspecialchars($product['nama_produk']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="deskripsi">Deskripsi <span class="text-danger">*</span></label>
                    <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="harga">Harga <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="harga" name="harga" value="<?php echo htmlspecialchars($product['harga']); ?>" min="0" required>
                </div>

                <div class="form-group">
                    <label for="id_kategori">Kategori <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="id_kategori" name="id_kategori" value="<?php echo htmlspecialchars($product['id_kategori']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="stok">Stok <span class="text-danger">*</span></label>
                    <input type="number" class="form-control" id="stok" name="stok" value="<?php echo htmlspecialchars($product['stok']); ?>" min="0" required>
                </div>

                <div class="form-group">
                    <label for="image">Gambar Produk <?php echo !$is_edit ? '<span class="text-danger">*</span>' : ''; ?></label>
                    <?php if (!empty($product['gambar'])): ?>
                        <div class="mb-2">
                            <img src="../<?php echo htmlspecialchars($product['gambar']); ?>" alt="Current Image" style="max-width: 200px;" class="img-thumbnail">
                        </div>
                    <?php endif; ?>
                    <input type="file" class="form-control-file" id="image" name="image" accept="image/jpeg,image/png,image/gif" <?php echo !$is_edit ? 'required' : ''; ?>>
                    <small class="form-text text-muted">Format yang didukung: JPG, JPEG, PNG, GIF. Maksimal ukuran file: 2MB</small>
                    <div id="imagePreview" class="mt-2" style="display: none;">
                        <img src="" alt="Preview" style="max-width: 200px;" class="img-thumbnail">
                    </div>
                </div>

                <div class="form-group">
                    <label for="verification_status">Status Verifikasi</label>
                    <select class="form-control" id="verification_status" name="verification_status">
                        <option value="menunggu" <?php echo $product['verification_status'] == 'menunggu' ? 'selected' : ''; ?>>Menunggu</option>
                        <option value="terverifikasi" <?php echo $product['verification_status'] == 'terverifikasi' ? 'selected' : ''; ?>>Terverifikasi</option>
                        <option value="ditolak" <?php echo $product['verification_status'] == 'ditolak' ? 'selected' : ''; ?>>Ditolak</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary"><?php echo $is_edit ? 'Update' : 'Tambah'; ?> Produk</button>
                <a href="products.php" class="btn btn-secondary">Batal</a>
            </form>
        </div>
    </div>
</div>

<script>
    function validateForm() {
        const form = document.getElementById('productForm');
        const imageInput = document.getElementById('image');
        const isEdit = <?php echo $is_edit ? 'true' : 'false'; ?>;

        // Validate required fields
        if (!form.checkValidity()) {
            form.reportValidity();
            return false;
        }

        // Validate image for new products
        if (!isEdit && (!imageInput.files || imageInput.files.length === 0)) {
            alert('Gambar produk wajib diunggah untuk produk baru.');
            return false;
        }

        // Validate image file type and size
        if (imageInput.files && imageInput.files.length > 0) {
            const file = imageInput.files[0];
            const allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            const maxSize = 2 * 1024 * 1024; // 2MB

            if (!allowedTypes.includes(file.type)) {
                alert('Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.');
                return false;
            }

            if (file.size > maxSize) {
                alert('Ukuran file terlalu besar. Maksimal 2MB.');
                return false;
            }
        }

        return true;
    }

    // Image preview
    document.getElementById('image').addEventListener('change', function(e) {
        const preview = document.getElementById('imagePreview');
        const file = e.target.files[0];

        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.querySelector('img').src = e.target.result;
                preview.style.display = 'block';
            }
            reader.readAsDataURL(file);
        } else {
            preview.style.display = 'none';
        }
    });
</script>

<?php
// Include footer
include 'templates/footer.php';
?>