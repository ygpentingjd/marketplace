<?php
session_start();
include 'koneksi.php';

// Check if user is logged in and is a seller
if (!isset($_SESSION['id_user']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: login2.php");
    exit();
}

// Check if product ID is provided
if (!isset($_GET['id'])) {
    header("Location: my_products.php");
    exit();
}

$product_id = $_GET['id'];
$user_id = $_SESSION['id_user'];

// Get product details
$query = "SELECT * FROM products WHERE id_produk = ? AND id_user = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $product_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: my_products.php");
    exit();
}

$product = $result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_produk = $_POST['nama_produk'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $deskripsi = $_POST['deskripsi'];
    
    // Handle image upload if new image is provided
    $gambar = $product['gambar']; // Keep existing image by default
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $target_dir = "uploads/";
        $file_extension = strtolower(pathinfo($_FILES["gambar"]["name"], PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = $target_dir . $new_filename;
        
        // Check if image file is a actual image
        $check = getimagesize($_FILES["gambar"]["tmp_name"]);
        if ($check !== false) {
            if (move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
                // Delete old image
                if (file_exists($product['gambar'])) {
                    unlink($product['gambar']);
                }
                $gambar = $target_file;
            }
        }
    }
    
    // Update product in database
    $update_query = "UPDATE products SET 
                    nama_produk = ?, 
                    harga = ?, 
                    stok = ?, 
                    deskripsi = ?, 
                    gambar = ? 
                    WHERE id_produk = ? AND id_user = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("siissii", $nama_produk, $harga, $stok, $deskripsi, $gambar, $product_id, $user_id);
    
    if ($update_stmt->execute()) {
        $_SESSION['success_message'] = "Produk berhasil diperbarui";
        header("Location: my_products.php");
        exit();
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui produk";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .preview-image {
            max-width: 300px;
            max-height: 300px;
            object-fit: cover;
            border-radius: 8px;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <?php include 'hf/header.php'; ?>

    <div class="container mt-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Edit Produk</h3>
                    </div>
                    <div class="card-body">
                        <form action="" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="nama_produk" class="form-label">Nama Produk</label>
                                <input type="text" class="form-control" id="nama_produk" name="nama_produk" 
                                       value="<?php echo htmlspecialchars($product['nama_produk']); ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="harga" class="form-label">Harga</label>
                                <input type="number" class="form-control" id="harga" name="harga" 
                                       value="<?php echo $product['harga']; ?>" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="stok" class="form-label">Stok</label>
                                <input type="number" class="form-control" id="stok" name="stok" 
                                       value="<?php echo $product['stok']; ?>" required min="0">
                            </div>
                            
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required><?php echo htmlspecialchars($product['deskripsi']); ?></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="gambar" class="form-label">Gambar Produk</label>
                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                                <small class="text-muted">Biarkan kosong jika tidak ingin mengubah gambar</small>
                                <div class="mt-2">
                                    <img src="<?php echo htmlspecialchars($product['gambar']); ?>" alt="Preview" class="preview-image">
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between">
                                <a href="my_products.php" class="btn btn-secondary">Kembali</a>
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'hf/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview image before upload
        document.getElementById('gambar').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.querySelector('.preview-image').src = e.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html> 