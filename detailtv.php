<?php
include 'koneksi.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details
$product = null;
if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id_produk = ? AND id_kategori = 3");
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

        .tag.elektronik {
            background: #e3f2fd;
            color: #1976d2;
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
                <span class="tag elektronik">Elektronik</span>
                <div class="price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></div>

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
    <script src="js/credit.js"></script>
    <script>
        const PRODUCT_PRICE = <?php echo $product['harga']; ?>;

        document.addEventListener('DOMContentLoaded', function() {
            const creditOptionsContainer = document.getElementById('creditOptionsContainer');
            creditOptionsContainer.innerHTML = showCreditOptions(PRODUCT_PRICE);
        });

        function addToCart(productType) {
            const selectedCredit = document.querySelector('input[name="credit_option"]:checked');
            if (!selectedCredit) {
                alert('Silakan pilih metode pembayaran');
                return;
            }

            const paymentMethod = selectedCredit.value;
            let cartItem = {
                id: 'tv',
                type: 'tv',
                name: "<?php echo htmlspecialchars($product['nama_produk']); ?>",
                price: PRODUCT_PRICE,
                paymentMethod: paymentMethod,
                store: "Tech Store",
                image: "uploads/<?php echo $product['gambar']; ?>"
            };

            if (paymentMethod !== 'cash') {
                const calculation = calculateInstallment(PRODUCT_PRICE, parseInt(paymentMethod));
                cartItem.installment = {
                    months: parseInt(paymentMethod),
                    monthlyPayment: calculation.monthlyPayment,
                    adminFee: calculation.adminFee,
                    totalPrice: calculation.totalPrice
                };
            }

            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart.push(cartItem);
            localStorage.setItem('cart', JSON.stringify(cart));

            alert('Produk berhasil ditambahkan ke keranjang!');
        }

        function handleCheckout() {
            const selectedCredit = document.querySelector('input[name="credit_option"]:checked');
            if (!selectedCredit) {
                alert('Silakan pilih metode pembayaran');
                return;
            }

            const paymentMethod = selectedCredit.value;
            let checkoutItem = {
                id: 'tv',
                type: 'tv',
                name: "<?php echo htmlspecialchars($product['nama_produk']); ?>",
                price: PRODUCT_PRICE,
                paymentMethod: paymentMethod,
                store: "Tech Store",
                image: "uploads/<?php echo $product['gambar']; ?>"
            };

            if (paymentMethod !== 'cash') {
                const months = parseInt(paymentMethod);
                const result = calculateInstallment(PRODUCT_PRICE, months);
                checkoutItem.installment = {
                    months: months,
                    monthlyPayment: result.monthlyPayment,
                    adminFee: result.adminFee,
                    totalPrice: result.totalPrice
                };
            }

            // Store checkout item in localStorage as a single-item array
            localStorage.setItem('checkoutItems', JSON.stringify([checkoutItem]));

            // Redirect to checkout page
            window.location.href = 'checkout.php';
        }
    </script>
</body>

</html>