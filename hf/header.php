<?php include 'hf/style.php'; ?>
<link href="style.css" rel="stylesheet">
<style>
    .sticky-header {
        position: sticky;
        top: 0;
        z-index: 1000;
        background: white;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .sticky-header .top-header {
        padding: 10px 0;
    }

    .sticky-header .bottom-header {
        background: #f8f9fa;
        padding: 5px 0;
    }

    .sticky-header hr {
        margin: 0;
    }
</style>

<div class="sticky-header">
    <!-- Header Atas -->
    <div class="top-header d-flex justify-content-between px-4 align-items-center">
        <div href="#" class="logo"><img src="image/logo.png"></div>
        <div class="search-container w-100 d-flex justify-content-center">
            <div class="col-md-6">
                <form action="search.php" method="GET" class="d-flex">
                    <input class="form-control me-2" type="search" name="search" placeholder="Cari produk..." aria-label="Search">
                    <button class="btn btn-outline-dark" type="submit" style="padding: 0; width: 40px; height: 38px; display: flex; align-items: center; justify-content: center; border: none;">
                        <img src="image/search.png" alt="Search" style="width: 24px; height: 24px;">
                    </button>
                </form>
            </div>
        </div>
        <div class="d-flex align-items-center icon-links">
            <a href="cart.php" class="me-3 position-relative">
                <img src="image/shopping-cart.png" alt="Keranjang">
                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger cart-counter">
                    0
                </span>
            </a>
            <a href="account.php"><img src="image/user-square.png" alt="Akun"></a>
        </div>
    </div>

    <nav class="bottom-header navbar navbar-expand-lg">
        <div class="container-fluid px-4">
            <div class="dropdown">
                <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    Kategori
                </button>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="#">Preloved</a></li>
                    <li><a class="dropdown-item" href="#">Like New</a></li>
                    <li><a class="dropdown-item" href="#">Second</a></li>
                    <li><a class="dropdown-item" href="#">Minus</a></li>
                </ul>
            </div>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="ada.php">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="live.php">Live</a></li>
                    <li class="nav-item"><a class="nav-link" href="trending.php">Trending</a></li>
                </ul>
                <a href="https://wa.me/+6285742708990" class="ms-auto support-icon"><img src="image/support.png" alt="Support"></a>
            </div>
        </div>
    </nav>
    <hr style="border: 2px grey; width: 80%; margin: 0 auto; opacity: 1;">
</div>

<script>
    // Update cart counter
    function updateCartCounter() {
        const cartItems = JSON.parse(localStorage.getItem('cart')) || [];
        const counter = document.querySelector('.cart-counter');
        if (counter) {
            counter.textContent = cartItems.length;
            counter.style.display = cartItems.length > 0 ? 'block' : 'none';
        }
    }

    // Update counter when page loads
    document.addEventListener('DOMContentLoaded', updateCartCounter);

    // Listen for cart updates
    window.addEventListener('storage', function(e) {
        if (e.key === 'cart') {
            updateCartCounter();
        }
    });

    // Update counter every 5 seconds to ensure consistency
    setInterval(updateCartCounter, 5000);
</script>