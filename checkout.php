<?php
session_start();
include 'koneksi.php';

// Cek jika user belum login
if (!isset($_SESSION['id_user'])) {
    // Simpan halaman yang ingin diakses
    $_SESSION['redirect_url'] = 'checkout.php';
    header("Location: login2.php?error=login_required&message=Silakan+login+terlebih+dahulu");
    exit();
}

// Ambil data user untuk alamat pengiriman
$user_id = $_SESSION['id_user'];
$user_query = "SELECT * FROM users WHERE id_user = ?";
$stmt = $conn->prepare($user_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_result = $stmt->get_result();
$user_data = $user_result->fetch_assoc();

// Proses checkout jika ada POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Mulai transaksi
        $conn->begin_transaction();

        // Terima data dari form
        $items = json_decode($_POST['items'], true);
        $payment_method = $_POST['payment_method'];
        $note = $_POST['note'];

        // Hitung total harga
        $total_harga = 0;
        foreach ($items as $item) {
            $total_harga += ($item['harga'] * $item['quantity']);
        }

        // Insert ke tabel orders
        $order_query = "INSERT INTO orders (id_user, total_harga, status_pesanan, tanggal_pemesanan) 
                        VALUES (?, ?, 'diproses', NOW())";
        $stmt = $conn->prepare($order_query);
        $stmt->bind_param("id", $user_id, $total_harga);
        $stmt->execute();
        $order_id = $conn->insert_id;

        // Insert ke tabel order_details
        $detail_query = "INSERT INTO order_details (id_order, id_produk, jumlah, subtotal) VALUES (?, ?, ?, ?)";
        $stmt = $conn->prepare($detail_query);

        foreach ($items as $item) {
            $subtotal = $item['harga'] * $item['quantity'];
            $stmt->bind_param("iiid", $order_id, $item['id_produk'], $item['quantity'], $subtotal);
            $stmt->execute();

            // Update stok produk
            $update_stok = "UPDATE products SET stok = stok - ? WHERE id_produk = ?";
            $stmt_stok = $conn->prepare($update_stok);
            $stmt_stok->bind_param("ii", $item['quantity'], $item['id_produk']);
            $stmt_stok->execute();
        }

        // Mapping payment method
        $metode_db = 'Cashless';
        if (strtolower($payment_method) === 'cod') {
            $metode_db = 'COD';
        }
        // Insert ke tabel payment
        $payment_query = "INSERT INTO payment (id_order, metode, status_pembayaran) VALUES (?, ?, 'pending')";
        $stmt = $conn->prepare($payment_query);
        $stmt->bind_param("is", $order_id, $metode_db);
        $stmt->execute();

        // Insert ke tabel transactions
        $transaction_query = "INSERT INTO transactions (id_order, metode_pembayaran, status_pembayaran) 
                             VALUES (?, ?, 'pending')";
        $stmt = $conn->prepare($transaction_query);
        $stmt->bind_param("is", $order_id, $payment_method);
        $stmt->execute();

        // Hapus item dari cart
        $delete_cart = "DELETE FROM cart WHERE id_user = ?";
        $stmt = $conn->prepare($delete_cart);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();

        // Commit transaksi
        $conn->commit();

        // Redirect ke halaman orders
        header("Location: orders.php");
        exit();
    } catch (Exception $e) {
        // Rollback jika terjadi error
        $conn->rollback();
        $error_message = 'Terjadi kesalahan: ' . $e->getMessage();
    }
}

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            color: #333;
            min-width: 1400px;
        }

        .page-header {
            background: white;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
            min-width: 1400px;
        }

        .page-title {
            font-size: 16px;
            font-weight: 500;
            margin: 0;
        }

        .checkout-container {
            width: 1400px;
            margin: 20px auto;
            padding: 0;
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

        .section-white {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .address-box {
            background: #f8f9fa;
            border-radius: 4px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .address-name {
            font-weight: 500;
            margin-bottom: 5px;
        }

        .address-text {
            color: #666;
            font-size: 14px;
            margin-bottom: 0;
        }

        .btn-edit {
            color: #0d6efd;
            text-decoration: none;
            font-size: 14px;
        }

        .btn-edit:hover {
            text-decoration: underline;
        }

        .product-list {
            margin-top: 20px;
        }

        .product-item {
            display: flex;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .product-image {
            width: 80px;
            height: 80px;
            object-fit: contain;
            margin-right: 15px;
        }

        .product-info {
            flex-grow: 1;
        }

        .product-name {
            font-size: 14px;
            margin-bottom: 4px;
        }

        .product-size {
            color: #666;
            font-size: 12px;
        }

        .product-price {
            font-size: 14px;
            font-weight: 500;
            text-align: right;
        }

        .shipping-option {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            padding: 10px 0;
            border-top: 1px solid #eee;
        }

        .shipping-name {
            font-size: 14px;
            color: #666;
        }

        .shipping-price {
            font-size: 14px;
            font-weight: 500;
        }

        .payment-summary {
            margin-top: 20px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .summary-total {
            font-size: 16px;
            font-weight: 500;
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px solid #eee;
        }

        .btn-order {
            background: #000;
            color: white;
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 4px;
            margin-top: 20px;
        }

        .btn-order:hover {
            background: #333;
        }

        .dropship-checkbox {
            margin-top: 15px;
        }

        .form-control {
            font-size: 14px;
        }

        .note-input {
            margin-top: 15px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            width: 100%;
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

        .product-amount {
            padding: 15px;
            border-top: 1px solid #eee;
        }

        .product-amount .input-group {
            border-radius: 4px;
            overflow: hidden;
        }

        .product-amount .form-control {
            border-left: 0;
            border-right: 0;
            text-align: center;
            font-weight: 500;
        }

        .product-amount .btn {
            width: 40px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .product-amount .btn-outline-secondary {
            border-color: #ddd;
            color: #666;
        }

        .product-amount .btn-outline-secondary:hover {
            background-color: #f8f9fa;
            border-color: #ddd;
            color: #333;
        }
    </style>
</head>

<body>
    <?php include 'hf/header.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex align-items-center">
                <a href="cart.php" class="text-dark text-decoration-none">Keranjang</a>
                <span class="mx-2">|</span>
                <span class="fw-500">Checkout</span>
            </div>
        </div>
    </div>

    <?php if (isset($error_message)): ?>
        <div class="alert alert-danger" role="alert">
            <?php echo htmlspecialchars($error_message); ?>
        </div>
    <?php endif; ?>

    <div class="checkout-container">
        <div class="row">
            <div class="col-lg-8">
                <!-- Alamat Pengiriman -->
                <div class="section-white">
                    <div class="section-title">
                        <i class="fas fa-map-marker-alt"></i>
                        Alamat Pengirim
                    </div>
                    <div class="address-box">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <div class="address-name"><?php echo htmlspecialchars($user_data['nama']); ?> (+62) <?php echo htmlspecialchars($user_data['nomor_telepon']); ?></div>
                                <p class="address-text"><?php echo htmlspecialchars($user_data['alamat']); ?></p>
                            </div>
                            <a href="profile.php" class="btn-edit">Ubah</a>
                        </div>
                    </div>
                    <div class="dropship-checkbox">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="dropshipCheck">
                            <label class="form-check-label" for="dropshipCheck">
                                Kirim sebagai dropshipper
                            </label>
                        </div>
                    </div>
                    <div id="dropshipForm" class="mt-3" style="display: none;">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <input type="text" class="form-control" placeholder="Nama">
                            </div>
                            <div class="col-md-6">
                                <input type="tel" class="form-control" placeholder="Nomor Telepon">
                            </div>
                        </div>
                        <button class="btn btn-sm btn-primary mt-2">Simpan</button>
                    </div>
                </div>

                <!-- Produk Dipesan -->
                <div class="section-white">
                    <div class="section-title">Produk Dipesan</div>
                    <div class="product-list">
                        <!-- Products will be loaded here -->
                    </div>
                    <div class="product-amount mt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-500">Total Produk</span>
                            <div class="input-group" style="width: 150px;">
                                <button class="btn btn-outline-secondary" type="button" onclick="decrementQuantity()">-</button>
                                <input type="number" class="form-control text-center" id="totalProduct" value="1" min="1" max="99" onchange="updateTotals()">
                                <button class="btn btn-outline-secondary" type="button" onclick="incrementQuantity()">+</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Metode Pembayaran -->
                <div class="section-white">
                    <div class="section-title">Metode Pembayaran</div>
                    <div class="payment-methods">
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="codPayment" value="cod" checked>
                            <label class="form-check-label" for="codPayment">
                                COD (Bayar di Tempat)
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="transferPayment" value="transfer">
                            <label class="form-check-label" for="transferPayment">
                                Transfer Bank
                            </label>
                        </div>
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="installmentPayment" value="installment">
                            <label class="form-check-label" for="installmentPayment">
                                Cicilan
                            </label>
                        </div>
                        <div id="installmentOptions" class="ms-4 mb-3" style="display: none;">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="installmentPeriod" value="3">
                                <label class="form-check-label">3 Bulan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="installmentPeriod" value="6">
                                <label class="form-check-label">6 Bulan</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="installmentPeriod" value="12">
                                <label class="form-check-label">12 Bulan</label>
                            </div>
                            <div id="installmentDetails" class="mt-2 p-2 border rounded" style="display: none;">
                                <!-- Installment details will be shown here -->
                            </div>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="paymentMethod" id="ewalletPayment" value="ewallet">
                            <label class="form-check-label" for="ewalletPayment">
                                E-Wallet
                            </label>
                        </div>
                        <div id="ewalletOptions" class="ms-4" style="display: none;">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="ewalletType" value="gopay">
                                <label class="form-check-label">GoPay</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="ewalletType" value="ovo">
                                <label class="form-check-label">OVO</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="ewalletType" value="dana">
                                <label class="form-check-label">DANA</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="section-white" style="position: sticky; top: 20px;">
                    <div class="section-title">Ringkasan Belanja</div>
                    <div class="payment-summary">
                        <div class="summary-row">
                            <span>Total Harga</span>
                            <span id="subtotal">Rp0</span>
                        </div>
                        <div class="summary-row">
                            <span>Biaya Pengiriman</span>
                            <span id="shippingCost">Rp0</span>
                        </div>
                        <div class="summary-row">
                            <span>Biaya Layanan</span>
                            <span id="serviceFee">Rp0</span>
                        </div>
                        <div id="installmentSummary" style="display: none;">
                            <hr class="my-2">
                            <div class="summary-row text-primary">
                                <span>Informasi Cicilan:</span>
                            </div>
                            <div class="summary-row">
                                <span>Tenor</span>
                                <span id="installmentPeriod">-</span>
                            </div>
                            <div class="summary-row">
                                <span>Biaya Admin (2%)</span>
                                <span id="installmentAdminFee">Rp0</span>
                            </div>
                            <div class="summary-row">
                                <span>Cicilan per Bulan</span>
                                <span id="monthlyPayment">Rp0</span>
                            </div>
                            <div class="summary-row">
                                <span>Total dengan Cicilan</span>
                                <span id="installmentTotal">Rp0</span>
                            </div>
                        </div>
                        <div class="summary-total">
                            <div class="d-flex justify-content-between">
                                <span>Total Tagihan</span>
                                <span id="totalAmount">Rp0</span>
                            </div>
                            <div id="totalNote" class="small text-muted mt-1" style="display: none;">
                                *Pembayaran pertama termasuk DP dan cicilan bulan pertama
                            </div>
                        </div>
                    </div>
                    <textarea class="note-input mb-3" name="note" placeholder="Catatan untuk penjual (opsional)"></textarea>
                    <button class="btn-order" onclick="placeOrder()">Buat Pesanan</button>
                </div>
            </div>
        </div>
    </div>

    <?php include 'hf/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadCheckoutItems();
            setupDropshipToggle();
            setupPaymentMethods();
            updateTotals();
        });

        function loadCheckoutItems() {
            const checkoutItems = JSON.parse(localStorage.getItem('checkoutItems')) || [];
            const items = checkoutItems.length > 0 ? checkoutItems : (JSON.parse(localStorage.getItem('cart')) || []);
            const productList = document.querySelector('.product-list');

            if (items.length === 0) {
                productList.innerHTML = `
                    <div class="text-center py-4">
                        <p class="mb-0">Tidak ada produk yang dipilih</p>
                        <a href="cart.php" class="btn btn-link">Kembali ke Keranjang</a>
                    </div>
                `;
                updateTotals();
                return;
            }

            let productHTML = '';
            items.forEach(item => {
                productHTML += `
                    <div class="cart-item">
                        <img src="${item.gambar}" 
                             class="product-image" 
                             alt="${item.nama_produk}"
                             onerror="this.src='image/default.png';">
                        <div class="product-info">
                            <div class="product-name">${item.nama_produk}</div>
                            <div class="product-price">Rp${parseInt(item.harga).toLocaleString('id-ID')}</div>
                        </div>
                    </div>
                `;
            });

            productList.innerHTML = productHTML;
            updateTotals();
        }

        function incrementQuantity() {
            const input = document.getElementById('totalProduct');
            const currentValue = parseInt(input.value);
            if (currentValue < parseInt(input.max)) {
                input.value = currentValue + 1;
                updateTotals();
            }
        }

        function decrementQuantity() {
            const input = document.getElementById('totalProduct');
            const currentValue = parseInt(input.value);
            if (currentValue > parseInt(input.min)) {
                input.value = currentValue - 1;
                updateTotals();
            }
        }

        function updateTotals() {
            const checkoutItems = JSON.parse(localStorage.getItem('checkoutItems')) || [];
            const items = checkoutItems.length > 0 ? checkoutItems : (JSON.parse(localStorage.getItem('cart')) || []);
            const quantity = parseInt(document.getElementById('totalProduct').value) || 1;
            let subtotal = 0;

            items.forEach(item => {
                subtotal += parseInt(item.harga) * quantity;
            });

            const shippingCost = 15000;
            const serviceFee = Math.round(subtotal * 0.02);
            const total = subtotal + shippingCost + serviceFee;

            document.getElementById('subtotal').textContent = `Rp${subtotal.toLocaleString('id-ID')}`;
            document.getElementById('shippingCost').textContent = `Rp${shippingCost.toLocaleString('id-ID')}`;
            document.getElementById('serviceFee').textContent = `Rp${serviceFee.toLocaleString('id-ID')}`;
            document.getElementById('totalAmount').textContent = `Rp${total.toLocaleString('id-ID')}`;

            // Disable order button if no items
            const orderButton = document.querySelector('.btn-order');
            if (items.length === 0) {
                orderButton.disabled = true;
                orderButton.style.opacity = '0.5';
            } else {
                orderButton.disabled = false;
                orderButton.style.opacity = '1';
            }
        }

        function setupDropshipToggle() {
            const dropshipCheck = document.getElementById('dropshipCheck');
            const dropshipForm = document.getElementById('dropshipForm');

            dropshipCheck.addEventListener('change', function() {
                dropshipForm.style.display = this.checked ? 'block' : 'none';
            });
        }

        function setupPaymentMethods() {
            const installmentPayment = document.getElementById('installmentPayment');
            const installmentOptions = document.getElementById('installmentOptions');
            const ewalletPayment = document.getElementById('ewalletPayment');
            const ewalletOptions = document.getElementById('ewalletOptions');
            const installmentDetails = document.getElementById('installmentDetails');
            const installmentSummary = document.getElementById('installmentSummary');
            const totalNote = document.getElementById('totalNote');

            // Handle payment method changes
            document.querySelectorAll('input[name="paymentMethod"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    installmentOptions.style.display = installmentPayment.checked ? 'block' : 'none';
                    ewalletOptions.style.display = ewalletPayment.checked ? 'block' : 'none';
                    if (!installmentPayment.checked) {
                        document.querySelectorAll('input[name="installmentPeriod"]').forEach(radio => radio.checked = false);
                        installmentDetails.style.display = 'none';
                        installmentSummary.style.display = 'none';
                        totalNote.style.display = 'none';
                        updateTotals();
                    }
                    if (!ewalletPayment.checked) {
                        document.querySelectorAll('input[name="ewalletType"]').forEach(radio => radio.checked = false);
                    }
                });
            });

            // Handle installment period changes
            document.querySelectorAll('input[name="installmentPeriod"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.checked) {
                        const months = parseInt(this.value);
                        const subtotal = parseFloat(document.getElementById('subtotal').textContent.replace(/[^0-9]/g, ''));
                        const result = calculateInstallment(subtotal, months);

                        // Update installment details in the payment section
                        installmentDetails.innerHTML = `
                            <div class="small">
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Harga Produk:</span>
                                    <span>Rp${subtotal.toLocaleString('id-ID')}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Biaya Admin (2%):</span>
                                    <span>Rp${result.adminFee.toLocaleString('id-ID')}</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span>Cicilan per Bulan:</span>
                                    <span>Rp${result.monthlyPayment.toLocaleString('id-ID')}</span>
                                </div>
                                <div class="d-flex justify-content-between fw-bold">
                                    <span>Total Pembayaran:</span>
                                    <span>Rp${result.totalPrice.toLocaleString('id-ID')}</span>
                                </div>
                            </div>
                        `;
                        installmentDetails.style.display = 'block';

                        // Update summary section with installment details
                        document.getElementById('installmentPeriod').textContent = `${months} Bulan`;
                        document.getElementById('installmentAdminFee').textContent = `Rp${result.adminFee.toLocaleString('id-ID')}`;
                        document.getElementById('monthlyPayment').textContent = `Rp${result.monthlyPayment.toLocaleString('id-ID')}`;
                        document.getElementById('installmentTotal').textContent = `Rp${result.totalPrice.toLocaleString('id-ID')}`;
                        document.getElementById('totalAmount').textContent = `Rp${result.totalPrice.toLocaleString('id-ID')}`;

                        installmentSummary.style.display = 'block';
                        totalNote.style.display = 'block';
                    }
                });
            });
        }

        function placeOrder() {
            const checkoutItems = JSON.parse(localStorage.getItem('checkoutItems')) || [];
            const items = checkoutItems.length > 0 ? checkoutItems : (JSON.parse(localStorage.getItem('cart')) || []);
            const quantity = parseInt(document.getElementById('totalProduct').value) || 1;

            if (items.length === 0) {
                alert('Tidak ada produk yang dipilih');
                return;
            }

            // Update quantity for each item
            items.forEach(item => {
                item.quantity = quantity;
            });

            const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
            const note = document.querySelector('.note-input').value;

            const formData = new FormData();
            formData.append('items', JSON.stringify(items));
            formData.append('payment_method', paymentMethod);
            formData.append('note', note);

            // Submit form
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'checkout.php';

            for (const [key, value] of formData.entries()) {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = key;
                input.value = value;
                form.appendChild(input);
            }

            document.body.appendChild(form);
            form.submit();
        }
    </script>
</body>

</html>