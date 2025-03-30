<?php include 'hf/header.php'; ?>

<div class="container" style="max-width: 1200px; margin: auto; padding: 20px; border-radius: 10px;">
    <div class="product-detail" style="display: flex; gap: 20px; flex-wrap: wrap;">
        <div class="product-image" style="flex: 1; min-width: 300px;">
            <img src="image/knalpot.png" alt="Kenalpot DBS Ninja" style="width: 100%; border-radius: 10px;">
        </div>
        <div class="product-info" style="flex: 2; min-width: 300px;">
            <h2>Kenalpot DBS Ninja</h2>
            <span class="tag" style="background: lightgreen; padding: 5px; border-radius: 5px;">Tag</span>
            <h3>Rp 2.830.000</h3>
            <br>
            <div id="creditOptionsContainer"></div>
            <br>
            <div style="display: flex; gap: 10px;">
                <button onclick="addToCart('knalpot')" style="flex: 1; padding: 10px; background: #4CAF50; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    <i class="fas fa-shopping-cart"></i> Tambah ke Keranjang
                </button>
                <button onclick="handleCheckout()" style="flex: 1; padding: 10px; background: black; color: white; border: none; border-radius: 5px; cursor: pointer;">Checkout</button>
            </div>
            <br>
            <div class="description" style="border: 1px solid #ddd; padding: 10px; border-radius: 5px;">
                <strong>Deskripsi</strong>
                <p>Kenalpot DBS untuk motor ninja<br>Condition: 80% mulus minus pemakaian (Original)</p>
            </div>
        </div>
    </div>
    <br>
    <h3>Riview terbaru</h3>
    <div class="reviews" style="display: flex; gap: 20px;">
        <div class="review-card" style="flex: 1; border: 1px solid #ddd; padding: 10px; border-radius: 10px;">
            <p>⭐⭐⭐⭐⭐</p>
            <p>Produk mulus dan original</p>
            <br><strong>AhmadRacing</strong>
            <br><small>02-01-2022</small>
        </div>
    </div>
    <br>
    <div style="text-align: center;">
        <h3>Ranto Kopling</h3>
        <p>Kota Surakarta</p>
        <button style="padding: 5px 10px; background: black; color: white; border-radius: 5px; cursor: pointer;">Kunjungi</button>
    </div>
</div>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="js/credit.js"></script>
<script>
    const PRODUCT_PRICE = 2830000;

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
            id: 'knalpot',
            type: 'knalpot',
            name: "Kenalpot DBS Ninja",
            price: PRODUCT_PRICE,
            paymentMethod: paymentMethod,
            store: "Ranto Kopling",
            image: "image/knalpot.png"
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
            id: 'knalpot',
            name: "Kenalpot DBS Ninja",
            price: PRODUCT_PRICE,
            paymentMethod: paymentMethod,
            store: "Ranto Kopling",
            image: "image/knalpot.png"
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

<?php include 'hf/footer.php'; ?>