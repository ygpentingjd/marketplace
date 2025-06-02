<?php
include 'koneksi.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch product details
$product = null;
if ($product_id > 0) {
    $stmt = $conn->prepare("SELECT * FROM products WHERE id_produk = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
}

if (!$product) {
    die("Product not found.");
}

// Get category name
$category_query = "SELECT nama_kategori FROM categories WHERE id_kategori = ?";
$stmt = $conn->prepare($category_query);
$stmt->bind_param("i", $product['id_kategori']);
$stmt->execute();
$category_result = $stmt->get_result();
$category_name = $category_result->fetch_assoc()['nama_kategori'] ?? 'Unknown';

// Redirect to appropriate template based on category
switch ($product['id_kategori']) {
    case 1: // Fashion
        include 'detailsepatu.php';
        break;
    case 2: // Otomotif
        include 'detailknalpot.php';
        break;
    case 3: // Elektronik
        include 'detailtv.php';
        break;
    default:
        // Default template for unknown categories
        include 'detailtv.php';
        break;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($product['nama_produk']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .product-image {
            max-width: 100%;
            height: auto;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-6">
                <img src="<?php echo $product['gambar']; ?>"
                    alt="<?php echo htmlspecialchars($product['nama_produk']); ?>"
                    class="product-image">
            </div>
            <div class="col-md-6">
                <h1><?php echo htmlspecialchars($product['nama_produk']); ?></h1>
                <p><strong>Price:</strong> Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></p>
                <p><strong>Kategori:</strong> <?php echo htmlspecialchars($category_name); ?></p>
                <p><strong>Kondisi:</strong> <?php echo htmlspecialchars(ucfirst($product['kondisi'])); ?></p>
                <p><strong>Stok:</strong> <?php echo $product['stok'] > 0 ? $product['stok'] . ' unit' : 'Habis'; ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($product['deskripsi'] ?? 'No description available.'); ?></p>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>