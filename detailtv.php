<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Samsung TV 43 inch - Detail Produk</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-image {
            max-width: 100%;
            height: auto;
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
    </style>
</head>

<body>
    <?php include 'hf/header.php'; ?>

    <div class="container" style="max-width: 1200px; margin: auto; padding: 20px; border-radius: 10px;">
        <div class="product-detail" style="display: flex; gap: 20px; flex-wrap: wrap;">
            <div class="product-image" style="flex: 1; min-width: 300px;">
                <img src="image/tv.png" alt="Samsung TV 43 Inch" style="width: 100%; border-radius: 10px;">
            </div>
            <div class="product-info" style="flex: 2; min-width: 300px;">
                <h2>Samsung TV 43 Inch</h2>
                <span class="tag" style="background: lightgreen; padding: 5px; border-radius: 5px;">Elektronik</span>
                <h3>Rp 4.500.000</h3>
                <br>
                <div id="creditOptionsContainer"></div>
                <br>
                <div style="display: flex; gap: 10px;">
                    <button onclick="addToCart('tv')" style="flex: 1; padding: 10px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">
                        <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                    </button>
                    <button onclick="handleCheckout()" style="flex: 1; padding: 10px; background: black; color: white; border: none; border-radius: 5px; cursor: pointer;">Checkout</button>
                </div>
                <br>
                <div class="description" style="border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                    <strong>Deskripsi</strong>
                    <p>TV Samsung 43 Inch OLED<br>Condition: 100% mulus (Original)</p>
                </div>
            </div>
        </div>
        <br>
        <h3>Review terbaru</h3>
        <div class="reviews" style="display: flex; gap: 20px;">
            <div class="review-card" style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 10px;">
                <p>⭐⭐⭐⭐⭐</p>
                <p>TV bagus dan original</p>
                <br><strong>John Doe</strong>
                <br><small>15-03-2025</small>
            </div>
            <div class="review-card" style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 10px;">
                <p>⭐⭐⭐⭐</p>
                <p>Pengiriman cepat dan aman</p>
                <br><strong>Jane Smith</strong>
                <br><small>10-03-2024</small>
            </div>
            <div class="review-card" style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 10px;">
                <p>⭐⭐⭐⭐⭐</p>
                <p>Kualitas gambar sangat bagus</p>
                <br><strong>Mike Johnson</strong>
                <br><small>05-02-2025</small>
            </div>
        </div>
        <br>
        <div style="text-align: center;">
            <h3>Tech Store</h3>
            <p>Kota Surakarta</p>
            <button style="padding: 5px 10px; background: black; color: white; border-radius: 5px; cursor: pointer;">Kunjungi</button>
        </div>
    </div>

    <?php include 'hf/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/credit.js"></script>
    <script>
        const PRODUCT_PRICE = 4500000;

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
                name: "Samsung TV 43 Inch",
                price: PRODUCT_PRICE,
                paymentMethod: paymentMethod,
                store: "Tech Store",
                image: "image/tv.png"
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
                name: "Samsung TV 43 Inch",
                price: PRODUCT_PRICE,
                paymentMethod: paymentMethod,
                store: "Tech Store",
                image: "image/tv.png"
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