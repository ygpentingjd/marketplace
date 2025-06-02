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
                    <form method="POST" action="cart.php" style="display:inline;">
                        <input type="hidden" name="id_produk" value="<?php echo $product['id_produk']; ?>">
                        <input type="hidden" name="qty" value="1">
                        <button type="submit" class="btn-cart">
                            <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                        </button>
                    </form>
                    <form method="POST" action="checkout.php" style="display:inline;">
                        <input type="hidden" name="id_produk" value="<?php echo $product['id_produk']; ?>">
                        <input type="hidden" name="qty" value="1">
                        <button type="submit" class="btn-checkout">Checkout</button>
                    </form>
                    <a href="https://wa.me/+6282310598605?text=Halo,%20saya%20tertarik%20dengan%20produk:%20<?php echo urlencode($product['nama_produk']); ?>"
                        class="btn-whatsapp" target="_blank">
                        <i class="fab fa-whatsapp"></i> Chat WhatsApp
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
            </div>
        </div>
    </div>

    <?php include 'hf/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function selectSize(element) {
            // Remove selected class from all size options
            document.querySelectorAll('.size-option').forEach(opt => {
                opt.classList.remove('selected');
            });
            // Add selected class to clicked size option
            element.classList.add('selected');
        }
    </script>
</body>

</html>