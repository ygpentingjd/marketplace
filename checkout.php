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
                                <div class="address-name">Chris Setiawan(+62) 85784912876</div>
                                <p class="address-text">Jl Melati, Tunggul RT01/RW02, Kali Bagor, Wonogiri, Banyumas, Jawa Tengah, ID 45981</p>
                            </div>
                            <a href="#" class="btn-edit">Ubah</a>
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
                    <textarea class="note-input mb-3" placeholder="Catatan untuk penjual (opsional)"></textarea>
                    <a href="orders.php"><button class="btn-order" onclick="placeOrder()">Buat Pesanan</button></a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'hf/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/credit.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            loadCheckoutItems();
            setupDropshipToggle();
            setupPaymentMethods();
            updateTotals();
        });

        function loadCheckoutItems() {
            // Get items from either checkoutItems or cart
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

            // Group items by store
            const itemsByStore = {};
            items.forEach(item => {
                const store = item.store || 'Preloved By Ocaa';
                if (!itemsByStore[store]) {
                    itemsByStore[store] = [];
                }
                itemsByStore[store].push(item);
            });

            let productHTML = '';

            // Render items grouped by store
            for (const [store, storeItems] of Object.entries(itemsByStore)) {
                productHTML += `
                    <div class="store-section mb-4">
                        <div class="store-name mb-3">
                            <i class="fas fa-store me-2"></i>${store}
                        </div>
                `;

                storeItems.forEach(item => {
                    const price = item.paymentMethod === 'installment' && item.installment ?
                        item.installment.totalPrice : item.price;

                    productHTML += `
                        <div class="cart-item">
                            <img src="${item.type === 'tv' ? 'image/tv.png' : 
                                     item.type === 'sepatu' ? 'image/sepatu.png' : 
                                     item.type === 'knalpot' ? 'image/knalpot.png' : 
                                     'image/default.png'}" 
                                 class="product-image" 
                                 alt="${item.name}">
                            <div class="product-info">
                                <div class="product-name">${item.name}</div>
                                ${item.size ? `<div class="product-size">Size: ${item.size}</div>` : ''}
                                ${item.color ? `<div class="product-size">Color: ${item.color}</div>` : ''}
                                <div class="product-price">Rp${price.toLocaleString('id-ID')}</div>
                            </div>
                        </div>
                    `;
                });

                productHTML += `</div>`;
            }

            productList.innerHTML = productHTML;
            updateTotals();
        }

        function updateTotals() {
            // Get items from either checkoutItems or cart
            const checkoutItems = JSON.parse(localStorage.getItem('checkoutItems')) || [];
            const items = checkoutItems.length > 0 ? checkoutItems : (JSON.parse(localStorage.getItem('cart')) || []);
            let subtotal = 0;

            items.forEach(item => {
                const price = item.paymentMethod === 'installment' && item.installment ?
                    item.installment.totalPrice : item.price;
                subtotal += price;
            });

            const shippingCost = 15000;
            const serviceFee = Math.round(subtotal * 0.02); // 2% service fee
            const total = subtotal + shippingCost + serviceFee;

            document.getElementById('subtotal').textContent = `Rp${subtotal.toLocaleString('id-ID')}`;
            document.getElementById('shippingCost').textContent = `Rp${shippingCost.toLocaleString('id-ID')}`;
            document.getElementById('serviceFee').textContent = `Rp${serviceFee.toLocaleString('id-ID')}`;

            // Reset installment display
            document.getElementById('installmentSummary').style.display = 'none';
            document.getElementById('totalNote').style.display = 'none';
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
            const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
            const items = checkoutItems.length > 0 ? checkoutItems : cartItems;

            if (items.length === 0) {
                alert('Tidak ada produk yang dipilih');
                window.location.href = 'cart.php';
                return;
            }

            // Get form data
            const note = document.querySelector('.note-input').value.trim();
            const paymentMethod = document.querySelector('input[name="paymentMethod"]:checked').value;
            const isDropship = document.getElementById('dropshipCheck').checked;

            // Validate payment method selection
            if (paymentMethod === 'installment') {
                const installmentPeriod = document.querySelector('input[name="installmentPeriod"]:checked');
                if (!installmentPeriod) {
                    alert('Silakan pilih periode cicilan');
                    return;
                }
            } else if (paymentMethod === 'ewallet') {
                const ewalletType = document.querySelector('input[name="ewalletType"]:checked');
                if (!ewalletType) {
                    alert('Silakan pilih jenis e-wallet');
                    return;
                }
            }

            let dropshipData = null;
            if (isDropship) {
                const dropshipName = document.querySelector('input[placeholder="Nama"]').value.trim();
                const dropshipPhone = document.querySelector('input[placeholder="Nomor Telepon"]').value.trim();

                if (!dropshipName || !dropshipPhone) {
                    alert('Mohon lengkapi data dropshipper');
                    return;
                }

                dropshipData = {
                    name: dropshipName,
                    phone: dropshipPhone
                };
            }

            // Get payment details
            let paymentDetails = {
                method: paymentMethod,
                total: parseFloat(document.getElementById('totalAmount').textContent.replace(/[^0-9]/g, '')),
                shipping: 15000,
                serviceFee: parseFloat(document.getElementById('serviceFee').textContent.replace(/[^0-9]/g, ''))
            };

            // Add specific payment method details
            if (paymentMethod === 'installment') {
                const months = parseInt(document.querySelector('input[name="installmentPeriod"]:checked').value);
                const subtotal = parseFloat(document.getElementById('subtotal').textContent.replace(/[^0-9]/g, ''));
                const installmentCalc = calculateInstallment(subtotal, months);
                paymentDetails.installment = {
                    months: months,
                    monthlyPayment: installmentCalc.monthlyPayment,
                    adminFee: installmentCalc.adminFee,
                    totalPrice: installmentCalc.totalPrice
                };
            } else if (paymentMethod === 'ewallet') {
                paymentDetails.ewalletType = document.querySelector('input[name="ewalletType"]:checked').value;
            }

            // Create order data
            const orderData = {
                items: items,
                payment: paymentDetails,
                note: note,
                dropship: dropshipData,
                orderDate: new Date().toISOString()
            };

            // Save order data
            const orders = JSON.parse(localStorage.getItem('orders')) || [];
            orders.push(orderData);
            localStorage.setItem('orders', JSON.stringify(orders));

            // Clear cart or checkout items
            if (checkoutItems.length > 0) {
                localStorage.removeItem('checkoutItems');
            } else {
                localStorage.removeItem('cart');
            }

            // Show success message and redirect
            alert('Pesanan berhasil dibuat!');
            window.location.href = 'index.php';
        }
    </script>
</body>

</html>