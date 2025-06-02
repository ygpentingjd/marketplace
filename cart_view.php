<?php
// cart_view.php
session_start();
include 'koneksi.php';

$id_user = $_SESSION['user_id'] ?? 1;

// Ambil isi keranjang user
$stmt = $conn->prepare("SELECT c.id_cart, c.qty, p.nama_produk, p.harga, p.gambar FROM cart c JOIN products p ON c.id_produk = p.id_produk WHERE c.id_user = ? ORDER BY c.id_cart DESC");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$result = $stmt->get_result();
$cart_items = [];
while ($row = $result->fetch_assoc()) {
    $cart_items[] = $row;
}

// Hapus item dari keranjang jika ada request
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $id_cart = (int)$_GET['delete'];
    $del = $conn->prepare("DELETE FROM cart WHERE id_cart = ? AND id_user = ?");
    $del->bind_param("ii", $id_cart, $id_user);
    $del->execute();
    header("Location: cart_view.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Keranjang Belanja</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .cart-table img {
            max-width: 80px;
            height: auto;
        }

        .cart-table th,
        .cart-table td {
            vertical-align: middle;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h2>Keranjang Belanja</h2>
        <?php if (count($cart_items) === 0): ?>
            <div class="alert alert-info">Keranjang Anda kosong.</div>
        <?php else: ?>
            <table class="table cart-table table-bordered align-middle mt-4">
                <thead class="table-light">
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Harga</th>
                        <th>Qty</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $grand_total = 0; ?>
                    <?php foreach ($cart_items as $item): ?>
                        <?php $total = $item['harga'] * $item['qty'];
                        $grand_total += $total; ?>
                        <tr>
                            <td><img src="<?php echo $item['gambar']; ?>" alt="<?php echo htmlspecialchars($item['nama_produk']); ?>"></td>
                            <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                            <td>Rp <?php echo number_format($item['harga'], 0, ',', '.'); ?></td>
                            <td><?php echo $item['qty']; ?></td>
                            <td>Rp <?php echo number_format($total, 0, ',', '.'); ?></td>
                            <td>
                                <a href="cart_view.php?delete=<?php echo $item['id_cart']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus produk ini dari keranjang?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Grand Total</th>
                        <th colspan="2">Rp <?php echo number_format($grand_total, 0, ',', '.'); ?></th>
                    </tr>
                </tfoot>
            </table>
            <form method="POST" action="checkout.php">
                <input type="hidden" name="id_produk" value="<?php echo $cart_items[0]['id_cart']; ?>">
                <input type="hidden" name="qty" value="<?php echo $cart_items[0]['qty']; ?>">
                <button type="submit" class="btn btn-success">Checkout</button>
            </form>
        <?php endif; ?>
        <a href="index.php" class="btn btn-link mt-3">&larr; Kembali ke Home</a>
    </div>
</body>

</html>