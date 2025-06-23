<?php
include 'koneksi.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details
$product = null;
if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id_produk = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

if (!$product) {
    die("Product not found.");
}

// Get category name
$category_query = "SELECT nama_kategori FROM categories WHERE id_kategori = ?";
$stmt = $conn->prepare($category_query);
$stmt->bind_param("i", $product['id_kategori']);
$stmt->execute();
$category_result = $stmt->get_result();
$category_name = $category_result->fetch_assoc()['nama_kategori'] ?? 'Unknown';

// Get reviews for this product
$reviews_query = "SELECT r.*, u.nama as reviewer_name 
                 FROM reviews r 
                 JOIN users u ON r.id_user = u.id_user 
                 WHERE r.id_produk = ? 
                 ORDER BY r.tanggal_review DESC";
$stmt = $conn->prepare($reviews_query);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$reviews = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Calculate average rating
$avg_rating = 0;
if (!empty($reviews)) {
    $total_rating = array_sum(array_column($reviews, 'rating'));
    $avg_rating = $total_rating / count($reviews);
}

// Redirect to appropriate template based on category
switch ($product['id_kategori']) {
    case 1: // Fashion
        include 'detailsepatu.php';
        break;
    case 2: // Otomotif
        include 'detailknalpot.php';
        break;
    case 3: // Elektronik
        include 'detailtv.php';
        break;
    default:
        // Default template for unknown categories
        include 'detailtv.php';
        break;
}

// End the script here to prevent additional HTML output
exit();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['nama_produk']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .product-image {
            max-width: 100%;
            height: auto;
        }
        .star-rating {
            color: #ffc107;
            font-size: 1.2rem;
        }
        .star-rating .far {
            color: #e4e5e9;
        }
        .review-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .review-date {
            color: #666;
            font-size: 0.9rem;
        }
        .review-content {
            margin-top: 10px;
        }
        .rating-summary {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo $product['gambar']; ?>"
                    alt="<?php echo htmlspecialchars($product['nama_produk']); ?>"
                    class="product-image">
            </div>
            <div class="col-md-6">
                <h1><?php echo htmlspecialchars($product['nama_produk']); ?></h1>
                <p><strong>Price:</strong> Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                <p><strong>Kategori:</strong> <?php echo htmlspecialchars($category_name); ?></p>
                <p><strong>Kondisi:</strong> <?php echo htmlspecialchars(ucfirst($product['kondisi'])); ?></p>
                <p><strong>Stok:</strong> <?php echo $product['stok'] > 0 ? $product['stok'] . ' unit' : 'Habis'; ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($product['deskripsi'] ?? 'No description available.'); ?></p>
            </div>
        </div>

        <!-- Reviews Section -->
        <div class="row mt-5">
            <div class="col-12">
                <h3>Ulasan Produk</h3>
                
                <!-- Rating Summary -->
                <div class="rating-summary">
                    <div class="row align-items-center">
                        <div class="col-md-4 text-center">
                            <h2 class="mb-0"><?php echo number_format($avg_rating, 1); ?></h2>
                            <div class="star-rating">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <?php if ($i <= round($avg_rating)): ?>
                                        <i class="fas fa-star"></i>
                                    <?php else: ?>
                                        <i class="far fa-star"></i>
                                    <?php endif; ?>
                                <?php endfor; ?>
                            </div>
                            <p class="text-muted mb-0"><?php echo count($reviews); ?> ulasan</p>
                        </div>
                        <div class="col-md-8">
                            <?php
                            $rating_counts = array_count_values(array_column($reviews, 'rating'));
                            for ($i = 5; $i >= 1; $i--):
                                $count = $rating_counts[$i] ?? 0;
                                $percentage = count($reviews) > 0 ? ($count / count($reviews)) * 100 : 0;
                            ?>
                            <div class="d-flex align-items-center mb-2">
                                <div class="me-2" style="width: 60px;"><?php echo $i; ?> bintang</div>
                                <div class="progress flex-grow-1" style="height: 8px;">
                                    <div class="progress-bar bg-warning" role="progressbar" 
                                         style="width: <?php echo $percentage; ?>%"></div>
                                </div>
                                <div class="ms-2" style="width: 40px;"><?php echo $count; ?></div>
                            </div>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>

                <!-- Reviews List -->
                <?php if (empty($reviews)): ?>
                    <div class="alert alert-info">
                        Belum ada ulasan untuk produk ini.
                    </div>
                <?php else: ?>
                    <?php foreach ($reviews as $review): ?>
                        <div class="review-card">
                            <div class="review-header">
                                <div>
                                    <strong><?php echo htmlspecialchars($review['reviewer_name']); ?></strong>
                                    <div class="star-rating">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <?php if ($i <= $review['rating']): ?>
                                                <i class="fas fa-star"></i>
                                            <?php else: ?>
                                                <i class="far fa-star"></i>
                                            <?php endif; ?>
                                        <?php endfor; ?>
                                    </div>
                                </div>
                                <div class="review-date">
                                    <?php echo date('d M Y', strtotime($review['tanggal_review'])); ?>
                                </div>
                            </div>
                            <div class="review-content">
                                <?php echo nl2br(htmlspecialchars($review['komentar'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>