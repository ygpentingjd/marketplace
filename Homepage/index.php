<?php
include 'koneksi.php';

// Fetch trending products (most viewed or most recent verified products)
$trending_query = "SELECT * FROM products 
                  WHERE verification_status = 'terverifikasi' 
                  ORDER BY id_produk DESC 
                  LIMIT 3";
$trending_result = $conn->query($trending_query);
$trending_products = [];
if ($trending_result) {
    while ($row = $trending_result->fetch_assoc()) {
        $trending_products[] = $row;
    }
}

// Fetch all verified products for explore section
$explore_query = "SELECT * FROM products 
                 WHERE verification_status = 'terverifikasi' 
                 ORDER BY id_produk DESC";
$explore_result = $conn->query($explore_query);
$explore_products = [];
if ($explore_result) {
    while ($row = $explore_result->fetch_assoc()) {
        $explore_products[] = $row;
    }
}
?>

<link href="Homepage/styles.css" rel="stylesheet">
<!-- Paling Sering Dicari -->
<div class="container">
    <h2 class="pb-2 border-bottom">Paling Sering Dicari</h2>
    <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            <?php foreach ($trending_products as $index => $product): ?>
                <div class="carousel-item <?php echo $index === 0 ? 'active' : ''; ?>">
                    <a href="detail.php?id=<?php echo $product['id_produk']; ?>">
                        <img src="<?php echo $product['gambar']; ?>" class="d-block w-100" alt="<?php echo htmlspecialchars($product['nama_produk']); ?>">
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Previous</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Next</span>
        </button>
    </div>

    <hr style="border: 2px grey; width: 80%; margin: 40px auto; opacity: 1;">

    <!-- Explore -->
    <div class="container mt-4">
        <h2 class="pb-2 border-bottom"><img src="image/explore.png"></h2>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
            <?php foreach ($explore_products as $product): ?>
                <div class="col">
                    <div class="product-card">
                        <a href="detail.php?id=<?php echo $product['id_produk']; ?>">
                            <img src="<?php echo $product['gambar']; ?>" alt="<?php echo htmlspecialchars($product['nama_produk']); ?>">
                        </a>
                        <div class="product-title"><?php echo htmlspecialchars($product['nama_produk']); ?></div>
                        <div class="price">Rp <?php echo number_format($product['harga'], 0, ',', '.'); ?></div>
                        <?php if ($product['stok'] > 0): ?>
                            <span class="tag">Tersedia</span>
                        <?php else: ?>
                            <span class="tag" style="background-color: #dc3545;">Habis</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>