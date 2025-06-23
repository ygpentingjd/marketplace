<?php
session_start();
include 'koneksi.php';

// Cek jika user belum login
if (!isset($_SESSION['id_user'])) {
    $_SESSION['redirect_url'] = 'orders.php';
    header("Location: login2.php?error=login_required&message=Silakan+login+terlebih+dahulu");
    exit();
}

$user_id = $_SESSION['id_user'];

// Fungsi untuk mendapatkan teks status
function getStatusText($status)
{
    $statusMap = array(
        'menunggu-pembayaran' => 'Menunggu Pembayaran',
        'menunggu-dikemas' => 'Menunggu Dikemas',
        'diproses' => 'Diproses',
        'dikirim' => 'Dikirim',
        'selesai' => 'Selesai',
        'dibatalkan' => 'Dibatalkan'
    );
    return isset($statusMap[$status]) ? $statusMap[$status] : $status;
}

// Cek koneksi database
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Get all orders for the user with their details
$query = "SELECT o.*, 
          GROUP_CONCAT(p.nama_produk SEPARATOR ', ') as products,
          GROUP_CONCAT(p.gambar SEPARATOR ', ') as product_images,
          GROUP_CONCAT(od.jumlah SEPARATOR ', ') as quantities,
          GROUP_CONCAT(od.subtotal SEPARATOR ', ') as subtotals,
          py.status_pembayaran, py.metode as metode_pembayaran
          FROM orders o 
          JOIN order_details od ON o.id_order = od.id_order 
          JOIN products p ON od.id_produk = p.id_produk 
          LEFT JOIN payment py ON o.id_order = py.id_order
          WHERE o.id_user = ? 
          GROUP BY o.id_order 
          ORDER BY o.tanggal_pemesanan DESC";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$orders = [];
while ($row = $result->fetch_assoc()) {
    $order_id = $row['id_order'];
    if (!isset($orders[$order_id])) {
        $orders[$order_id] = array(
            'id_order' => $order_id,
            'tanggal_pemesanan' => $row['tanggal_pemesanan'],
            'status_pesanan' => $row['status_pesanan'],
            'total_harga' => $row['total_harga'],
            'metode_pembayaran' => $row['metode_pembayaran'],
            'status_pembayaran' => $row['status_pembayaran'],
            'products' => explode(', ', $row['products']),
            'product_images' => explode(', ', $row['product_images']),
            'quantities' => explode(', ', $row['quantities']),
            'subtotals' => explode(', ', $row['subtotals'])
        );
    }
}

