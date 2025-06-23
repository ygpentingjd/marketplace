<?php
include 'koneksi.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details
$product = null;
if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id_produk = ? AND id_kategori = 1");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

// Tambahkan query untuk mengambil data penjual
$seller_query = $conn->prepare("SELECT u.nama, u.nomor_telepon FROM users u JOIN products p ON u.id_user = p.id_user WHERE p.id_produk = ?");
$seller_query->bind_param("i", $product_id);
$seller_query->execute();
$seller_result = $seller_query->get_result();
$seller_data = $seller_result->fetch_assoc();

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

if (!$product) {
    die("Product not found.");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['nama_produk']); ?> - Detail Produk</title>
    <?php include 'hf/style.php'; ?>
    <style>
        .product-image {
            max-width: 100%;
            height: auto;
            border-radius: 10px;
        }

        .product-details {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .price {
            font-size: 24px;
            font-weight: bold;
            color: #2c3e50;
        }

        .product-info {
            padding: 20px;
        }

        .description {
            border: 1px solid #ddd;
            padding: 15px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .action-buttons button,
        .action-buttons a {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-align: center;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 5px;
            font-weight: 500;
        }

        .btn-cart {
            background: #4CAF50;
            color: white;
        }

        .btn-checkout {
            background: #000;
            color: white;
        }

        .btn-whatsapp {
            background: #25D366;
            color: white;
        }

        .tag {
            display: inline-block;
            padding: 5px 15px;
            border-radius: 5px;
            font-size: 14px;
            margin: 10px 0;
        }

        .tag.fashion {
            background: #fce4ec;
            color: #c2185b;
        }

        .size-guide {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .size-guide h4 {
            margin-bottom: 15px;
            color: #333;
        }

        .size-options {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .size-option {
            padding: 8px 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .size-option:hover {
            background: #e9ecef;
        }

        .size-option.selected {
            background: #007bff;
            color: white;
            border-color: #007bff;
        }

        .product-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .detail-item {
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .detail-item strong {
            display: block;
            margin-bottom: 5px;
            color: #666;
        }

        /* Review Styles */
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
    <?php include 'hf/header.php'; ?>

    <div class="container" style="max-width: 1200px; margin: auto; padding: 20px;">
        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo $product['gambar']; ?>"
                    alt="<?php echo htmlspecialchars($product['nama_produk']); ?>"
                    class="product-image">
            </div>
            <div class="col-md-6 product-info">
                <h1><?php echo htmlspecialchars($product['nama_produk']); ?></h1>
                <span class="tag fashion">Fashion</span>
                <div class="price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></div>

                <!-- Rating Summary -->
                <div class="rating-summary mt-3">
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

                <div class="specs" id="creditOptionsContainer">
                    <h4>Opsi Cicilan</h4>
                    <div class="credit-options">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="credit_option" value="3" id="credit3">
                            <label class="form-check-label" for="credit3">Cicilan 3 Bulan</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="credit_option" value="6" id="credit6">
                            <label class="form-check-label" for="credit6">Cicilan 6 Bulan</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="credit_option" value="12" id="credit12">
                            <label class="form-check-label" for="credit12">Cicilan 12 Bulan</label>
                        </div>
                        <div id="creditDetails" class="mt-3" style="display: none;">
                            <div class="alert alert-info">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Harga Produk:</span>
                                    <span id="productPrice">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Biaya Admin (2%):</span>
                                    <span id="adminFee">-</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Cicilan per Bulan:</span>
                                    <span id="monthlyPayment">-</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total Pembayaran:</span>
                                    <span id="totalPayment">-</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="size-guide">
                    <h4>Pilih Ukuran</h4>
                    <div class="size-options">
                        <?php
                        $sizes = ['36', '37', '38', '39', '40', '41', '42', '43', '44'];
                        foreach ($sizes as $size) {
                            echo "<div class='size-option' onclick='selectSize(this)'>$size</div>";
                        }
                        ?>
                    </div>
                </div>

                <div class="action-buttons">
                    <button onclick="addToCart()" class="btn-cart">
                        <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                    </button>
                    <button onclick="buyNow()" class="btn-checkout">Checkout</button>
                    <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $seller_data['nomor_telepon']); ?>?text=Halo%20<?php echo urlencode($seller_data['nama']); ?>,%20saya%20tertarik%20dengan%20produk:%20<?php echo urlencode($product['nama_produk']); ?>"
                        class="btn-whatsapp" target="_blank">
                        <i class="fab fa-whatsapp"></i> Chat Penjual
                    </a>
                </div>

                <div class="description">
                    <h4>Deskripsi Produk</h4>
                    <p><?php echo nl2br(htmlspecialchars($product['deskripsi'] ?? 'Tidak ada deskripsi produk.')); ?></p>
                </div>

                <div class="product-details-grid">
                    <div class="detail-item">
                        <strong>Brand</strong>
                        <?php echo htmlspecialchars($product['brand'] ?? 'Generic'); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Material</strong>
                        <?php echo htmlspecialchars($product['material'] ?? 'Canvas'); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Warna</strong>
                        <?php echo htmlspecialchars($product['warna'] ?? 'Hitam'); ?>
                    </div>
                    <div class="detail-item">
                        <strong>Kondisi</strong>
                        <?php echo htmlspecialchars($product['kondisi'] ?? 'Baru'); ?>
                    </div>
                </div>

                <?php if ($product['stok'] > 0): ?>
                    <div class="tag" style="background: #e8f5e9; color: #2e7d32;">
                        Stok: <?php echo $product['stok']; ?> unit
                    </div>
                <?php else: ?>
                    <div class="tag" style="background: #ffebee; color: #c62828;">
                        Stok Habis
                    </div>
                <?php endif; ?>

                <!-- Reviews Section -->
                <div class="row mt-5">
                    <div class="col-12">
                        <h3>Ulasan Produk</h3>
                        
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
        </div>
    </div>

    <?php include 'hf/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const PRODUCT_PRICE = <?php echo $product['harga']; ?>;

        document.addEventListener('DOMContentLoaded', function() {
            // Handle credit option changes
            document.querySelectorAll('input[name="credit_option"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    const months = parseInt(this.value);
                    const result = calculateInstallment(PRODUCT_PRICE, months);
                    
                    document.getElementById('adminFee').textContent = `Rp${result.adminFee.toLocaleString('id-ID')}`;
                    document.getElementById('monthlyPayment').textContent = `Rp${result.monthlyPayment.toLocaleString('id-ID')}`;
                    document.getElementById('totalPayment').textContent = `Rp${result.totalPrice.toLocaleString('id-ID')}`;
                    
                    document.getElementById('creditDetails').style.display = 'block';
                });
            });
        });

        function calculateInstallment(price, months) {
            const adminFee = Math.round(price * 0.02); // 2% admin fee
            const totalPrice = price + adminFee;
            const monthlyPayment = Math.round(totalPrice / months);
            
            return {
                adminFee: adminFee,
                monthlyPayment: monthlyPayment,
                totalPrice: totalPrice
            };
        }

        function selectSize(element) {
            // Remove selected class from all size options
            document.querySelectorAll('.size-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            // Add selected class to clicked size option
            element.classList.add('selected');
            // Update hidden input
            document.getElementById('selected_size_input').value = element.textContent;
        }

        function validateSize() {
            const selectedSize = document.getElementById('selected_size_input').value;
            if (!selectedSize) {
                alert('Silakan pilih ukuran sepatu terlebih dahulu');
                return false;
            }
            return true;
        }

        function addToCart() {
            const selectedSize = document.querySelector('.size-option.selected');
            if (!selectedSize) {
                alert('Silakan pilih ukuran sepatu terlebih dahulu');
                return;
            }

            const selectedCredit = document.querySelector('input[name="credit_option"]:checked');
            let paymentMethod = 'cash';
            let installmentDetails = null;

            if (selectedCredit) {
                const months = parseInt(selectedCredit.value);
                const result = calculateInstallment(PRODUCT_PRICE, months);
                paymentMethod = 'credit';
                installmentDetails = {
                    months: months,
                    monthlyPayment: result.monthlyPayment,
                    adminFee: result.adminFee,
                    totalPrice: result.totalPrice
                };
            }

            const cartItem = {
                id_produk: <?php echo $product['id_produk']; ?>,
                nama_produk: "<?php echo htmlspecialchars($product['nama_produk']); ?>",
                harga: PRODUCT_PRICE,
                gambar: "<?php echo $product['gambar']; ?>",
                ukuran: selectedSize.textContent,
                qty: 1,
                store: "<?php echo htmlspecialchars($seller_data['nama']); ?>",
                payment_method: paymentMethod,
                installment: installmentDetails
            };

            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart.push(cartItem);
            localStorage.setItem('cart', JSON.stringify(cart));

            alert('Produk berhasil ditambahkan ke keranjang!');
            window.location.href = 'cart.php';
        }

        function buyNow() {
            const selectedSize = document.querySelector('.size-option.selected');
            if (!selectedSize) {
                alert('Silakan pilih ukuran sepatu terlebih dahulu');
                return;
            }

            const selectedCredit = document.querySelector('input[name="credit_option"]:checked');
            let paymentMethod = 'cash';
            let installmentDetails = null;

            if (selectedCredit) {
                const months = parseInt(selectedCredit.value);
                const result = calculateInstallment(PRODUCT_PRICE, months);
                paymentMethod = 'credit';
                installmentDetails = {
                    months: months,
                    monthlyPayment: result.monthlyPayment,
                    adminFee: result.adminFee,
                    totalPrice: result.totalPrice
                };
            }

            const checkoutItem = {
                id_produk: <?php echo $product['id_produk']; ?>,
                nama_produk: "<?php echo htmlspecialchars($product['nama_produk']); ?>",
                harga: PRODUCT_PRICE,
                gambar: "<?php echo $product['gambar']; ?>",
                ukuran: selectedSize.textContent,
                qty: 1,
                store: "<?php echo htmlspecialchars($seller_data['nama']); ?>",
                payment_method: paymentMethod,
                installment: installmentDetails
            };

            localStorage.setItem('checkoutItems', JSON.stringify([checkoutItem]));
            window.location.href = 'checkout.php';
        }
    </script>
</body>

</html>