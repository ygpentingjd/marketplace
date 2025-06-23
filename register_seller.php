<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include 'koneksi.php';
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $nomor_telepon = $_POST['nomor_telepon'];
    $alamat = $_POST['alamat'];
    $role = 'penjual';

    try {
        // Cek apakah email sudah terdaftar
        $check_email = "SELECT * FROM users WHERE email = ?";
        $stmt_check = $conn->prepare($check_email);
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result = $stmt_check->get_result();

        if ($result->num_rows > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            // Insert user baru dengan role penjual
            $sql = "INSERT INTO users (nama, email, password, nomor_telepon, alamat, role) 
                    VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssssss", $nama, $email, $password, $nomor_telepon, $alamat, $role);

            if ($stmt->execute()) {
                $_SESSION['success'] = "Registrasi penjual berhasil! Silakan login.";
                header("Location: login2.php");
                exit();
            } else {
                $error = "Error: " . $stmt->error;
            }
        }
    } catch (Exception $e) {
        $error = "Error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Penjual - K.O</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            position: relative;
            background: url('image/ID.png') no-repeat;
            background-size: cover;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .register-box {
            position: relative;
            z-index: 10;
            background: rgba(255, 255, 255, 0.9);
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
            color: black;
            width: 400px;
            text-align: center;
        }

        input {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            background-color: #ff9800;
            color: white;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }

        button:hover {
            background-color: #e65100;
        }

        .link {
            font-size: 14px;
            margin-top: 15px;
        }

        .link a {
            color: #007bff;
            text-decoration: none;
        }

        .error-message {
            color: #dc3545;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f8d7da;
            border-radius: 5px;
        }

        h2 {
            margin-bottom: 25px;
            color: #333;
        }

        label {
            display: block;
            text-align: left;
            margin-bottom: 5px;
            color: #333;
        }

        .form-group {
            margin-bottom: 15px;
        }

        #emailFeedback {
            font-size: 14px;
            margin-top: 5px;
            padding: 5px;
            border-radius: 3px;
        }

        .available {
            color: #28a745;
            background-color: #d4edda;
        }

        .taken {
            color: #dc3545;
            background-color: #f8d7da;
        }
    </style>
</head>

<body>
    <div class="register-box">
        <h2>Daftar Penjual</h2>
        <?php if (isset($error)): ?>
            <div class="error-message"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST" action="" id="registerForm">
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="nama" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" id="email" required>
                <div id="emailFeedback"></div>
            </div>
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>
            <div class="form-group">
                <label>Nomor Telepon</label>
                <input type="tel" name="nomor_telepon" pattern="[0-9]{10,13}" required>
            </div>
            <div class="form-group">
                <label>Alamat</label>
                <input type="text" name="alamat" required>
            </div>
            <button type="submit" id="submitBtn">Daftar Penjual</button>
            <p class="link">Sudah punya akun? <a href="login2.php">Login</a></p>
        </form>
    </div>
    <script>
        $(document).ready(function() {
            $('#email').on('input', function() {
                var email = $(this).val();
                if (email.length > 0) {
                    $.ajax({
                        url: 'check_email.php',
                        method: 'POST',
                        data: {
                            email: email
                        },
                        success: function(response) {
                            if (response === 'available') {
                                $('#emailFeedback').text('Email tersedia').removeClass('taken').addClass('available');
                            } else {
                                $('#emailFeedback').text('Email sudah terdaftar').removeClass('available').addClass('taken');
                            }
                        }
                    });
                } else {
                    $('#emailFeedback').text('');
                }
            });
        });
    </script>
</body>

</html>