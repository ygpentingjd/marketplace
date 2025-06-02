<?php
include 'koneksi.php';
session_start(); // Add session start for notifications

// Enable error reporting and logging
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', 'upload_errors.log');

// Log request method and session status
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
error_log("Session Status: " . print_r($_SESSION, true));

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    error_log("=== START OF FORM SUBMISSION ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("FILES data: " . print_r($_FILES, true));
    error_log("Session data: " . print_r($_SESSION, true));

    // Verify database connection
    if (!$conn) {
        error_log("Database connection failed: " . mysqli_connect_error());
        $_SESSION['error'] = "Koneksi database gagal: " . mysqli_connect_error();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
    error_log("Database connection successful");

    // Get form data with validation
    $nama_produk = isset($_POST['nama_produk']) ? trim($_POST['nama_produk']) : '';
    $deskripsi = isset($_POST['deskripsi']) ? trim($_POST['deskripsi']) : '';
    $kategori = isset($_POST['kategori']) ? $_POST['kategori'] : '';
    $harga = isset($_POST['harga']) ? str_replace(['.', ','], '', $_POST['harga']) : '';
    $stok = isset($_POST['stok']) ? (int)$_POST['stok'] : 0;
    $kondisi = isset($_POST['kondisi']) ? $_POST['kondisi'] : '';

    error_log("Form data after processing:");
    error_log("Nama Produk: " . $nama_produk);
    error_log("Deskripsi: " . $deskripsi);
    error_log("Kategori: " . $kategori);
    error_log("Harga: " . $harga);
    error_log("Stok: " . $stok);
    error_log("Kondisi: " . $kondisi);

    // Validate required fields
    $required_fields = [
        'nama_produk' => 'Nama Produk',
        'deskripsi' => 'Deskripsi',
        'kategori' => 'Kategori',
        'harga' => 'Harga',
        'stok' => 'Stok',
        'kondisi' => 'Kondisi'
    ];

    foreach ($required_fields as $field => $label) {
        if (empty($$field)) {
            error_log("Validation failed: $label is empty");
            $_SESSION['error'] = "$label harus diisi!";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Validate price is numeric
    if (!is_numeric($harga)) {
        error_log("Validation failed: Harga is not numeric: $harga");
        $_SESSION['error'] = "Harga harus berupa angka!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Check if files were uploaded
    if (!isset($_FILES['product_images']) || empty($_FILES['product_images']['name'][0])) {
        error_log("Validation failed: No images uploaded");
        $_SESSION['error'] = "Minimal satu gambar produk harus diunggah.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Create uploads directory if it doesn't exist
    $upload_dir = "uploads/";
    if (!file_exists($upload_dir)) {
        error_log("Creating upload directory: $upload_dir");
        if (!mkdir($upload_dir, 0777, true)) {
            error_log("Failed to create upload directory");
            $_SESSION['error'] = "Gagal membuat direktori upload.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        chmod($upload_dir, 0777); // Ensure directory is writable
    }

    // Verify upload directory permissions
    if (!is_writable($upload_dir)) {
        error_log("Upload directory is not writable: $upload_dir");
        $_SESSION['error'] = "Direktori upload tidak memiliki izin tulis.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Process uploaded files
    $uploaded_images = [];
    $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
    $max_file_size = 2 * 1024 * 1024; // 2MB

    foreach ($_FILES['product_images']['tmp_name'] as $key => $tmp_name) {
        if (empty($_FILES['product_images']['name'][$key])) {
            continue;
        }

        $file_name = $_FILES['product_images']['name'][$key];
        $file_size = $_FILES['product_images']['size'][$key];
        $file_type = $_FILES['product_images']['type'][$key];
        $file_error = $_FILES['product_images']['error'][$key];

        error_log("Processing file: $file_name");
        error_log("File size: $file_size bytes");
        error_log("File type: $file_type");
        error_log("File error: $file_error");

        // Validate file
        if ($file_error !== UPLOAD_ERR_OK) {
            $error_message = match ($file_error) {
                UPLOAD_ERR_INI_SIZE => "File terlalu besar (melebihi upload_max_filesize di php.ini)",
                UPLOAD_ERR_FORM_SIZE => "File terlalu besar (melebihi MAX_FILE_SIZE di form)",
                UPLOAD_ERR_PARTIAL => "File hanya terupload sebagian",
                UPLOAD_ERR_NO_FILE => "Tidak ada file yang diupload",
                UPLOAD_ERR_NO_TMP_DIR => "Folder temporary tidak ditemukan",
                UPLOAD_ERR_CANT_WRITE => "Gagal menulis file ke disk",
                UPLOAD_ERR_EXTENSION => "Upload dihentikan oleh ekstensi PHP",
                default => "Error upload tidak diketahui"
            };
            error_log("File upload error: $error_message");
            $_SESSION['error'] = "Error upload file $file_name: $error_message";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        if (!in_array($file_type, $allowed_types)) {
            error_log("Invalid file type: $file_type");
            $_SESSION['error'] = "File $file_name: Tipe file tidak didukung. Gunakan JPG, PNG, atau GIF.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        if ($file_size > $max_file_size) {
            error_log("File too large: $file_size bytes");
            $_SESSION['error'] = "File $file_name: Ukuran file terlalu besar. Maksimal 2MB.";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }

        // Generate unique filename and move file
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $new_filename = uniqid() . '.' . $file_extension;
        $target_file = "uploads/" . $new_filename;
        $upload_path = $upload_dir . $new_filename;

        if (move_uploaded_file($tmp_name, $upload_path)) {
            error_log("File successfully uploaded to: $upload_path");
            $uploaded_images[] = $target_file;
        } else {
            $upload_error = error_get_last();
            error_log("Failed to move uploaded file: " . print_r($upload_error, true));
            $_SESSION['error'] = "Gagal mengunggah file $file_name: " . ($upload_error['message'] ?? 'Unknown error');
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Check if at least one image was uploaded
    if (empty($uploaded_images)) {
        $_SESSION['error'] = "Minimal satu gambar produk harus diunggah.";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Get category ID
    $category_map = [
        'Fashion' => 1,
        'Otomotif' => 2,
        'Elektronik' => 3
    ];
    $category_id = $category_map[$kategori] ?? 1;

    // Validate stok
    if ($stok < 0) {
        $_SESSION['error'] = "Stok tidak boleh negatif!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Validate kondisi
    $valid_kondisi = ['preloved', 'like new', 'normal', 'rusak'];
    if (!in_array($kondisi, $valid_kondisi)) {
        $_SESSION['error'] = "Kondisi produk tidak valid!";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Use first image as main product image
    $main_image = $uploaded_images[0];

    try {
        // Start transaction
        $conn->begin_transaction();

        // Save product to database with additional fields
        $stmt = $conn->prepare("INSERT INTO products (nama_produk, deskripsi, id_kategori, harga, stok, kondisi, gambar, status, verification_status, id_user) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, 'tersedia', 'terverifikasi', ?)");

        // Get user ID from session or use default
        $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 1;

        $stmt->bind_param("ssidissi", $nama_produk, $deskripsi, $category_id, $harga, $stok, $kondisi, $main_image, $user_id);

        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan produk: " . $stmt->error);
        }

        $product_id = $stmt->insert_id;
        error_log("Product inserted with ID: $product_id");

        // Save additional images if any
        if (count($uploaded_images) > 1) {
            for ($i = 1; $i < count($uploaded_images); $i++) {
                $stmt = $conn->prepare("UPDATE products SET gambar = CONCAT(gambar, ',', ?) WHERE id_produk = ?");
                $stmt->bind_param("si", $uploaded_images[$i], $product_id);
                if (!$stmt->execute()) {
                    throw new Exception("Gagal menyimpan gambar tambahan: " . $stmt->error);
                }
            }
        }

        // Commit transaction
        $conn->commit();
        error_log("Transaction committed successfully");

        // Set success message and redirect
        $_SESSION['success'] = "Produk berhasil ditambahkan dengan " . count($uploaded_images) . " gambar! ID Produk: " . $product_id;
        header("Location: detail.php?id=" . $product_id);
        exit();
    } catch (Exception $e) {
        // Rollback transaction on error
        $conn->rollback();
        error_log("Transaction rolled back due to error: " . $e->getMessage());

        // Delete uploaded files if database insert fails
        foreach ($uploaded_images as $image) {
            $file_path = "uploads/" . $image;
            if (file_exists($file_path)) {
                unlink($file_path);
            }
        }

        $_SESSION['error'] = "Error: " . $e->getMessage();
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Display notifications
$success_message = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error_message = isset($_SESSION['error']) ? $_SESSION['error'] : '';

// Clear session messages
unset($_SESSION['success']);
unset($_SESSION['error']);
?>

<link href="style.css" rel="stylesheet">
<?php include 'hf/style.php'; ?>
<?php include 'hf/header.php'; ?>

<div class="container mt-4">
    <h4>Tambahkan Produk</h4>

    <?php if ($success_message): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($success_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <div class="card mt-3">
        <div class="card-body">
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" enctype="multipart/form-data" id="uploadForm" onsubmit="return validateForm()">
                <h5 class="mb-4">Informasi produk</h5>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">Nama produk <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="nama_produk" required
                            value="<?php echo isset($_POST['nama_produk']) ? htmlspecialchars($_POST['nama_produk']) : ''; ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">Foto produk <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <div class="row" id="imagePreviewContainer">
                            <div class="col-md-3">
                                <div class="border rounded p-2 text-center mb-3 upload-box" style="height: 150px; cursor: pointer;" onclick="document.getElementById('imageUpload').click()">
                                    <div class="d-flex align-items-center justify-content-center h-100">
                                        <div>
                                            <i class="fas fa-camera fa-2x mb-2"></i>
                                            <div>Tambahkan</div>
                                            <div><span id="imageCount">0</span>/5</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <input type="file" id="imageUpload" name="product_images[]" accept="image/*" multiple style="display: none;" onchange="handleImageUpload(this)" required>
                        <small class="form-text text-muted">Format yang didukung: JPG, JPEG, PNG, GIF. Maksimal ukuran file: 2MB</small>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">Deskripsi produk <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <textarea class="form-control" name="deskripsi" rows="4" required><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">Kategori <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <select class="form-select" name="kategori" required>
                            <option value="">Pilih Kategori</option>
                            <option value="Elektronik" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Elektronik') ? 'selected' : ''; ?>>Elektronik</option>
                            <option value="Fashion" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Fashion') ? 'selected' : ''; ?>>Fashion</option>
                            <option value="Otomotif" <?php echo (isset($_POST['kategori']) && $_POST['kategori'] == 'Otomotif') ? 'selected' : ''; ?>>Otomotif</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">Stok <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <input type="number" class="form-control" name="stok" required min="0"
                            value="<?php echo isset($_POST['stok']) ? htmlspecialchars($_POST['stok']) : '0'; ?>">
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">Kondisi <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <select class="form-select" name="kondisi" required>
                            <option value="">Pilih Kondisi</option>
                            <option value="preloved" <?php echo (isset($_POST['kondisi']) && $_POST['kondisi'] == 'preloved') ? 'selected' : ''; ?>>Preloved</option>
                            <option value="like new" <?php echo (isset($_POST['kondisi']) && $_POST['kondisi'] == 'like new') ? 'selected' : ''; ?>>Like New</option>
                            <option value="normal" <?php echo (isset($_POST['kondisi']) && $_POST['kondisi'] == 'normal') ? 'selected' : ''; ?>>Normal</option>
                            <option value="rusak" <?php echo (isset($_POST['kondisi']) && $_POST['kondisi'] == 'rusak') ? 'selected' : ''; ?>>Rusak</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3 row">
                    <label class="col-sm-2 col-form-label">Harga produk <span class="text-danger">*</span></label>
                    <div class="col-sm-10">
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" class="form-control" name="harga" required
                                value="<?php echo isset($_POST['harga']) ? htmlspecialchars($_POST['harga']) : ''; ?>"
                                onkeyup="formatRupiah(this)">
                        </div>
                    </div>
                </div>

                <div class="text-end mt-4">
                    <a href="account.php" class="btn btn-light me-2">Kembali</a>
                    <button type="submit" class="btn btn-dark" id="submitBtn">Tambahkan Produk</button>
                </div>
            </form>
        </div>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Form validation
    function validateForm() {
        const form = document.getElementById('uploadForm');
        const submitBtn = document.getElementById('submitBtn');

        // Disable submit button to prevent double submission
        submitBtn.disabled = true;

        // Validate required fields
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('is-invalid');
            } else {
                field.classList.remove('is-invalid');
            }
        });

        // Validate image upload
        const imageInput = document.getElementById('imageUpload');
        if (imageInput.files.length === 0) {
            isValid = false;
            imageInput.classList.add('is-invalid');
        } else {
            imageInput.classList.remove('is-invalid');
        }

        if (!isValid) {
            alert('Mohon lengkapi semua field yang wajib diisi!');
            submitBtn.disabled = false;
            return false;
        }

        return true;
    }

    // Format Rupiah
    function formatRupiah(input) {
        let value = input.value.replace(/[^\d]/g, '');
        if (value === '') return;
        value = parseInt(value, 10).toLocaleString('id-ID');
        input.value = value;
    }

    // Image preview and upload handling
    let imageCount = 0;

    function handleImageUpload(input) {
        const maxImages = 5;
        const container = document.getElementById('imagePreviewContainer');
        const uploadBoxCol = container.querySelector('.upload-box')?.parentElement;
        const files = input.files;

        // Hapus preview lama kecuali upload box
        const previews = container.querySelectorAll('.col-md-3:not(:first-child)');
        previews.forEach(preview => preview.remove());
        imageCount = 0;

        for (let i = 0; i < files.length && imageCount < maxImages; i++) {
            const file = files[i];
            const reader = new FileReader();
            const newCol = document.createElement('div');
            newCol.className = 'col-md-3';

            reader.onload = function(e) {
                newCol.innerHTML = `
                    <div class="border rounded p-2 text-center mb-3">
                        <img src="${e.target.result}" class="img-fluid mb-2" style="max-height: 150px;">
                        <button type="button" class="btn btn-sm btn-danger w-100" onclick="removeImage(this)">Hapus</button>
                    </div>
                `;
                if (uploadBoxCol) {
                    container.insertBefore(newCol, uploadBoxCol);
                } else {
                    container.appendChild(newCol);
                }
                imageCount++;
                updateImageCount();
            };

            reader.readAsDataURL(file);
        }
    }

    function removeImage(button) {
        button.closest('.col-md-3').remove();
        imageCount--;
        updateImageCount();
    }

    function updateImageCount() {
        document.getElementById('imageCount').textContent = imageCount;
        const uploadBoxCol = document.querySelector('.upload-box')?.parentElement;
        if (uploadBoxCol) {
            uploadBoxCol.style.display = imageCount >= 5 ? 'none' : 'block';
        }
    }
</script>

<?php include 'hf/footer.php'; ?>