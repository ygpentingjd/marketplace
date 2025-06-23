<?php
session_start();
include 'koneksi.php';

// Check if user is logged in
if (!isset($_SESSION['id_user'])) {
    header("Location: login2.php");
    exit();
}

$user_id = $_SESSION['id_user'];

// Handle review submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_produk']) && isset($_POST['rating'])) {
    $id_produk = $_POST['id_produk'];
    $rating = $_POST['rating'];
    $komentar = $_POST['komentar'] ?? '';

    // Verify that user has purchased the product
    $verify_query = "SELECT o.id_order 
                    FROM orders o 
                    JOIN order_details od ON o.id_order = od.id_order 
                    WHERE o.id_user = ? 
                    AND od.id_produk = ? 
                    AND o.status_pesanan = 'selesai'";
    $verify_stmt = $conn->prepare($verify_query);
    $verify_stmt->bind_param("ii", $user_id, $id_produk);
    $verify_stmt->execute();
    $verify_result = $verify_stmt->get_result();

    if ($verify_result->num_rows > 0) {
        // Check if user has already reviewed this product
        $check_query = "SELECT id_review FROM reviews WHERE id_user = ? AND id_produk = ?";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->bind_param("ii", $user_id, $id_produk);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();

        if ($check_result->num_rows > 0) {
            // Update existing review
            $update_query = "UPDATE reviews SET rating = ?, komentar = ? WHERE id_user = ? AND id_produk = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("isii", $rating, $komentar, $user_id, $id_produk);
        } else {
            // Insert new review
            $insert_query = "INSERT INTO reviews (id_user, id_produk, rating, komentar) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("iiis", $user_id, $id_produk, $rating, $komentar);
        }

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Ulasan berhasil disimpan";
        } else {
            $_SESSION['error_message'] = "Gagal menyimpan ulasan";
        }
    } else {
        $_SESSION['error_message'] = "Anda belum membeli produk ini atau pesanan belum selesai";
    }

    header("Location: reviews.php");
    exit();
}

// Get products that user has purchased and can review
$query = "SELECT DISTINCT p.id_produk, p.nama_produk, p.gambar,
          COALESCE(r.rating, 0) as user_rating,
          COALESCE(r.komentar, '') as user_komentar,
          COALESCE(AVG(r2.rating), 0) as avg_rating,
          COUNT(r2.id_review) as total_reviews
          FROM products p
          JOIN order_details od ON p.id_produk = od.id_produk
          JOIN orders o ON od.id_order = o.id_order
          LEFT JOIN reviews r ON p.id_produk = r.id_produk AND r.id_user = ?
          LEFT JOIN reviews r2 ON p.id_produk = r2.id_produk
          WHERE o.id_user = ? 
          AND o.status_pesanan = 'selesai'
          GROUP BY p.id_produk
          ORDER BY o.tanggal_pemesanan DESC";

$stmt = $conn->prepare($query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ulasan Produk - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .star-rating {
            color: #ffc107;
            font-size: 1.5rem;
        }
        .star-rating .far {
            color: #e4e5e9;
        }
    </style>
</head>
<body>
    <?php include 'hf/header.php'; ?>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Ulasan Produk</h2>
            <a href="orders.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Kembali ke Pesanan
            </a>
        </div>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($products)): ?>
            <div class="alert alert-info">
                Belum ada produk yang dapat diulas.
            </div>
        <?php else: ?>
            <div class="row">
                <?php foreach ($products as $product): ?>
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <img src="<?php echo htmlspecialchars($product['gambar']); ?>" 
                                             class="img-fluid rounded" 
                                             alt="<?php echo htmlspecialchars($product['nama_produk']); ?>">
                                    </div>
                                    <div class="col-md-8">
                                        <h5 class="card-title"><?php echo htmlspecialchars($product['nama_produk']); ?></h5>
                                        
                                        <!-- Average Rating -->
                                        <div class="mb-2">
                                            <div class="star-rating">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <?php if ($i <= round($product['avg_rating'])): ?>
                                                        <i class="fas fa-star"></i>
                                                    <?php else: ?>
                                                        <i class="far fa-star"></i>
                                                    <?php endif; ?>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="text-muted">
                                                <?php echo number_format($product['avg_rating'], 1); ?> 
                                                (<?php echo $product['total_reviews']; ?> ulasan)
                                            </small>
                                        </div>

                                        <!-- Review Form -->
                                        <form method="POST" class="mt-3">
                                            <input type="hidden" name="id_produk" value="<?php echo $product['id_produk']; ?>">
                                            
                                            <div class="mb-3">
                                                <label class="form-label">Rating Anda</label>
                                                <div class="star-rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fa-star <?php echo $i <= $product['user_rating'] ? 'fas' : 'far'; ?>"
                                                           style="cursor: pointer;"
                                                           onclick="setRating(this, <?php echo $i; ?>, <?php echo $product['id_produk']; ?>)"></i>
                                                    <?php endfor; ?>
                                                    <input type="hidden" name="rating" id="rating_<?php echo $product['id_produk']; ?>" 
                                                           value="<?php echo $product['user_rating']; ?>">
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label for="komentar_<?php echo $product['id_produk']; ?>" class="form-label">Komentar</label>
                                                <textarea class="form-control" 
                                                          id="komentar_<?php echo $product['id_produk']; ?>" 
                                                          name="komentar" 
                                                          rows="3"><?php echo htmlspecialchars($product['user_komentar']); ?></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-primary">Kirim Ulasan</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'hf/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function setRating(star, rating, productId) {
            // Update stars
            const stars = star.parentElement.getElementsByClassName('fa-star');
            for (let i = 0; i < stars.length; i++) {
                if (i < rating) {
                    stars[i].classList.remove('far');
                    stars[i].classList.add('fas');
                } else {
                    stars[i].classList.remove('fas');
                    stars[i].classList.add('far');
                }
            }
            
            // Update hidden input
            document.getElementById('rating_' + productId).value = rating;
        }
    </script>
</body>
</html> 