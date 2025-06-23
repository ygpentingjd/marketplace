<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'koneksi.php';

// Cek jika user belum login
if (!isset($_SESSION['id_user'])) {
    header("Location: login2.php?error=login_required&message=Silakan+login+terlebih+dahulu");
    exit();
}

$user_id = $_SESSION['id_user'];
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : null;

// Validasi order_id
if (!$order_id) {
    header("Location: orders.php?error=invalid_order");
    exit();
}

// Cek apakah order milik user ini
$check_query = "SELECT o.*, p.id_user as id_penjual 
                FROM orders o 
                JOIN order_details od ON o.id_order = od.id_order 
                JOIN products p ON od.id_produk = p.id_produk 
                WHERE o.id_order = ? AND o.id_user = ? 
                GROUP BY o.id_order";
$check_stmt = $conn->prepare($check_query);
$check_stmt->bind_param("ii", $order_id, $user_id);
$check_stmt->execute();
$order_result = $check_stmt->get_result();

if ($order_result->num_rows === 0) {
    header("Location: orders.php?error=invalid_order");
    exit();
}

$order = $order_result->fetch_assoc();

// Cek apakah sudah ada refund request untuk order ini
$refund_check = "SELECT * FROM refund WHERE id_order = ?";
$refund_stmt = $conn->prepare($refund_check);
$refund_stmt->bind_param("i", $order_id);
$refund_stmt->execute();
$refund_result = $refund_stmt->get_result();

if ($refund_result->num_rows > 0) {
    header("Location: orders.php?error=refund_exists");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $alasan = $_POST['alasan'];
    $bukti_refund = null;

    // Handle file upload if exists
    if (isset($_FILES['bukti_refund']) && $_FILES['bukti_refund']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/refund/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_extension = strtolower(pathinfo($_FILES['bukti_refund']['name'], PATHINFO_EXTENSION));
        $allowed_extensions = array('jpg', 'jpeg', 'png', 'pdf');

        if (in_array($file_extension, $allowed_extensions)) {
            $new_filename = 'refund_' . $order_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;

            if (move_uploaded_file($_FILES['bukti_refund']['tmp_name'], $upload_path)) {
                $bukti_refund = $upload_path;
            }
        }
    }

    // Insert refund request
    $insert_query = "INSERT INTO refund (id_order, id_user, alasan, bukti_refund, total_refund) 
                    VALUES (?, ?, ?, ?, (SELECT total_harga FROM orders WHERE id_order = ?))";
    $insert_stmt = $conn->prepare($insert_query);
    $insert_stmt->bind_param("iissi", $order_id, $user_id, $alasan, $bukti_refund, $order_id);

    if ($insert_stmt->execute()) {
        // Update order status
        $update_order = "UPDATE orders SET status_pesanan = 'refund' WHERE id_order = ?";
        $update_stmt = $conn->prepare($update_order);
        $update_stmt->bind_param("i", $order_id);
        $update_stmt->execute();

        header("Location: orders.php?success=refund_requested");
        exit();
    } else {
        $error = "Gagal mengirim permintaan refund";
    }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Request Refund - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
        }

        .refund-container {
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #333;
        }

        .form-label {
            font-weight: 500;
            color: #333;
        }

        .btn-submit {
            background-color: #000;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-submit:hover {
            background-color: #333;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: none;
            background: none;
            color: #333;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .back-button:hover {
            color: #000;
        }
    </style>
</head>

<body>
    <?php include 'hf/header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Request Refund</h2>
            <button class="btn btn-secondary" onclick="window.location.href='orders.php'">
                <i class="fas fa-arrow-left"></i> Kembali ke Pesanan
            </button>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <div class="refund-container">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-4">
                    <label class="form-label">Detail Pesanan</label>
                    <div class="card">
                        <div class="card-body">
                            <p class="mb-1"><strong>Order ID:</strong> #<?php echo $order['id_order']; ?></p>
                            <p class="mb-1"><strong>Tanggal Pesanan:</strong> <?php echo date('d/m/Y H:i', strtotime($order['tanggal_pemesanan'])); ?></p>
                            <p class="mb-0"><strong>Total:</strong> Rp<?php echo number_format($order['total_harga'], 0, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="alasan" class="form-label">Alasan Refund</label>
                    <textarea class="form-control" id="alasan" name="alasan" rows="4" required
                        placeholder="Jelaskan alasan Anda meminta refund..."></textarea>
                </div>

                <div class="mb-4">
                    <label for="bukti_refund" class="form-label">Bukti Pendukung (Opsional)</label>
                    <input type="file" class="form-control" id="bukti_refund" name="bukti_refund"
                        accept=".jpg,.jpeg,.png,.pdf">
                    <small class="text-muted">Format yang didukung: JPG, JPEG, PNG, PDF. Maksimal 2MB</small>
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Informasi:</strong>
                    <ul class="mb-0 mt-2">
                        <li>Permintaan refund akan ditinjau oleh penjual</li>
                        <li>Proses refund dapat memakan waktu 1-3 hari kerja</li>
                        <li>Pastikan alasan refund Anda jelas dan lengkap</li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-submit">Kirim Permintaan Refund</button>
            </form>
        </div>
    </div>

    <?php include 'hf/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>