<?php
session_start();
include 'koneksi.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : 'all';

// Base query
$query = "SELECT p.*, c.nama_kategori, u.nama as nama_penjual 
          FROM products p 
          LEFT JOIN categories c ON p.id_kategori = c.id_kategori
          LEFT JOIN users u ON p.id_user = u.id_user
          WHERE (p.nama_produk LIKE ? OR p.deskripsi LIKE ?)";

// Add category filter if specified
if ($category !== 'all') {
    $query .= " AND p.id_kategori = ?";
}

$query .= " ORDER BY p.id_produk DESC";

// Prepare and execute the query
$stmt = $conn->prepare($query);

if ($category !== 'all') {
    $searchParam = "%$search%";
    $stmt->bind_param("ssi", $searchParam, $searchParam, $category);
} else {
    $searchParam = "%$search%";
    $stmt->bind_param("ss", $searchParam, $searchParam);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Pencarian - <?php echo htmlspecialchars($search); ?></title>
    <?php include 'hf/style.php'; ?>
    <style>
        .search-results {
            padding: 20px 0;
        }

        .search-header {
            margin-bottom: 20px;
        }

        .search-header h2 {
            font-size: 24px;
            margin-bottom: 10px;
        }

        .filter-section {
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .product-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .product-card {
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: transform 0.2s;
            text-decoration: none;
            color: inherit;
        }

        .product-card:hover {
            transform: translateY(-5px);
        }

        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }

        .product-info {
            padding: 15px;
        }

        .product-name {
            font-size: 16px;
            font-weight: 500;
            margin-bottom: 8px;
            color: #333;
        }

        .product-price {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .product-seller {
            font-size: 14px;
            color: #666;
        }

        .category-tag {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            margin-bottom: 8px;
        }

        .category-fashion { background: #fce4ec; color: #c2185b; }
        .category-otomotif { background: #fff3e0; color: #e65100; }
        .category-elektronik { background: #e3f2fd; color: #1976d2; }

        .no-results {
            text-align: center;
            padding: 40px;
            background: #f8f9fa;
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <?php include 'hf/header.php'; ?>

    <div class="container search-results">
        <div class="search-header">
            <h2>Hasil Pencarian: "<?php echo htmlspecialchars($search); ?>"</h2>
            <p>Ditemukan <?php echo $result->num_rows; ?> produk</p>
        </div>

        <div class="filter-section">
            <form action="search.php" method="GET" class="row g-3">
                <div class="col-md-6">
                    <input type="text" name="search" class="form-control" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari produk...">
                </div>
                <div class="col-md-4">
                    <select name="category" class="form-select">
                        <option value="all" <?php echo $category === 'all' ? 'selected' : ''; ?>>Semua Kategori</option>
                        <option value="1" <?php echo $category === '1' ? 'selected' : ''; ?>>Fashion</option>
                        <option value="2" <?php echo $category === '2' ? 'selected' : ''; ?>>Otomotif</option>
                        <option value="3" <?php echo $category === '3' ? 'selected' : ''; ?>>Elektronik</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="product-grid">
                <?php while ($row = $result->fetch_assoc()): ?>
                    <a href="detail.php?id=<?php echo $row['id_produk']; ?>" class="product-card">
                        <img src="<?php echo $row['gambar']; ?>" 
                             alt="<?php echo htmlspecialchars($row['nama_produk']); ?>" 
                             class="product-image"
                             onerror="this.src='image/default.png';">
                        <div class="product-info">
                            <?php
                            $categoryClass = '';
                            switch($row['id_kategori']) {
                                case 1: $categoryClass = 'category-fashion'; break;
                                case 2: $categoryClass = 'category-otomotif'; break;
                                case 3: $categoryClass = 'category-elektronik'; break;
                            }
                            ?>
                            <span class="category-tag <?php echo $categoryClass; ?>">
                                <?php echo htmlspecialchars($row['nama_kategori']); ?>
                            </span>
                            <h3 class="product-name"><?php echo htmlspecialchars($row['nama_produk']); ?></h3>
                            <div class="product-price">Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></div>
                            <div class="product-seller"><?php echo htmlspecialchars($row['nama_penjual']); ?></div>
                        </div>
                    </a>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="no-results">
                <h3>Tidak ada produk yang ditemukan</h3>
                <p>Coba kata kunci lain atau ubah filter pencarian Anda</p>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'hf/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 