// Handle payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['payment_method'])) {
    $order_id = $_POST['order_id'];
    $payment_method = $_POST['payment_method'];

    // Update payment information
    $payment_query = "INSERT INTO payment (id_order, metode, status_pembayaran) 
                     VALUES (?, ?, 'pending')
                     ON DUPLICATE KEY UPDATE 
                     metode = VALUES(metode),
                     status_pembayaran = VALUES(status_pembayaran)";
    $payment_stmt = $conn->prepare($payment_query);
    $payment_stmt->bind_param("is", $order_id, $payment_method);

    if ($payment_stmt->execute()) {
        $_SESSION['success_message'] = "Metode pembayaran berhasil diperbarui";
    } else {
        $_SESSION['error_message'] = "Gagal memperbarui metode pembayaran";
    }

    header("Location: orders.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #ffffff;
            font-family: Arial, sans-serif;
            min-width: 1200px;
        }

        .orders-container {
            width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            font-size: 24px;
            font-weight: 500;
            margin-bottom: 20px;
            color: #333;
        }

        .nav-tabs {
            border: none;
            margin-bottom: 20px;
        }

        .nav-tabs .nav-link {
            border: none;
            color: #666;
            padding: 10px 20px;
            margin-right: 10px;
            border-radius: 20px;
        }

        .nav-tabs .nav-link.active {
            background-color: #000;
            color: white;
        }

        .order-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }

        .order-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .store-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .store-name {
            font-weight: 500;
            color: #333;
        }

        .order-status {
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 14px;
            font-weight: 500;
        }

        .status-diproses {
            background-color: #e3f2fd;
            color: #1976d2;
        }

        .status-dikirim {
            background-color: #fff3e0;
            color: #f57c00;
        }

        .status-selesai {
            background-color: #e8f5e9;
            color: #388e3c;
        }

        .status-dibatalkan {
            background-color: #ffebee;
            color: #d32f2f;
        }

        .order-items {
            padding: 15px 0;
        }

        .order-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 10px 0;
        }

        .item-image {
            width: 80px;
            height: 80px;
            object-fit: contain;
        }

        .item-details {
            flex: 1;
        }

        .item-name {
            font-size: 14px;
            color: #333;
            margin-bottom: 4px;
        }

        .item-variant {
            font-size: 12px;
            color: #666;
        }

        .item-price {
            font-size: 14px;
            font-weight: 500;
            color: #333;
        }

        .order-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .total-section {
            text-align: right;
        }

        .total-label {
            font-size: 12px;
            color: #666;
        }

        .total-amount {
            font-size: 16px;
            font-weight: 500;
            color: #333;
        }

        .action-buttons {
            display: flex;
            gap: 10px;
        }

        .btn-action {
            padding: 8px 16px;
            border-radius: 4px;
            font-size: 14px;
            cursor: pointer;
        }

        .btn-primary-outline {
            border: 1px solid #000;
            background: white;
            color: #000;
        }

        .btn-primary-outline:hover {
            background: #f8f9fa;
        }

        .btn-primary-solid {
            border: none;
            background: #000;
            color: white;
        }

        .btn-primary-solid:hover {
            background: #333;
        }

        .btn-danger-outline {
            border: 1px solid #dc3545;
            background: white;
            color: #dc3545;
        }

        .btn-danger-outline:hover {
            background: #fff5f5;
        }

        /* Modal styles */
        .review-modal,
        .details-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgb(251, 250, 250);
            z-index: 1000;
        }

        .modal-content {
            background: #ffffff;
            width: 600px;
            margin: 50px auto;
            padding: 20px;
            border-radius: 8px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .modal-title {
            font-size: 20px;
            font-weight: 500;
            color: #333;
        }

        .close-button {
            background: none;
            border: none;
            font-size: 24px;
            color: #666;
            cursor: pointer;
        }

        .close-button:hover {
            color: #333;
        }

        .order-info-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #ffffff;
            border-radius: 8px;
            border: 1px solid #eee;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
        }

        .order-info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }

        .order-info-label {
            color: #666;
        }

        .order-info-value {
            color: #333;
            font-weight: 500;
        }

        .order-timeline {
            margin: 30px 0;
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            padding-bottom: 20px;
        }

        .timeline-item:before {
            content: '';
            position: absolute;
            left: -30px;
            top: 0;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            background: #000;
        }

        .timeline-item:after {
            content: '';
            position: absolute;
            left: -25px;
            top: 12px;
            width: 2px;
            height: calc(100% - 12px);
            background: #ddd;
        }

        .timeline-item:last-child:after {
            display: none;
        }

        .timeline-date {
            font-size: 12px;
            color: #666;
            margin-bottom: 4px;
        }

        .timeline-status {
            font-weight: 500;
            color: #333;
        }

        .star-rating {
            display: flex;
            gap: 5px;
            margin: 15px 0;
        }

        .star {
            font-size: 24px;
            color: #ddd;
            cursor: pointer;
        }

        .star.active {
            color: #ffc107;
        }

        .review-textarea {
            width: 100%;
            height: 100px;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-bottom: 15px;
            resize: none;
        }

        /* Confirmation Modal */
        .confirmation-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1100;
        }

        .confirmation-content {
            background: #ffffff;
            width: 400px;
            margin: 100px auto;
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .confirmation-title {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 15px;
            color: #333;
        }

        .confirmation-message {
            font-size: 14px;
            color: #666;
            margin-bottom: 20px;
        }

        .back-button {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border: none;
            background: none;
            color: #333;
            font-size: 16px;
            cursor: pointer;
            margin-bottom: 20px;
        }

        .back-button:hover {
            color: #000;
        }

        .back-button i {
            font-size: 18px;
        }
    </style>
</head>

<body>
    <?php include 'hf/header.php'; ?>

    <div class="orders-container">
        <button class="back-button" onclick="window.location.href='ada.php'">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </button>

        <h1 class="page-title">Pesanan Saya</h1>

        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link active" href="#" onclick="filterOrders('semua')">Semua</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="filterOrders('diproses')">Diproses</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="filterOrders('dikirim')">Dikirim</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="filterOrders('selesai')">Selesai</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="#" onclick="filterOrders('dibatalkan')">Dibatalkan</a>
            </li>
        </ul>

        <div id="ordersList">
            <?php if (empty($orders)): ?>
                <div class="text-center py-5">
                    <p class="mb-0">Belum ada pesanan</p>
                </div>
            <?php else: ?>
                <?php foreach ($orders as $order): ?>
                    <div class="card mb-3">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">Order #<?php echo $order['id_order']; ?></h5>
                                <small class="text-muted"><?php echo date('d/m/Y H:i', strtotime($order['tanggal_pemesanan'])); ?></small>
                            </div>
                            <div>
                                <span class="badge bg-<?php
                                                        echo match ($order['status_pesanan']) {
                                                            'diproses' => 'warning',
                                                            'dikirim' => 'info',
                                                            'selesai' => 'success',
                                                            'dibatalkan' => 'danger',
                                                            'refund_requested' => 'warning',
                                                            'refunded' => 'info',
                                                            default => 'secondary'
                                                        };
                                                        ?>">
                                    <?php echo ucfirst($order['status_pesanan']); ?>
                                </span>
                                <a href="detail_pesanan.php?id_order=<?php echo $order['id_order']; ?>" class="btn btn-sm btn-primary ms-2">
                                    <i class="fas fa-eye"></i> Detail
                                </a>
                                <?php if (in_array($order['status_pesanan'], ['selesai', 'dikirim']) && !in_array($order['status_pesanan'], ['refund_requested', 'refunded'])): ?>
                                    <a href="request_refund.php?order_id=<?php echo $order['id_order']; ?>" class="btn btn-sm btn-warning ms-2">
                                        <i class="fas fa-undo"></i> Request Refund
                                    </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="order-items">
                                <?php for ($i = 0; $i < count($order['products']); $i++): ?>
                                    <div class="order-item">
                                        <img src="<?php echo htmlspecialchars($order['product_images'][$i]); ?>"
                                            class="item-image"
                                            alt="<?php echo htmlspecialchars($order['products'][$i]); ?>"
                                            onerror="this.src='image/default.png';">
                                        <div class="item-details">
                                            <div class="item-name"><?php echo htmlspecialchars($order['products'][$i]); ?></div>
                                            <div class="item-price">
                                                Rp<?php echo number_format($order['subtotals'][$i], 0, ',', '.'); ?> × <?php echo $order['quantities'][$i]; ?>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <strong>Rp<?php echo number_format($order['subtotals'][$i], 0, ',', '.'); ?></strong>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            </div>
                            <div class="order-footer">
                                <div class="total-section">
                                    <div class="total-label">Total Belanja</div>
                                    <div class="total-amount">
                                        Rp<?php echo number_format($order['total_harga'], 0, ',', '.'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Review Modal -->
    <div id="reviewModal" class="review-modal">
        <div class="modal-content">
            <h3>Beri Penilaian</h3>
            <div class="star-rating" id="starRating">
                <span class="star" data-rating="1">★</span>
                <span class="star" data-rating="2">★</span>
                <span class="star" data-rating="3">★</span>
                <span class="star" data-rating="4">★</span>
                <span class="star" data-rating="5">★</span>
            </div>
            <textarea class="review-textarea" id="reviewText" placeholder="Tulis ulasan Anda di sini..."></textarea>
            <div class="action-buttons">
                <button class="btn-action btn-primary-outline" onclick="closeReviewModal()">Batal</button>
                <button class="btn-action btn-primary-solid" onclick="submitReview()">Kirim</button>
            </div>
        </div>
    </div>

    <!-- Order Details Modal -->
    <div id="orderDetailsModal" class="details-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Detail Pesanan</h3>
                <button class="close-button" onclick="closeOrderDetails()">&times;</button>
            </div>
            <div id="orderDetailsContent">
                <!-- Order details will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Confirmation Modal -->
    <div id="confirmationModal" class="confirmation-modal">
        <div class="confirmation-content">
            <h3 class="confirmation-title">Batalkan Pesanan</h3>
            <p class="confirmation-message">Apakah Anda yakin ingin membatalkan pesanan ini?</p>
            <div class="action-buttons">
                <button class="btn-action btn-primary-outline" onclick="closeConfirmationModal()">Tidak</button>
                <button class="btn-action btn-danger-outline" onclick="confirmCancelOrder()">Ya, Batalkan</button>
            </div>
        </div>
    </div>

    <?php include 'hf/footer.php'; ?>

    <script>
        let currentOrderId = null;
        let currentRating = 0;
        let orderToBeCancelled = null;

        document.addEventListener('DOMContentLoaded', function() {
            loadOrders('semua');
            setupStarRating();
        });

        function loadOrders(filter) {
            const orders = JSON.parse(localStorage.getItem('orders')) || [];
            const ordersList = document.getElementById('ordersList');

            if (orders.length === 0) {
                ordersList.innerHTML = `
                    <div class="text-center py-5">
                        <p class="mb-0">Belum ada pesanan</p>
                    </div>
                `;
                return;
            }

            let ordersHTML = '';
            orders.forEach((order, index) => {
                if (filter === 'semua' || order.status === filter) {
                    ordersHTML += createOrderCard(order, index);
                }
            });

            ordersList.innerHTML = ordersHTML;
        }

        function createOrderCard(order, index) {
            const totalAmount = order.payment.total;
            const status = order.status || 'dikemas';
            const date = new Date(order.orderDate).toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });

            return `
                <div class="order-card">
                    <div class="order-header">
                        <div class="store-info">
                            <i class="fas fa-store"></i>
                            <span class="store-name">${order.items[0].store}</span>
                        </div>
                        <span class="order-status status-${status}">${getStatusText(status)}</span>
                    </div>
                    <div class="order-items">
                        ${order.items.map(item => `
                            <div class="order-item">
                                <img src="${getItemImage(item)}" class="item-image" alt="${item.name}">
                                <div class="item-details">
                                    <div class="item-name">${item.name}</div>
                                    ${item.size ? `<div class="item-variant">Size: ${item.size}</div>` : ''}
                                    ${item.color ? `<div class="item-variant">Color: ${item.color}</div>` : ''}
                                    <div class="item-price">Rp${item.price.toLocaleString('id-ID')}</div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                    <div class="order-footer">
                        <div class="order-date">
                            <i class="far fa-calendar"></i>
                            ${date}
                        </div>
                        <div class="total-section">
                            <div class="total-label">Total Belanja</div>
                            <div class="total-amount">Rp${totalAmount.toLocaleString('id-ID')}</div>
                        </div>
                    </div>
                    <div class="action-buttons mt-3">
                        <button class="btn-action btn-primary-outline" onclick="showOrderDetails(${index})">
                            Detail Pesanan
                        </button>
                        ${status === 'selesai' && !order.reviewed ? `
                            <button class="btn-action btn-primary-solid" onclick="openReviewModal(${index})">
                                Beri Penilaian
                            </button>
                        ` : ''}
                        ${(status === 'belum-bayar' || status === 'dikemas') ? `
                            <button class="btn-action btn-danger-outline" onclick="openCancelConfirmation(${index})">
                                Batalkan Pesanan
                            </button>
                        ` : ''}
                    </div>
                </div>
            `;
        }

        function getItemImage(item) {
            return item.type === 'tv' ? 'image/tv.png' :
                item.type === 'sepatu' ? 'image/sepatu.png' :
                item.type === 'knalpot' ? 'image/knalpot.png' :
                'image/default.png';
        }

        function getStatusText(status) {
            const statusMap = {
                'belum-bayar': 'Belum Bayar',
                'dikemas': 'Dikemas',
                'dikirim': 'Dikirim',
                'selesai': 'Selesai',
                'dibatalkan': 'Dibatalkan'
            };
            return statusMap[status] || status;
        }

        function filterOrders(status) {
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            event.target.classList.add('active');
            loadOrders(status);
        }

        function showOrderDetails(index) {
            const orders = JSON.parse(localStorage.getItem('orders')) || [];
            const order = orders[index];
            if (!order) return;

            const orderDate = new Date(order.orderDate);
            const formattedDate = orderDate.toLocaleDateString('id-ID', {
                day: '2-digit',
                month: 'long',
                year: 'numeric'
            });

            const statusTimeline = getStatusTimeline(order.status);

            const detailsHTML = `
                <div class="order-info-section">
                    <div class="order-info-row">
                        <span class="order-info-label">Nomor Pesanan</span>
                        <span class="order-info-value">#${generateOrderNumber(index, orderDate)}</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Tanggal Pembelian</span>
                        <span class="order-info-value">${formattedDate}</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Status</span>
                        <span class="order-info-value">${getStatusText(order.status)}</span>
                    </div>
                </div>

                <div class="order-timeline">
                    ${statusTimeline.map(status => `
                        <div class="timeline-item">
                            <div class="timeline-date">${status.date}</div>
                            <div class="timeline-status">${status.text}</div>
                        </div>
                    `).join('')}
                </div>

                <div class="order-info-section">
                    <h4 class="mb-3">Detail Produk</h4>
                    ${order.items.map(item => `                        <div class="order-item">
                            <img src="${getItemImage(item)}" class="item-image" alt="${item.name}">
                            <div class="item-details">
                                <div class="item-name">${item.name}</div>
                                ${item.size ? `<div class="item-variant">Size: ${item.size}</div>` : ''}
                                ${item.color ? `<div class="item-variant">Color: ${item.color}</div>` : ''}
                                <div class="item-price">Rp${item.price.toLocaleString('id-ID')}</div>
                            </div>
                        </div>
                    `).join('')}
                </div>

                <div class="order-info-section">
                    <h4 class="mb-3">Informasi Pembayaran</h4>
                    <div class="order-info-row">
                        <span class="order-info-label">Metode Pembayaran</span>
                        <span class="order-info-value">${order.payment.method || 'Transfer Bank'}</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Total Harga Produk</span>
                        <span class="order-info-value">Rp${order.payment.total.toLocaleString('id-ID')}</span>
                    </div>
                    <div class="order-info-row">
                        <span class="order-info-label">Total Pembayaran</span>
                        <span class="order-info-value">Rp${order.payment.total.toLocaleString('id-ID')}</span>
                    </div>
                </div>

                ${(order.status === 'belum-bayar' || order.status === 'dikemas') ? `
                <div class="action-buttons mt-3">
                    <button class="btn-action btn-danger-outline" onclick="openCancelConfirmation(${index})">
                        Batalkan Pesanan
                    </button>
                </div>
                ` : ''}
            `;

            document.getElementById('orderDetailsContent').innerHTML = detailsHTML;
            document.getElementById('orderDetailsModal').style.display = 'block';
        }

        function closeOrderDetails() {
            document.getElementById('orderDetailsModal').style.display = 'none';
        }

        function generateOrderNumber(index, date) {
            const dateStr = date.toISOString().slice(2, 10).replace(/-/g, '');
            return `KO${dateStr}${String(index + 1).padStart(4, '0')}`;
        }

        function getStatusTimeline(currentStatus) {
            const now = new Date();
            const timeline = [];

            const statuses = [{
                    status: 'belum-bayar',
                    text: 'Menunggu Pembayaran'
                },
                {
                    status: 'dikemas',
                    text: 'Pesanan Dikemas'
                },
                {
                    status: 'dikirim',
                    text: 'Pesanan Dikirim'
                },
                {
                    status: 'selesai',
                    text: 'Pesanan Selesai'
                }
            ];

            let found = false;
            statuses.forEach(status => {
                if (currentStatus === 'dibatalkan' && !found) {
                    timeline.push({
                        date: now.toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        }),
                        text: 'Pesanan Dibatalkan'
                    });
                    found = true;
                    return;
                }

                if (status.status === currentStatus) {
                    found = true;
                }

                if (!found || status.status === currentStatus) {
                    timeline.push({
                        date: now.toLocaleDateString('id-ID', {
                            day: '2-digit',
                            month: 'long',
                            year: 'numeric',
                            hour: '2-digit',
                            minute: '2-digit'
                        }),
                        text: status.text
                    });
                }
            });

            return timeline;
        }

        function openReviewModal(orderId) {
            currentOrderId = orderId;
            document.getElementById('reviewModal').style.display = 'block';
            resetReviewForm();
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').style.display = 'none';
            resetReviewForm();
        }

        function setupStarRating() {
            const stars = document.querySelectorAll('.star');
            stars.forEach(star => {
                star.addEventListener('mouseover', function() {
                    const rating = this.dataset.rating;
                    highlightStars(rating);
                });

                star.addEventListener('click', function() {
                    currentRating = this.dataset.rating;
                    highlightStars(currentRating);
                });
            });

            document.getElementById('starRating').addEventListener('mouseleave', function() {
                highlightStars(currentRating);
            });
        }

        function highlightStars(rating) {
            document.querySelectorAll('.star').forEach(star => {
                star.classList.toggle('active', star.dataset.rating <= rating);
            });
        }

        function resetReviewForm() {
            currentRating = 0;
            highlightStars(0);
            document.getElementById('reviewText').value = '';
        }

        function submitReview() {
            if (currentRating === 0) {
                alert('Silakan beri rating bintang');
                return;
            }

            const reviewText = document.getElementById('reviewText').value.trim();
            if (!reviewText) {
                alert('Silakan tulis ulasan Anda');
                return;
            }

            const orders = JSON.parse(localStorage.getItem('orders')) || [];
            if (currentOrderId !== null && currentOrderId < orders.length) {
                orders[currentOrderId].reviewed = true;
                orders[currentOrderId].review = {
                    rating: currentRating,
                    text: reviewText,
                    date: new Date().toISOString()
                };
                localStorage.setItem('orders', JSON.stringify(orders));
            }

            closeReviewModal();
            loadOrders('semua');
        }

        function openCancelConfirmation(orderId) {
            if (confirm('Apakah Anda yakin ingin membatalkan pesanan ini?')) {
                cancelOrder(orderId);
            }
        }

        function cancelOrder(orderId) {
            const formData = new FormData();
            formData.append('order_id', orderId);

            fetch('cancel_order.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        alert('Pesanan berhasil dibatalkan');
                        // Reload halaman untuk memperbarui daftar pesanan
                        window.location.reload();
                    } else {
                        alert(data.message || 'Terjadi kesalahan saat membatalkan pesanan');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan saat membatalkan pesanan');
                });
        }

        function closeConfirmationModal() {
            document.getElementById('confirmationModal').style.display = 'none';
            orderToBeCancelled = null;
        }

        function confirmCancelOrder() {
            if (orderToBeCancelled === null) return;

            const orders = JSON.parse(localStorage.getItem('orders')) || [];
            if (orderToBeCancelled < orders.length) {
                orders[orderToBeCancelled].status = 'dibatalkan';
                orders[orderToBeCancelled].cancelDate = new Date().toISOString();
                localStorage.setItem('orders', JSON.stringify(orders));
            }

            closeConfirmationModal();
            closeOrderDetails();
            loadOrders('semua');
        }
    </script>
</body>

</html>