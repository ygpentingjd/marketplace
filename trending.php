<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include header and database connection
include 'hf/header.php';
include 'koneksi.php';

// Get category filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : 'all';

// Base query for verified products
$query = "SELECT * FROM products WHERE verification_status = 'terverifikasi'";

// Add category filter if specified
if ($category_filter !== 'all') {
    $query .= " AND id_kategori = ?";
}

// Order by most recent
$query .= " ORDER BY id_produk DESC";

// Prepare and execute query
$stmt = $conn->prepare($query);
if ($category_filter !== 'all') {
    $stmt->bind_param("s", $category_filter);
}
$stmt->execute();
$result = $stmt->get_result();

$products = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}

// Get unique categories for filter
$categories_query = "SELECT DISTINCT id_kategori FROM products WHERE verification_status = 'terverifikasi' ORDER BY id_kategori";
$categories_result = $conn->query($categories_query);
$categories = [];
if ($categories_result) {
    while ($row = $categories_result->fetch_assoc()) {
        $categories[] = $row['id_kategori'];
    }
}

// Add admin dashboard link if user is admin
$is_admin = isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
?>

<style>
    .container {
        width: 90%;
        margin: auto;
    }

    .section {
        background: #F5F5F5;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 10px;
    }

    .section h2 {
        font-size: 24px;
        font-weight: bold;
        margin-bottom: 20px;
        color: #333;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .admin-link {
        font-size: 14px;
        padding: 5px 10px;
        background: #007bff;
        color: white;
        text-decoration: none;
        border-radius: 5px;
        transition: background 0.3s ease;
    }

    .admin-link:hover {
        background: #0056b3;
        color: white;
    }

    .category-filter {
        margin-bottom: 20px;
        padding: 10px;
        background: #fff;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .category-filter a {
        display: inline-block;
        padding: 5px 15px;
        margin: 0 5px 5px 0;
        background: #f8f9fa;
        border-radius: 20px;
        color: #333;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .category-filter a:hover,
    .category-filter a.active {
        background: #007bff;
        color: #fff;
    }

    .products {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
        gap: 20px;
        padding: 10px;
    }

    .product-card {
        background: white;
        padding: 15px;
        border-radius: 10px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
        text-align: center;
    }

    .product-card:hover {
        transform: translateY(-5px);
    }

    .product-card img {
        width: 100%;
        height: 200px;
        object-fit: cover;
        border-radius: 8px;
        margin-bottom: 10px;
    }

    .product-card h3 {
        font-size: 16px;
        margin: 10px 0;
        color: #333;
    }

    .product-card .price {
        font-weight: bold;
        color: #007bff;
        margin: 5px 0;
    }

    .product-card .tag {
        display: inline-block;
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 12px;
        margin-top: 5px;
    }

    .tag.available {
        background: #28a745;
        color: white;
    }

    .tag.sold-out {
        background: #dc3545;
        color: white;
    }

    .no-products {
        text-align: center;
        padding: 40px;
        color: #666;
        font-size: 18px;
    }
</style>

<div class="container">
    <div class="section">
        <h2>
            Trending Products
            <?php if ($is_admin): ?>
                <a href="admin/index.php" class="admin-link">Admin Dashboard</a>
            <?php endif; ?>
        </h2>

        <!-- Category Filter -->
        <div class="category-filter">
            <a href="?category=all" class="<?php echo $category_filter === 'all' ? 'active' : ''; ?>">All</a>
            <?php foreach ($categories as $category): ?>
                <a href="?category=<?php echo urlencode($category); ?>"
                    class="<?php echo $category_filter === $category ? 'active' : ''; ?>">
                    <?php echo htmlspecialchars($category); ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Products Grid -->
        <div class="products">
            <?php if (empty($products)): ?>
                <div class="no-products">
                    No products found in this category.
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-card">
                        <a href="detail.php?id=<?php echo $product['id_produk']; ?>">
                            <img src="<?php echo $product['gambar']; ?>"
                                alt="<?php echo htmlspecialchars($product['nama_produk']); ?>">
                        </a>
                        <h3><?php echo htmlspecialchars($product['nama_produk']); ?></h3>
                        <div class="price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></div>
                        <span class="tag <?php echo $product['stok'] > 0 ? 'available' : 'sold-out'; ?>">
                            <?php echo $product['stok'] > 0 ? 'Tersedia' : 'Habis'; ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'hf/footer.php'; ?>