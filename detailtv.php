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

    <div class="product-details">
        <div class="row">
            <div class="col-md-6">
                <img src="image/tv.png" alt="Samsung TV 43 inch" class="product-image">
            </div>
            <div class="col-md-6">
                <h1>Samsung TV 43 inch</h1>
                <div class="price mb-3">Rp 4.500.000</div>

                <div id="creditOptionsContainer"></div>

                <div class="mt-3">
                    <button class="btn btn-success" onclick="addToCart()">Tambah ke Keranjang</button>
                    <a href="checkout.php"><button class="btn btn-dark" onclick="checkout()">Checkout</button></a>
                </div>

                <div class="mt-4">
                    <h4>Deskripsi Produk</h4>
                    <p>TV Samsung 43 Inch OLED</p>
                    <p>Condition: 100% mulus</p>
                </div>
            </div>
        </div>
    </div>

    <?php include 'hf/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/credit.js"></script>
    <script>
        const productPrice = 4500000;

        // Tampilkan opsi kredit saat halaman dimuat
        document.addEventListener('DOMContentLoaded', function() {
            const creditOptionsContainer = document.getElementById('creditOptionsContainer');
            creditOptionsContainer.innerHTML = showCreditOptions(productPrice);
        });

        function addToCart() {
            const selectedOption = document.querySelector('input[name="credit_option"]:checked');
            if (!selectedOption) {
                alert('Silakan pilih metode pembayaran');
                return;
            }

            const paymentMethod = selectedOption.value;
            let cartItem = {
                id: 'tv',
                name: 'Samsung TV 43 inch',
                price: productPrice,
                paymentMethod: paymentMethod,
                store: "Preloved By Ocaa"
            };

            if (paymentMethod !== 'cash') {
                const calculation = calculateInstallment(productPrice, parseInt(paymentMethod));
                cartItem.installment = {
                    months: parseInt(paymentMethod),
                    monthlyPayment: calculation.monthlyPayment,
                    adminFee: calculation.adminFee,
                    totalPrice: calculation.totalPrice
                };
            }

            // Simpan ke localStorage
            let cart = JSON.parse(localStorage.getItem('cart')) || [];
            cart.push(cartItem);
            localStorage.setItem('cart', JSON.stringify(cart));

            alert('Produk berhasil ditambahkan ke keranjang!');
        }

        function checkout() {
            const selectedOption = document.querySelector('input[name="credit_option"]:checked');
            if (!selectedOption) {
                alert('Silakan pilih metode pembayaran');
                return;
            }

            const paymentMethod = selectedOption.value;
            let checkoutInfo = {
                id: 'tv-samsung-43',
                name: 'Samsung TV 43 inch',
                price: productPrice,
                paymentMethod: paymentMethod
            };

            if (paymentMethod !== 'cash') {
                const calculation = calculateInstallment(productPrice, parseInt(paymentMethod));
                checkoutInfo.installment = {
                    months: parseInt(paymentMethod),
                    monthlyPayment: calculation.monthlyPayment,
                    adminFee: calculation.adminFee,
                    totalPrice: calculation.totalPrice
                };
            }

            // Simpan informasi checkout ke localStorage
            localStorage.setItem('checkout', JSON.stringify(checkoutInfo));

            // Redirect ke halaman checkout
            window.location.href = 'checkout.php';
        }
    </script>
</body>

</html>