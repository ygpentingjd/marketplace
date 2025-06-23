<?php
session_start();
include 'koneksi.php';

// Cek jika user belum login
if (!isset($_SESSION['id_user'])) {
    // Simpan halaman yang ingin diakses
    $_SESSION['redirect_url'] = 'cart.php';
    header("Location: login2.php?error=login_required&message=Silakan+login+terlebih+dahulu");
    exit();
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #fff;
            font-family: Arial, sans-serif;
            min-width: 1400px;
        }

        .cart-container {
            width: 1400px;
            margin: 20px auto;
            padding: 0;
        }

        .cart-title {
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #333;
        }

        .cart-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px 20px;
            margin-bottom: 20px;
        }

        .store-section {
            background: #fff;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid #eee;
        }

        .store-name {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 15px;
            color: #333;
        }

        .cart-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            gap: 15px;
            border-bottom: 1px solid #f5f5f5;
        }

        .cart-item:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .cart-item:first-child {
            padding-top: 0;
        }

        .product-image {
            width: 90px;
            height: 90px;
            object-fit: contain;
        }

        .product-info {
            flex: 1;
            padding-right: 20px;
        }

        .product-name {
            font-size: 14px;
            color: #333;
            margin-bottom: 4px;
        }

        .product-size {
            font-size: 12px;
            color: #666;
        }

        .product-price {
            font-size: 14px;
            font-weight: 500;
            color: #333;
            margin-top: 5px;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .action-btn {
            background: none;
            border: none;
            padding: 5px;
            color: #666;
        }

        .action-btn:hover {
            color: #333;
        }

        .form-check-input {
            margin-right: 12px;
            width: 16px;
            height: 16px;
        }

        .checkout-section {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            position: sticky;
            top: 20px;
        }

        .total-section {
            margin-bottom: 15px;
        }

        .total-label {
            color: #333;
            font-size: 14px;
        }

        .total-amount {
            font-size: 14px;
            color: #333;
            text-align: right;
        }

        .checkout-btn {
            width: 100%;
            background: #000;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 8px;
            font-size: 14px;
        }

        .checkout-btn:hover {
            background: #333;
        }

        .row {
            margin: 0 -15px;
        }

        .col-lg-8 {
            width: 70%;
            padding: 0 15px;
            float: left;
        }

        .col-lg-4 {
            width: 30%;
            padding: 0 15px;
            float: left;
        }

        /* Clear float */
        .row::after {
            content: "";
            clear: both;
            display: table;
        }
    </style>
</head>

<body>
    <?php include 'hf/header.php'; ?>

    <div class="cart-container">
        <h1 class="cart-title">Keranjang</h1>

        <div class="row">
            <div class="col-lg-8">
                <div class="cart-section">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="selectAll" onchange="toggleAll(this)">
                        <label class="form-check-label" for="selectAll">
                            Pilih Semua
                        </label>
                    </div>
                </div>

                <div id="cartItems">
                    <!-- Cart items will be loaded here -->
                </div>
            </div>

            <div class="col-lg-4">
                <div class="checkout-section">
                    <div class="total-section">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="total-label">Total</span>
                            <span id="totalPrice" class="total-amount">-</span>
                        </div>
                    </div>
                    <button class="checkout-btn" onclick="proceedToCheckout()">
                        Checkout
                    </button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'hf/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadCartItems();
        });

        function loadCartItems() {
            const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            const cartContainer = document.getElementById('cartItems');

            if (cartItems.length === 0) {
                cartContainer.innerHTML = `
                    <div class="store-section text-center py-5">
                        <p class="mb-0">Keranjang Belanja Kosong</p>
                    </div>
                `;
                return;
            }

            // Group items by store
            const itemsByStore = {};
            cartItems.forEach(item => {
                const store = item.store || 'Preloved By Ocaa';
                if (!itemsByStore[store]) {
                    itemsByStore[store] = [];
                }
                itemsByStore[store].push(item);
            });

            let cartHTML = '';
            let globalIndex = 0;

            // Render items grouped by store
            for (const [store, items] of Object.entries(itemsByStore)) {
                cartHTML += `
                    <div class="store-section">
                        <div class="form-check">
                            <input class="form-check-input store-checkbox" type="checkbox" 
                                onchange="toggleStore(this, '${store}')"
                                data-store="${store}">
                            <label class="form-check-label store-name">
                                ${store}
                            </label>
                        </div>
                `;

                items.forEach((item) => {
                    cartHTML += `
                        <div class="cart-item">
                            <input class="form-check-input item-checkbox" type="checkbox" 
                                   data-store="${store}"
                                   data-index="${globalIndex}"
                                   data-price="${item.harga}"
                                   onchange="updateTotal()">
                            <img src="${item.gambar}" 
                                 class="product-image" 
                                 alt="${item.nama_produk}"
                                 onerror="this.onerror=null; this.src='image/default.png';">
                            <div class="product-info">
                                <div class="product-name">${item.nama_produk}</div>
                                ${item.ukuran ? `<div class="product-size">Size: ${item.ukuran}</div>` : ''}
                                <div class="product-price">Rp${parseInt(item.harga).toLocaleString('id-ID')}</div>
                            </div>
                            <div class="action-buttons">
                                <button class="action-btn" onclick="moveToWishlist(${globalIndex})">
                                    <i class="far fa-heart"></i>
                                </button>
                                <button class="action-btn" onclick="removeFromCart(${globalIndex})">
                                    <i class="far fa-trash-alt"></i>
                                </button>
                            </div>
                        </div>
                    `;
                    globalIndex++;
                });

                cartHTML += `</div>`;
            }

            cartContainer.innerHTML = cartHTML;
            updateTotal();
        }

        function toggleStore(checkbox, store) {
            const storeItems = document.querySelectorAll(`.item-checkbox[data-store="${store}"]`);
            storeItems.forEach(item => {
                item.checked = checkbox.checked;
            });
            updateTotal();
        }

        function toggleAll(checkbox) {
            const itemCheckboxes = document.querySelectorAll('.item-checkbox');
            const storeCheckboxes = document.querySelectorAll('.store-checkbox');
            itemCheckboxes.forEach(item => item.checked = checkbox.checked);
            storeCheckboxes.forEach(item => item.checked = checkbox.checked);
            updateTotal();
        }

        function updateTotal() {
            const selectedItems = document.querySelectorAll('.item-checkbox:checked');
            let total = 0;

            selectedItems.forEach(item => {
                total += parseFloat(item.dataset.price);
            });

            document.getElementById('totalPrice').textContent = total > 0 ?
                `Rp${total.toLocaleString('id-ID')}` : '-';
        }

        function moveToWishlist(index) {
            const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            const wishlist = JSON.parse(localStorage.getItem('wishlist')) || [];

            const item = cartItems[index];
            cartItems.splice(index, 1);

            if (!wishlist.some(w => w.id === item.id)) {
                wishlist.push(item);
            }

            localStorage.setItem('cart', JSON.stringify(cartItems));
            localStorage.setItem('wishlist', JSON.stringify(wishlist));

            loadCartItems();
        }

        function removeFromCart(index) {
            const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            cartItems.splice(index, 1);
            localStorage.setItem('cart', JSON.stringify(cartItems));
            loadCartItems();
        }

        function proceedToCheckout() {
            const selectedItems = document.querySelectorAll('.item-checkbox:checked');
            if (selectedItems.length === 0) {
                alert('Silakan pilih produk yang ingin dibeli');
                return;
            }

            const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            const checkoutItems = [];

            selectedItems.forEach(checkbox => {
                const index = parseInt(checkbox.dataset.index);
                const item = cartItems[index];
                if (item) {
                    checkoutItems.push({
                        ...item
                    }); // Create a copy of the item
                }
            });

            if (checkoutItems.length > 0) {
                localStorage.setItem('checkoutItems', JSON.stringify(checkoutItems));
                window.location.href = 'checkout.php';
            } else {
                alert('Terjadi kesalahan saat memproses item. Silakan coba lagi.');
            }
        }
    </script>
</body>

</html>