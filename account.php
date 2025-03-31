<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Account - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .account-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .profile-section {
            background: #fff;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 30px;
        }

        .profile-picture {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: #f0f0f0;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 20px;
        }

        .profile-picture i {
            font-size: 40px;
            color: #666;
        }

        .menu-item {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
            display: flex;
            align-items: center;
            text-decoration: none;
            color: inherit;
        }

        .menu-item:hover {
            background: #f8f9fa;
        }

        .menu-item i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }

        .menu-item.logout {
            color: #dc3545;
        }

        .menu-item.logout:hover {
            background: #dc3545;
            color: white;
        }

        .auth-section {
            text-align: center;
            padding: 40px 20px;
        }

        .auth-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            margin-top: 20px;
        }

        .auth-button {
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .login-button {
            background-color: #007bff;
            color: white;
        }

        .login-button:hover {
            background-color: #0056b3;
            color: white;
        }

        .register-button {
            background-color: #28a745;
            color: white;
        }

        .register-button:hover {
            background-color: #218838;
            color: white;
        }

        .user-info {
            margin-top: 10px;
        }

        .user-info p {
            margin: 5px 0;
            color: #666;
        }
    </style>
</head>

<body>
    <?php include 'hf/header.php'; ?>

    <div class="account-container">
        <div class="profile-section">
            <?php if (isset($_SESSION['id_user'])): ?>
                <div class="profile-header">
                    <div class="profile-picture">
                        <i class="fas fa-user"></i>
                    </div>
                    <div>
                        <h2><?php echo htmlspecialchars($_SESSION['nama'] ?? 'User'); ?></h2>
                        <div class="user-info">
                            <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($_SESSION['email'] ?? ''); ?></p>
                            <p><i class="fas fa-calendar"></i> Member since <?php echo date('F Y'); ?></p>
                        </div>
                    </div>
                </div>

                <div class="menu-items">
                    <a href="orders.php" class="menu-item">
                        <i class="fas fa-history"></i>
                        <span>Riwayat Pembelian</span>
                    </a>
                    <a href="upload.php" class="menu-item">
                        <i class="fas fa-upload"></i>
                        <span>Upload Barang</span>
                    </a>
                    <a href="reviews.php" class="menu-item">
                        <i class="fas fa-star"></i>
                        <span>Beri Review</span>
                    </a>
                    <a href="settings.php" class="menu-item">
                        <i class="fas fa-cog"></i>
                        <span>Pengaturan</span>
                    </a>
                    <a href="logout.php" class="menu-item logout">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Logout</span>
                    </a>
                </div>
            <?php else: ?>
                <div class="auth-section">
                    <h2>Selamat Datang di K.O</h2>
                    <p class="text-muted">Silakan login atau daftar untuk mengakses akun Anda</p>
                    <div class="auth-buttons">
                        <a href="login2.php" class="auth-button login-button">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </a>
                        <a href="register.php" class="auth-button register-button">
                            <i class="fas fa-user-plus"></i> Register
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'hf/footer.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>